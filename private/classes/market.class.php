<?php

class Market extends DatabaseObject
{
  static protected $table_name = "market";
  static protected $db_columns = [
    'market_id',
    'market_name',
    'region_id',
    'city',
    'state_id',
    'zip_code',
    'parking_info',
    'market_open',
    'market_close'
  ];
  static protected $primary_key = 'market_id';

  public $market_id;
  public $market_name;
  public $region_id;
  public $city;
  public $state_id;
  public $zip_code;
  public $parking_info;
  public $market_open;
  public $market_close;

  // Retrieve the region details for this market
  public function get_region()
  {
    $sql = "SELECT * FROM region WHERE region_id = :region_id LIMIT 1";
    $params = [':region_id' => $this->region_id];
    $result = Region::find_by_sql($sql, $params);
    return !empty($result) ? array_shift($result) : false;
  }

  // Fetch market details
  public static function fetchMarketDetails($market_id)
  {
    $sql = "SELECT 
            m.market_name, 
            m.city, 
            s.state_name, 
            m.zip_code, 
            m.parking_info,
            m.market_open,
            m.market_close,
            GROUP_CONCAT(DISTINCT se.season_name ORDER BY se.season_name ASC SEPARATOR ', ') AS market_season,
            MAX(ms.last_day_of_season) AS last_market_date,
            GROUP_CONCAT(DISTINCT ms.market_day ORDER BY ms.market_day ASC SEPARATOR ', ') AS market_days
        FROM market m
        LEFT JOIN state s ON m.state_id = s.state_id
        LEFT JOIN market_schedule ms ON m.market_id = ms.market_id
        LEFT JOIN season se ON ms.season_id = se.season_id
        WHERE m.market_id = :market_id
        GROUP BY m.market_id";


    $stmt = self::$database->prepare($sql);
    $stmt->bindParam(':market_id', $market_id, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetch(PDO::FETCH_ASSOC);
  }

  public static function fetchMarketPolicies()
  {
    try {
      $sql = "SELECT policy_name, policy_description 
                    FROM policy_info
                    WHERE policy_name IN ('Pet-Friendly Market', 'SNAP/EBT Accepted')"; // Only include these two policies

      $stmt = self::$database->query($sql);
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
      die("SQL Error: " . $e->getMessage());
    }
  }
}
