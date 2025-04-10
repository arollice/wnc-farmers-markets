<?php
//require_once('databaseobject.class.php');
//require_once('vendor.class.php');

class UserAccount extends DatabaseObject
{
  static protected $table_name = "user_account";
  static protected $db_columns = [
    'user_id',
    'username',
    'password_hash',
    'email',
    'role',
    'vendor_id',
    'created_at',
    'last_login',
    'is_active'
  ];
  static protected $primary_key = 'user_id';

  public $user_id;
  public $username;
  public $password_hash;
  public $email;
  public $role;
  public $vendor_id;
  public $created_at;
  public $last_login;
  public $is_active;

  // If the user is linked to a vendor, fetch that vendor's information
  public function get_vendor()
  {
    $sql = "SELECT * FROM vendor WHERE vendor_id = :vendor_id LIMIT 1";
    $params = [':vendor_id' => $this->vendor_id];
    $result = Vendor::find_by_sql($sql, $params);
    return !empty($result) ? array_shift($result) : false;
  }

  // Static register method to create a new user account.
  public static function register($data)
  {
    $user = new self();
    $user->username = $data['username'];
    // Expect a key 'password' and hash it here
    $user->password_hash = password_hash($data['password'], PASSWORD_DEFAULT);
    $user->email = $data['email'];
    $user->role = $data['role'];
    $user->vendor_id = $data['vendor_id'];
    $user->created_at = date("Y-m-d H:i:s");
    // last_login is not set at registration
    $user->is_active = 1;

    if ($user->save()) {
      return $user;
    } else {
      return false;
    }
  }

  public function updateLastLogin()
  {
    $this->last_login = date("Y-m-d H:i:s");
    return $this->save();
  }

  public static function find_by_username($username)
  {
    $sql = "SELECT * FROM " . static::$table_name . " WHERE username = :username LIMIT 1";
    $stmt = self::$database->prepare($sql);
    $stmt->bindValue(':username', $username);
    $stmt->execute();
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    return $record ? static::instantiate($record) : false;
  }

  public static function find_by_email($email)
  {
    $sql = "SELECT * FROM " . static::$table_name . " WHERE email = :email LIMIT 1";
    $stmt = self::$database->prepare($sql);
    $stmt->bindValue(':email', $email);
    $stmt->execute();
    $record = $stmt->fetch(PDO::FETCH_ASSOC);
    return $record ? static::instantiate($record) : false;
  }

  // -------------------------------
  // Admin Action Methods
  // -------------------------------

  /**
   * Update the admin account details.
   *
   * @param string $username New username.
   * @param string $email New email address.
   * @return bool True on success, false otherwise.
   */
  public function updateAdminDetails($username, $email)
  {
    $this->username = trim($username);
    $this->email = trim($email);
    return $this->save();
  }

  /**
   * Delete the admin account.
   *
   * @return bool True on success, false on failure.
   */
  public function deleteAdmin()
  {
    // Prevent an admin from deleting their own account.
    if ($this->user_id == $_SESSION['user_id']) {
      return false;
    }
    return $this->delete();
  }

  /**
   * Process an admin-related action.
   *
   * This static method expects sanitized POST data.
   *
   * @param array $data The POST data.
   */
  public static function processAdminAction(array $data)
  {
    $admin_id = intval($data['admin_id'] ?? 0);
    $action = $data['action'] ?? '';

    $admin = self::find_by_id($admin_id);
    if (!$admin) {
      Utils::setFlashMessage('error', "Admin not found.");
      header("Location: admin.php");
      exit;
    }

    switch ($action) {
      case 'edit_admin':
        // Retrieve and trim the updated details.
        $username = trim($data['username'] ?? '');
        $email = trim($data['email'] ?? '');
        if ($admin->updateAdminDetails($username, $email)) {
          Utils::setFlashMessage('success', "Admin '{$admin->username}' updated successfully.");
        } else {
          Utils::setFlashMessage('error', "Error updating admin '{$admin->username}'.");
        }
        break;

      case 'delete_admin':
        if ($admin->user_id == $_SESSION['user_id']) {
          Utils::setFlashMessage('error', "You cannot delete your own admin account.");
        } elseif ($admin->deleteAdmin()) {
          Utils::setFlashMessage('success', "Admin '{$admin->username}' deleted.");
        } else {
          Utils::setFlashMessage('error', "Error deleting admin.");
        }
        break;

      default:
        Utils::setFlashMessage('error', "Invalid admin action.");
        break;
    }
    header("Location: admin.php#admin-account-management");
    exit;
  }

  // -------------------------------
  // Vendor Action Methods
  // -------------------------------

  /**
   * Update vendor details.
   *
   * @param Vendor $vendor Vendor object.
   * @param array $data Sanitized POST data.
   * @param array $files Uploaded files array.
   * @param int $vendor_id Vendor ID.
   */
  public static function updateVendor(Vendor $vendor, array $data, array $files, int $vendor_id)
  {
    $maxFileSize = 100 * 1024; // 100KB
    if (!Utils::validateFileSize($files['vendor_logo'], $maxFileSize)) {
      Utils::setFlashMessage('error', "The uploaded logo exceeds the maximum allowed size of 100KB.");
      header("Location: admin-edit-vendor.php?vendor_id=" . $vendor_id);
      exit;
    }
    $result = $vendor->updateDetails($data, $files);
    if ($result['success']) {
      Utils::setFlashMessage('success', "Vendor updated successfully.");
    } else {
      Utils::setFlashMessage('error', implode("<br>", $result['errors']));
    }
    header("Location: admin-edit-vendor.php?vendor_id=" . $vendor_id);
    exit;
  }

  /**
   * Add a market to the vendor.
   *
   * @param Vendor $vendor Vendor object.
   * @param int $vendor_id Vendor ID.
   * @param int $market_to_add Market ID to add.
   */
  public static function addMarket(Vendor $vendor, int $vendor_id, int $market_to_add)
  {
    if (validateMarketId($market_to_add)) {
      if ($vendor->addMarket($market_to_add)) {
        Utils::setFlashMessage('success', "Market added successfully.");
      } else {
        Utils::setFlashMessage('error', "Vendor is already attending that market or an error occurred.");
      }
    }
    header("Location: admin-edit-vendor.php?vendor_id=" . $vendor_id);
    exit;
  }

  /**
   * Remove a market from the vendor.
   *
   * @param Vendor $vendor Vendor object.
   * @param int $vendor_id Vendor ID.
   * @param int $market_to_remove Market ID to remove.
   */
  public static function removeMarket(Vendor $vendor, int $vendor_id, int $market_to_remove)
  {
    if (validateMarketId($market_to_remove)) {
      if ($vendor->removeMarket($market_to_remove)) {
        Utils::setFlashMessage('success', "Market removed successfully.");
      } else {
        Utils::setFlashMessage('error', "An error occurred while removing the market.");
      }
    }
    header("Location: admin-edit-vendor.php?vendor_id=" . $vendor_id);
    exit;
  }

  /**
   * Update the accepted payment methods for the vendor.
   *
   * @param Vendor $vendor Vendor object.
   * @param int $vendor_id Vendor ID.
   * @param array $selectedPayments Array of payment IDs.
   */
  public static function updatePayments(Vendor $vendor, int $vendor_id, array $selectedPayments)
  {
    if ($vendor->associatePayments($selectedPayments)) {
      Utils::setFlashMessage('success', "Payment methods updated successfully.");
    } else {
      Utils::setFlashMessage('error', "Error updating payment methods.");
    }
    header("Location: admin-edit-vendor.php?vendor_id=" . $vendor_id . "#accepted-payments");
    exit;
  }
}
