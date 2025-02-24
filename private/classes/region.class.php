<?php
class Region extends DatabaseObject
{
  static protected $table_name = "region";
  // Add latitude, longitude, and description (if applicable) to your columns
  static protected $db_columns = ['region_id', 'region_name', 'latitude', 'longitude'];
  static protected $primary_key = 'region_id';

  public $region_id;
  public $region_name;
  public $latitude;
  public $longitude;
  public $description;

  public static function fetchRegionsWithMarkets()
  {
    $sql = "SELECT r.region_id, r.region_name, r.latitude, r.longitude, 
                   m.market_id, m.market_name 
            FROM region r
            LEFT JOIN market m ON r.region_id = m.region_id
            GROUP BY r.region_id, m.market_id";
    $stmt = self::$database->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
}
