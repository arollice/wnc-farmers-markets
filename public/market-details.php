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

  if (!isset($_GET['id'])) {
    die("Market ID not provided.");
  }

  $_SESSION['prev_page'] = $_SERVER['REQUEST_URI'];

  $market_id = intval($_GET['id']);
  $market = Market::fetchMarketDetails($market_id);
  $policies = Market::fetchMarketPolicies();
  $vendors = Vendor::findVendorsByMarket($market_id, ['approved' => true]);


  if (!$market) {
    die("Market not found.");
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

    <h1><?= htmlspecialchars($market['market_name']) ?> - Details</h1>
    <section id="single-market-card">
      <?= Market::renderMarketCard($market) ?>
    </section>

    <section>
      <h2>Attending Vendors</h2>
      <?php if (!empty($vendors)) : ?>
        <ul class="attending-vendors">
          <?php foreach ($vendors as $vendor) : ?>
            <li>
              <a href="vendor-details.php?id=<?= htmlspecialchars($vendor['vendor_id']) ?>">
                <?= htmlspecialchars($vendor['vendor_name']) ?>
              </a>
              <?php if (!empty($vendor['vendor_website'])) : ?>
                - <a href="<?= htmlspecialchars($vendor['vendor_website']) ?>" target="_blank">Website</a>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php else : ?>
        <p>No vendors are currently listed for this market.</p>
      <?php endif; ?>
    </section>

    <?php if (!empty($policies)) : ?>
      <section class="market-policies">
        <h2>Market Policies</h2>
        <ul>
          <?php foreach ($policies as $policy) : ?>
            <li><?= htmlspecialchars($policy['policy_description']) ?></li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>

    <?php
    $backLink = 'index.php';

    if (isset($_SESSION['breadcrumbs']) && count($_SESSION['breadcrumbs']) >= 2) {
      $backLink = $_SESSION['breadcrumbs'][count($_SESSION['breadcrumbs']) - 2];
    }
    ?>
    <a href="<?= htmlspecialchars($backLink) ?>" id="back-link">&larr; Back</a>
  </main>

  <?php include_once FOOTER_FILE; ?>
</body>

</html>
