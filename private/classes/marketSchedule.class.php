<?php
require_once __DIR__ . '/../../private/config.php';

class MarketSchedule extends DatabaseObject
{
  static protected $table_name  = 'market_schedule';
  static protected $db_columns  = [
    'market_id',
    'market_day',
    'last_day_of_season',
    'season_id'
  ];
  // We’ll treat market_id as the unique key here:
  static protected $primary_key = 'market_id';

  public $market_id;
  public $market_day;
  public $last_day_of_season;
  public $season_id;

  protected function marketAttributes()
  {
    $attributes = [];
    foreach (static::$db_columns as $column) {
      $attributes[$column] = $this->$column;
    }
    return $attributes;
  }

  /**
   * Fetch the single schedule row for a given market.
   * @param int $m_id
   * @return MarketSchedule|null
   */
  public static function findByMarketId(int $m_id): ?self
  {
    $rows = static::find_by_sql(
      "SELECT * FROM " . static::$table_name . " WHERE market_id = ?",
      [$m_id]
    );
    return $rows[0] ?? null;
  }

  public static function findAllByMarketId(int $m_id): array
  {
    return static::find_by_sql(
      "SELECT * FROM " . static::$table_name . " WHERE market_id = ?",
      [$m_id]
    );
  }

  public static function deleteByMarketId(int $m_id): bool
  {
    $sql = "DELETE FROM " . static::$table_name . " WHERE market_id = ?";
    $stmt = self::$database->prepare($sql);
    return $stmt->execute([$m_id]);
  }

  /**
   * Replace all schedule rows for a market in one go.
   *
   * @param int    $market_id
   * @param string[] $days         List of day names, e.g. ['Monday','Wednesday']
   * @param int    $season_id
   * @param string $last_day       Date in 'YYYY-MM-DD' format
   * @return void
   */
  public static function updateForMarket(int $market_id, array $days, int $season_id, string $last_day): void
  {
    if (empty($days)) {
      return;
    }

    static::deleteByMarketId($market_id);


    $db   = static::get_database();
    $sql  = "INSERT INTO " . static::$table_name . " 
               (market_id, market_day, season_id, last_day_of_season)
             VALUES 
               (:market_id, :market_day, :season_id, :last_day)";
    $stmt = $db->prepare($sql);

    foreach ($days as $day) {
      $stmt->execute([
        ':market_id'   => $market_id,
        ':market_day'  => $day,
        ':season_id'   => $season_id,
        ':last_day'    => $last_day
      ]);
    }
  }
}
