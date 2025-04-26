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

  public static function fetchAllRegions()
  {
    $sql = "SELECT region_id, region_name FROM region ORDER BY region_name ASC";
    $stmt = self::$database->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

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

  public static function fetchAllWithCoords(): array
  {
    $db  = self::get_database();
    $sql = "SELECT region_id, region_name, latitude, longitude
            FROM " . static::$table_name . "
            ORDER BY region_name ASC";
    $stmt = $db->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
  }

  public static function createNewRegion($region_name, $latitude, $longitude)
  {
    $db = self::get_database();
    $transaction_started = false;

    // Validate inputs
    $region_name = trim($region_name);
    $latitude = (float)$latitude;
    $longitude = (float)$longitude;


    if (empty($region_name)) {
      throw new Exception("Region name cannot be empty");
    }

    if (!is_numeric($latitude) || $latitude < -90 || $latitude > 90) {
      throw new Exception("Invalid latitude value");
    }

    if (!is_numeric($longitude) || $longitude < -180 || $longitude > 180) {
      throw new Exception("Invalid longitude value");
    }

    try {
      // Start transaction
      $db->beginTransaction();

      // Insert the new region
      $sql = "INSERT INTO region (region_name, latitude, longitude) 
                VALUES (:region_name, :latitude, :longitude)";

      $stmt = $db->prepare($sql);
      $stmt->bindParam(':region_name', $region_name, PDO::PARAM_STR);
      $stmt->bindParam(':latitude', $latitude, PDO::PARAM_STR);
      $stmt->bindParam(':longitude', $longitude, PDO::PARAM_STR);

      $stmt->execute();

      // Get the newly created region ID
      $region_id = $db->lastInsertId();

      if (!$region_id) {
        throw new Exception("Failed to get new region ID");
      }

      // Commit the transaction
      $db->commit();

      // Create and return a new Region object
      $region = new Region();
      $region->region_id = $region_id;
      $region->region_name = $region_name;
      $region->latitude = $latitude;
      $region->longitude = $longitude;

      return $region;
    } catch (PDOException $e) {
      // Roll back transaction on error
      if ($db->inTransaction()) {
        $db->rollBack();
      }
      throw new Exception("Database error: " . $e->getMessage());
    }
  }

  public static function findByName($region_name)
  {
    $db = self::get_database();
    $sql = "SELECT * FROM region WHERE region_name = :region_name LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->bindParam(':region_name', $region_name, PDO::PARAM_STR);
    $stmt->execute();

    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $region = new Region();
      $region->region_id = $row['region_id'];
      $region->region_name = $row['region_name'];
      $region->latitude = $row['latitude'];
      $region->longitude = $row['longitude'];
      return $region;
    }

    return null;
  }
}
