<?php
require_once('../private/config.php');

if (!isset($_GET['id'])) {
  die("Market ID not provided.");
}

$market_id = intval($_GET['id']);
$market = Market::fetchMarketDetails($market_id); // Fetch market details
$policies = Market::fetchMarketPolicies(); // Fetch selected policies
$vendors = Vendor::findVendorsByMarket($market_id, ['approved' => true]);


if (!$market) {
  die("Market not found.");
}

include_once HEADER_FILE;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Market Details - <?= htmlspecialchars($market['market_name']) ?></title>
</head>

<body>
  <main>
    <h1><?= htmlspecialchars($market['market_name']) ?> - Market Details</h1>

    <div id="single-market-card">
      <?= Market::renderMarketCard($market) ?>
    </div>

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

    <a href="regions.php">Back to Map</a>
  </main>

</body>

<?php include_once FOOTER_FILE; ?>

</html>
