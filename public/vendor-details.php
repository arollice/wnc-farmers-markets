<?php
require_once('../private/config.php');

// Check if vendor ID is provided
if (!isset($_GET['id'])) {
  die("Vendor ID not provided.");
}

$_SESSION['prev_page'] = $_SERVER['REQUEST_URI'];

$vendor_id = intval($_GET['id']);
$market_id = isset($_GET['market_id']) ? intval($_GET['market_id']) : null;

$vendor = Vendor::findVendorById($vendor_id);
$items = Item::findItemsByVendor($vendor_id);
$payment_methods = Currency::findPaymentMethodsByVendor($vendor_id);

if (!$vendor) {
  die("Vendor not found.");
}

// Use the vendor object if available, otherwise use the static function
$vendorMarkets = Vendor::findMarketsByVendor($vendor_id);

include_once HEADER_FILE;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars($vendor['vendor_name']) ?> - Vendor Details</title>
  <!-- Include additional CSS/JS if needed -->
</head>

<body>
  <main>
    <h1><?= htmlspecialchars($vendor['vendor_name']) ?></h1>

    <div id="vendor-details">
      <?php if (!empty($vendor['vendor_logo'])): ?>
        <img src="<?= htmlspecialchars($vendor['vendor_logo']) ?>" alt="<?= htmlspecialchars($vendor['vendor_name']) ?> Logo" class="vendor-logo">
      <?php else: ?>
        <p>No logo available for this vendor.</p>
      <?php endif; ?>

      <p><strong>Description:</strong>
        <?= !empty($vendor['vendor_description']) ? htmlspecialchars($vendor['vendor_description']) : 'No description available.' ?>
      </p>

      <p><strong>Website:</strong>
        <?php if (!empty($vendor['vendor_website'])): ?>
          <a href="<?= htmlspecialchars($vendor['vendor_website']) ?>" target="_blank">
            <?= htmlspecialchars($vendor['vendor_website']) ?>
          </a>
        <?php else: ?>
          No website available.
        <?php endif; ?>
      </p>
    </div>

    <!-- Display Items Sold -->
    <h2>Items Sold</h2>
    <?php if (!empty($items)): ?>
      <ul>
        <?php foreach ($items as $item): ?>
          <li><?= htmlspecialchars($item['item_name']) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No items listed for this vendor.</p>
    <?php endif; ?>

    <!-- Display Accepted Payment Methods -->
    <h2>Accepted Payment Methods</h2>
    <?php if (!empty($payment_methods)): ?>
      <ul>
        <?php foreach ($payment_methods as $method): ?>
          <li><?= htmlspecialchars($method) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>No payment methods listed for this vendor.</p>
    <?php endif; ?>

    <!-- Display Markets Attending by the Vendor -->
    <h2>Markets Attending</h2>
    <?php if (!empty($vendorMarkets)): ?>
      <ul class="attending-vendors">
        <?php foreach ($vendorMarkets as $market): ?>
          <li>
            <a href="market-details.php?id=<?= htmlspecialchars($market['market_id']) ?>">
              <?= htmlspecialchars($market['market_name']) ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <p>This vendor is not attending any markets.</p>
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
