<?php
//require_once('databaseobject.class.php');

class Item extends DatabaseObject
{
  static protected $table_name = "item";
  static protected $db_columns = ['item_id', 'item_name'];
  static protected $primary_key = 'item_id';

  public $item_id;
  public $item_name;

  public static function findItemsByVendor($vendor_id)
  {
    if (!isset(self::$database)) {
      die("Database connection is not established.");
    }

    $sql = "SELECT i.item_id, i.item_name 
            FROM item i
            JOIN vendor_item vi ON i.item_id = vi.item_id
            WHERE vi.vendor_id = ?";

    $stmt = self::$database->prepare($sql);
    $stmt->execute([$vendor_id]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
