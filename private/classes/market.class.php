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


  public static function fetchAllMarkets()
  {
    $sql = "SELECT 
                m.market_id,
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
            GROUP BY m.market_id
            ORDER BY m.market_name ASC";
    $stmt = self::$database->prepare($sql);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

  /**
   * Renders the HTML for a market card.
   *
   * @param array $market    The market data.
   * @param array|null $policies Optional policies related to the market.
   * @return string          The HTML output for the market card.
   */
  public static function renderMarketCard(array $market, ?array $policies = null): string
  {
    ob_start();
?>
    <div class="market-card">
      <h2><?= htmlspecialchars($market['market_name']) ?></h2>
      <p><strong>Location:</strong> <?= htmlspecialchars($market['city']) ?>, <?= htmlspecialchars($market['state_name']) ?> <?= htmlspecialchars($market['zip_code']) ?></p>
      <p><strong>Parking Info:</strong> <?= htmlspecialchars($market['parking_info']) ?></p>

      <?php if (!empty($market['market_open']) && !empty($market['market_close'])) : ?>
        <p><strong>Market Hours:</strong> <?= date("g:i A", strtotime($market['market_open'])) ?> - <?= date("g:i A", strtotime($market['market_close'])) ?></p>
      <?php endif; ?>

      <?php if (!empty($market['market_days'])) : ?>
        <p><strong>Market Days:</strong> <?= htmlspecialchars($market['market_days']) ?></p>
      <?php endif; ?>

      <?php if (!empty($market['market_season'])) : ?>
        <p><strong>Market Season:</strong> <?= htmlspecialchars($market['market_season']) ?></p>
      <?php endif; ?>

      <?php if (!empty($market['market_day'])) : ?>
        <p><strong>Market Season:</strong> <?= htmlspecialchars($market['market_day']) ?></p>
      <?php endif; ?>

      <?php if (!empty($market['last_market_date'])) : ?>
        <p><strong>Last Market Date:</strong> <?= date('F j, Y', strtotime($market['last_market_date'])) ?></p>
      <?php endif; ?>

      <?php if (!empty($policies)) : ?>
        <h3>Market Policies</h3>
        <ul>
          <?php foreach ($policies as $policy) : ?>
            <li>
              <strong><?= htmlspecialchars($policy['policy_name']) ?>:</strong>
              <?= nl2br(htmlspecialchars($policy['policy_description'])) ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
<?php
    return ob_get_clean();
  }

  public static function getVendorIdsForMarket($market_id)
  {
    if (!isset(self::$database)) {
      die("Database connection is not established.");
    }

    $sql = "SELECT vendor_id FROM vendor_market WHERE market_id = ?";
    $stmt = self::$database->prepare($sql);
    $stmt->execute([$market_id]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }
}
