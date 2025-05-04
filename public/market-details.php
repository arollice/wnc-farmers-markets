<?php
include_once('../private/config.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>WNC Farmers Market - Market Details</title>
  <script src="js/farmers-market.js" defer></script>
  <link rel="icon" type="image/svg+xml" href="img/wnc-favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="img/wnc-favicon32.png">
  <link rel="stylesheet" type="text/css" href="css/farmers-market.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
  <?php
  $breadcrumbs = isset($_SESSION['breadcrumbs']) ? $_SESSION['breadcrumbs'] : [];

  $breadcrumbTrail = array_slice($breadcrumbs, -2);

  if (!isset($_GET['region_id']) && !isset($_GET['id'])) {
    die("Must provide either region_id or market id.");
  }

  // Decide mode & load data
  if (isset($_GET['region_id'])) {
    $region_id = intval($_GET['region_id']);
    $markets   = Market::fetchMarketsByRegion($region_id);

    $markets = Market::fetchMarketsByRegion($region_id);

    if (count($markets) === 0) {
      die("No markets found in this region.");
    } elseif (count($markets) === 1) {
      $market    = $markets[0];
      $multiMode = false;
    } else {
      $multiMode = true;
    }
  } else {
    $market_id = intval($_GET['id']);
    $market    = Market::fetchMarketDetails($market_id)
      ?: die("Market not found.");
    $multiMode = false;
  }

  // Load policies & (in single mode) vendors
  $policies = Market::fetchMarketPolicies();
  if (! $multiMode) {
    $vendors = Vendor::findVendorsByMarket($market['market_id'], ['approved' => true]);
  }

  $backLink = 'index.php';
  if (count($breadcrumbTrail) >= 2) {
    $backLink = $breadcrumbTrail[count($breadcrumbTrail) - 2];
  }

  include_once HEADER_FILE;
  ?>

  <main>
    <nav class="breadcrumb-trail" aria-label="Breadcrumb">
      <ul>
        <?php foreach ($breadcrumbTrail as $crumb): ?>
          <li>
            <a href="<?= htmlspecialchars($crumb) ?>">
              <?= htmlspecialchars(Utils::displayName($crumb)) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </nav>

    <?php if ($multiMode): ?>
      <!-- Multi-Market List View -->
      <h1>Markets in <?= htmlspecialchars($markets[0]['region_name']) ?></h1>
      <section id="multi-market-list">
        <ul>
          <?php foreach ($markets as $m): ?>
            <li>
              <a href="market-details.php?id=<?= $m['market_id'] ?>">
                <?= htmlspecialchars($m['market_name']) ?>
              </a>
              &mdash; <?= htmlspecialchars($m['city']) ?>, <?= htmlspecialchars($m['state_name']) ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </section>

    <?php else: ?>
      <!-- Single-Market Detail View -->
      <h1><?= htmlspecialchars($market['market_name']) ?> &mdash; Details</h1>
      <section id="single-market-card">
        <?= Market::renderMarketCard($market, $policies) ?>
      </section>

      <section>
        <h2>Attending Vendors</h2>
        <?php if (!empty($vendors)): ?>
          <ul class="attending-vendors">
            <?php foreach ($vendors as $v): ?>
              <li>
                <a href="vendor-details.php?id=<?= htmlspecialchars($v['vendor_id']) ?>">
                  <?= htmlspecialchars($v['vendor_name']) ?>
                </a>
                <?php if (!empty($v['vendor_website'])): ?>
                  — <a href="<?= htmlspecialchars($v['vendor_website']) ?>" target="_blank">Website</a>
                <?php endif; ?>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p>No vendors are currently listed for this market.</p>
        <?php endif; ?>
      </section>
    <?php endif; ?>

    <!-- Back link -->
    <p><a href="<?= htmlspecialchars($backLink) ?>" id="back-link">&larr; Back</a></p>
  </main>
  <?php include_once FOOTER_FILE; ?>
</body>

</html>
