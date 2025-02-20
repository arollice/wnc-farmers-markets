<?php
//require_once('databaseobject.class.php');
//require_once('region.class.php');

class Market extends DatabaseObject
{
  static protected $table_name = "market";
  static protected $db_columns = ['market_id', 'market_name', 'region_id', 'city', 'state_id', 'zip_code', 'parking_info'];
  static protected $primary_key = 'market_id';

  public $market_id;
  public $market_name;
  public $region_id;
  public $city;
  public $state_id;
  public $zip_code;
  public $parking_info;

  // Retrieve the region details for this market
  public function get_region()
  {
    $sql = "SELECT * FROM region WHERE region_id = :region_id LIMIT 1";
    $params = [':region_id' => $this->region_id];
    $result = Region::find_by_sql($sql, $params);
    return !empty($result) ? array_shift($result) : false;
  }
}
