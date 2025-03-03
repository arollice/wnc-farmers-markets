<?php

class Vendor extends DatabaseObject
{
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

  // Fetch all vendors
  public static function findAll()
  {
    return self::fetch_all_from_table('vendor');
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

  // Retrieve vendors by an array of IDs
  public static function findVendorsByIds($vendor_ids)
  {
    if (!isset(self::$database)) {
      die("Database connection is not established.");
    }
    if (empty($vendor_ids)) {
      return [];
    }
    // Generate placeholders for the query
    $placeholders = implode(',', array_fill(0, count($vendor_ids), '?'));
    $sql = "SELECT vendor_id, vendor_name, vendor_website, vendor_logo, vendor_description 
            FROM vendor 
            WHERE vendor_id IN ($placeholders)";
    $stmt = self::$database->prepare($sql);
    $stmt->execute($vendor_ids);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  // Retrieve a single vendor by ID
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

  // Retrieve vendors by market ID
  public static function findVendorsByMarket($market_id)
  {
    if (!isset(self::$database)) {
      die("Database connection is not established.");
    }
    $sql = "SELECT v.vendor_id, v.vendor_name, v.vendor_website 
            FROM vendor v
            JOIN vendor_market vm ON v.vendor_id = vm.vendor_id
            WHERE vm.market_id = ?";
    $stmt = self::$database->prepare($sql);
    $stmt->execute([$market_id]);
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
}
