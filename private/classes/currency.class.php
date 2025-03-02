<?php
//require_once('databaseobject.class.php');

class Currency extends DatabaseObject
{
  static protected $table_name = "currency";
  static protected $db_columns = ['currency_id', 'currency_name'];
  static protected $primary_key = 'currency_id';

  public $currency_id;
  public $currency_name;

  public static function findPaymentMethodsByVendor($vendor_id)
  {
    $pdo = self::$database; // Use the inherited database connection

    $query = "SELECT c.currency_name 
              FROM vendor_currency vc
              JOIN currency c ON vc.currency_id = c.currency_id
              WHERE vc.vendor_id = ?";

    $stmt = $pdo->prepare($query);
    $stmt->execute([$vendor_id]);

    return $stmt->fetchAll(PDO::FETCH_COLUMN); // Fetch as an array of currency names
  }

  public static function fetchAllCurrencies()
  {
    $sql = "SELECT currency_id, currency_name FROM currency ORDER BY currency_name ASC";
    $stmt = self::$database->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function associateVendorPayments($vendor_id, $accepted_payments)
  {
    // Use the shared PDO connection set in DatabaseObject
    $db = self::$database;
    $sql = "INSERT INTO vendor_currency (vendor_id, currency_id) VALUES (:vendor_id, :currency_id)";
    $stmt = $db->prepare($sql);

    foreach ($accepted_payments as $currency_id) {
      $stmt->bindValue(':vendor_id', $vendor_id, PDO::PARAM_INT);
      $stmt->bindValue(':currency_id', $currency_id, PDO::PARAM_INT);
      $stmt->execute();
    }

    return true;
  }
}
