<?php
require_once('databaseobject.class.php');
require_once('item.class.php');
require_once('market.class.php');
require_once('currency.class.php');

class Vendor extends DatabaseObject
{
  static protected $table_name = "vendor";
  static protected $db_columns = ['vendor_id', 'vendor_name', 'vendor_website', 'vendor_logo'];
  static protected $primary_key = 'vendor_id';

  public $vendor_id;
  public $vendor_name;
  public $vendor_website;
  public $vendor_logo;

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
}
