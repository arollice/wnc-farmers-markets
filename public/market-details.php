<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>WNC Farmers Market - Market Details</title>
  <link rel="stylesheet" type="text/css" href="css/farmers-market.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="js/farmers-market.js" defer></script>
</head>

<body>
  <?php
  require_once('../private/config.php');

  $breadcrumbs = isset($_SESSION['breadcrumbs']) ? $_SESSION['breadcrumbs'] : [];

  $breadcrumbTrail = array_slice($breadcrumbs, -2);

  if (!isset($_GET['id'])) {
    die("Market ID not provided.");
  }

  $_SESSION['prev_page'] = $_SERVER['REQUEST_URI'];

  $market_id = intval($_GET['id']);
  $market = Market::fetchMarketDetails($market_id); // Fetch market details
  $policies = Market::fetchMarketPolicies(); // Fetch selected policies
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

    <h1><?= htmlspecialchars($market['market_name']) ?> - Market Details</h1>

    <div id="single-market-card">
      <?= Market::renderMarketCard($market) ?>
    </div>

    <!-- Vendors Attending This Market -->
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

    <!-- Display Market Policies at the Bottom -->
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

    $backLink = 'default_page.php';  // Change to your desired fallback (e.g., 'vendors.php' or 'regions.php')

    // Check if the breadcrumbs array exists and has at least 2 items.
    if (isset($_SESSION['breadcrumbs']) && count($_SESSION['breadcrumbs']) >= 2) {
      // Get the second-to-last entry (the previous page)
      $backLink = $_SESSION['breadcrumbs'][count($_SESSION['breadcrumbs']) - 2];
    }
    ?>
    <a href="<?= htmlspecialchars($backLink) ?>">Back</a>

  </main>

  <?php include_once FOOTER_FILE; ?>
</body>

</html>
