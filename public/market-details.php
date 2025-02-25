<?php
require_once('../private/config.php');
include_once HEADER_FILE;

if (!isset($_GET['id'])) {
  die("Market ID not provided.");
}

$market_id = intval($_GET['id']);
$market = Market::fetchMarketDetails($market_id); // Fetch market details
$policies = Market::fetchMarketPolicies(); // Fetch selected policies
$vendors = Vendor::findVendorsByMarket($market_id); // Fetch vendors attending this market

if (!$market) {
  die("Market not found.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Market Details - <?= htmlspecialchars($market['market_name']) ?></title>
  <link rel="stylesheet" href="styles.css">
</head>

<body>

  <h1><?= htmlspecialchars($market['market_name']) ?> - Market Details</h1>

  <?= Market::renderMarketCard($market, $policies) ?>

  <!-- Vendors Attending This Market -->
  <h2>Attending Vendors</h2>
  <?php if (!empty($vendors)) : ?>
    <ul>
      <?php foreach ($vendors as $vendor) : ?>
        <li>
          <a href="vendor-details.php?id=<?= htmlspecialchars($vendor['vendor_id']) ?>&market_id=<?= htmlspecialchars($market_id) ?>">
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

  <a href="regions.php">Back to Map</a>

</body>

<?php include_once FOOTER_FILE; ?>

</html>
