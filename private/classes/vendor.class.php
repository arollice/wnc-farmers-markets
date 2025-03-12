<?php
include_once('../private/filterable.php'); // Include the Filterable trait

class Vendor extends DatabaseObject
{
  use Filterable;

  static protected $table_name = "vendor";
  static protected $db_columns = [
    'vendor_id',
    'vendor_name',
    'vendor_website',
    'vendor_logo',
    'vendor_description',
    'status'
  ];
  static protected $primary_key = 'vendor_id';

  public $vendor_id;
  public $vendor_name;
  public $vendor_website;
  public $vendor_logo;
  public $vendor_description;
  public $status;

  // Fetch all vendors (legacy method)
  public static function findAll()
  {
    return self::fetch_all_from_table('vendor');
  }

  // New: Retrieve all vendors with optional filters.
  public static function findAllWithFilters($filters = [])
  {
    list($filterSql, $params) = self::buildFilterConditions($filters);
    $sql = "SELECT * FROM vendor" . $filterSql;
    $stmt = self::$database->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // Retrieve items associated with this vendor (via vendor_item junction table)
  public function get_items()
  {
    $sql  = "SELECT i.* FROM item i ";
    $sql .= "JOIN vendor_item vi ON i.item_id = vi.item_id ";
    $sql .= "WHERE vi.vendor_id = :vendor_id";
    $params = [':vendor_id' => $this->vendor_id];
    return Item::find_by_sql($sql, $params);
  }

  // Retrieve markets where this vendor attends (via vendor_market junction table)
  public function get_markets()
  {
    $sql  = "SELECT m.*, vm.attending_date FROM market m ";
    $sql .= "JOIN vendor_market vm ON m.market_id = vm.market_id ";
    $sql .= "WHERE vm.vendor_id = :vendor_id";
    $params = [':vendor_id' => $this->vendor_id];
    return Market::find_by_sql($sql, $params);
  }

  // Retrieve accepted currencies for this vendor (via vendor_currency junction table)
  public function get_accepted_currencies()
  {
    $sql  = "SELECT c.* FROM currency c ";
    $sql .= "JOIN vendor_currency vc ON c.currency_id = vc.currency_id ";
    $sql .= "WHERE vc.vendor_id = :vendor_id";
    $params = [':vendor_id' => $this->vendor_id];
    return Currency::find_by_sql($sql, $params);
  }

  // Retrieve vendors by an array of IDs with optional filters.
  public static function findVendorsByIds($vendor_ids, $filters = [])
  {
    if (!isset(self::$database)) {
      die("Database connection is not established.");
    }
    if (empty($vendor_ids)) {
      return [];
    }
    // Generate placeholders for the query.
    $placeholders = implode(',', array_fill(0, count($vendor_ids), '?'));
    $sql = "SELECT vendor_id, vendor_name, vendor_website, vendor_logo, vendor_description 
            FROM vendor 
            WHERE vendor_id IN ($placeholders)";
    if (!empty($filters['approved'])) {
      $sql .= " AND status = 'approved'";
    }
    $stmt = self::$database->prepare($sql);
    $stmt->execute($vendor_ids);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // Retrieve a single vendor by ID.
  public static function findVendorById($vendor_id)
  {
    if (!isset(self::$database)) {
      die("Database connection is not established.");
    }
    $sql = "SELECT vendor_id, vendor_name, vendor_website, vendor_logo, vendor_description, status 
            FROM vendor 
            WHERE vendor_id = ?";
    $stmt = self::$database->prepare($sql);
    $stmt->execute([$vendor_id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  // Retrieve vendors by market ID with optional filters.
  public static function findVendorsByMarket($market_id, $filters = [])
  {
    if (!isset(self::$database)) {
      die("Database connection is not established.");
    }
    $sql = "SELECT v.vendor_id, v.vendor_name, v.vendor_website 
            FROM vendor v
            JOIN vendor_market vm ON v.vendor_id = vm.vendor_id
            WHERE vm.market_id = ?";
    $params = [$market_id];
    if (!empty($filters['approved'])) {
      $sql .= " AND v.status = 'approved'";
    }
    $stmt = self::$database->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function register($data)
  {
    $vendor = new self();
    $vendor->vendor_name = trim($data['vendor_name']);
    $vendor->vendor_website = trim($data['vendor_website'] ?? '');
    $vendor->vendor_description = trim($data['vendor_description'] ?? '');
    $vendor->status = 'pending';  // New vendors start as pending

    if ($vendor->save()) {
      return $vendor;
    } else {
      return false;
    }
  }

  public function associatePayments($accepted_payments)
  {
    if (!empty($accepted_payments)) {
      return Currency::associateVendorPayments($this->{self::$primary_key}, $accepted_payments);
    }
    return true;
  }

  public static function findMarketsByVendor($vendor_id)
  {
    // Use the standard database connection method
    $pdo = DatabaseObject::get_database();

    $sql = "SELECT m.* 
          FROM market AS m
          INNER JOIN vendor_market AS vm ON m.market_id = vm.market_id
          WHERE vm.vendor_id = :vendor_id";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':vendor_id' => $vendor_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }


  /**
   * Updates the vendor's details based on provided form data and file uploads.
   *
   * @param array $post The $_POST data containing vendor details.
   * @param array $files The $_FILES data for file uploads.
   *
   * @return array An associative array with two keys:
   *               - 'success' (bool): True if the update was successful, false otherwise.
   *               - 'errors' (array): An array of error messages if the update failed.
   */
  public function updateDetails($post, $files)
  {
    $errors = [];
    $vendor_id = $this->vendor_id;
    $pdo = DatabaseObject::get_database();

    // Update website and description.
    $this->vendor_website = isset($post['vendor_website']) ? trim($post['vendor_website']) : null;
    if ($this->vendor_website === '') {
      $this->vendor_website = null;
    }
    $this->vendor_description = !empty($post['vendor_description']) ? trim($post['vendor_description']) : $this->vendor_description;

    // Process accepted payments.
    $stmt = $pdo->prepare("DELETE FROM vendor_currency WHERE vendor_id = ?");
    $stmt->execute([$vendor_id]);
    if (isset($post['accepted_payments']) && is_array($post['accepted_payments'])) {
      $accepted_payments = $post['accepted_payments'];
      Currency::associateVendorPayments($vendor_id, $accepted_payments);
    }

    // Process logo deletion and file upload.
    if (isset($post['delete_logo']) && $post['delete_logo'] == '1') {
      if (!empty($this->vendor_logo)) {
        $filePath = PROJECT_ROOT . '/public/' . $this->vendor_logo;
        if (file_exists($filePath)) {
          unlink($filePath);
        }
      }
      $this->vendor_logo = '';
    } elseif (isset($files['vendor_logo']) && $files['vendor_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
      if ($files['vendor_logo']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "There was an error uploading your file. Error code: " . $files['vendor_logo']['error'];
        return ['success' => false, 'errors' => $errors];
      }
      $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime_type = finfo_file($finfo, $files['vendor_logo']['tmp_name']);
      finfo_close($finfo);
      if (!in_array($mime_type, $allowed_types)) {
        $errors[] = "Only JPG, PNG, and GIF files are allowed. Detected type: $mime_type";
        return ['success' => false, 'errors' => $errors];
      }
      $maxSize = 2 * 1024 * 1024; // 2MB limit
      if ($files['vendor_logo']['size'] > $maxSize) {
        $errors[] = "The file is too large. Maximum allowed size is 2MB.";
        return ['success' => false, 'errors' => $errors];
      }
      $target_dir = UPLOADS_PATH;
      if (!is_dir($target_dir)) {
        $errors[] = "Upload directory does not exist: $target_dir";
        return ['success' => false, 'errors' => $errors];
      }
      $extension = strtolower(pathinfo($files["vendor_logo"]["name"], PATHINFO_EXTENSION));
      $unique_name = "vendor_" . $vendor_id . "_" . time() . "_" . uniqid() . "." . $extension;
      $target_file = $target_dir . '/' . $unique_name;
      if (!move_uploaded_file($files["vendor_logo"]["tmp_name"], $target_file)) {
        $errors[] = "There was an error uploading your file. Please try again.";
        return ['success' => false, 'errors' => $errors];
      }
      $this->vendor_logo = 'uploads/' . $unique_name;
    }

    $this->vendor_name = isset($post['vendor_name']) ? trim($post['vendor_name']) : $this->vendor_name;

    // Save updated data to the database.
    if ($this->save()) {
      return ['success' => true, 'errors' => []];
    } else {
      $errors[] = "There was an error updating the vendor.";
      return ['success' => false, 'errors' => $errors];
    }
  }

  public function addMarket($market_id)
  {
    $pdo = DatabaseObject::get_database();
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM vendor_market WHERE vendor_id = ? AND market_id = ?");
    $stmt->execute([$this->vendor_id, $market_id]);
    if ($stmt->fetchColumn() == 0) {
      $stmt = $pdo->prepare("INSERT INTO vendor_market (vendor_id, market_id) VALUES (?, ?)");
      return $stmt->execute([$this->vendor_id, $market_id]);
    }
    return false;
  }

  public function removeMarket($market_id)
  {
    $pdo = DatabaseObject::get_database();
    $stmt = $pdo->prepare("DELETE FROM vendor_market WHERE vendor_id = ? AND market_id = ?");
    return $stmt->execute([$this->vendor_id, $market_id]);
  }
}
