<?php
require_once('../private/config.php');
include_once HEADER_FILE;


// Check if vendor ID is provided
if (!isset($_GET['id'])) {
  die("Vendor ID not provided.");
}

$vendor_id = intval($_GET['id']);
$market_id = isset($_GET['market_id']) ? intval($_GET['market_id']) : null;

$vendor = Vendor::findVendorById($vendor_id);
$items = Item::findItemsByVendor($vendor_id);
$payment_methods = Currency::findPaymentMethodsByVendor($vendor_id);
if (!$vendor) {
  die("Vendor not found.");
}

$vendorMarkets = method_exists($vendor, 'get_markets')
  ? $vendor->get_markets()
  : Vendor::findMarketsByVendor($vendor_id);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($vendor['vendor_name']) ?> - Vendor Details</title>
</head>

<body>

  <h1><?= htmlspecialchars($vendor['vendor_name']) ?></h1>

  <?php if (!empty($vendor['vendor_logo'])) : ?>
    <img src="<?= htmlspecialchars($vendor['vendor_logo']) ?>" alt="<?= htmlspecialchars($vendor['vendor_name']) ?> Logo" class="vendor-logo">
  <?php else : ?>
    <p>No logo available for this vendor.</p>
  <?php endif; ?>

  <p><strong>Description:</strong>
    <?= !empty($vendor['vendor_description']) ? htmlspecialchars($vendor['vendor_description']) : 'No description available.' ?>
  </p>

  <p><strong>Website:</strong>
    <?php if (!empty($vendor['vendor_website'])) : ?>
      <a href="<?= htmlspecialchars($vendor['vendor_website']) ?>" target="_blank"><?= htmlspecialchars($vendor['vendor_website']) ?></a>
    <?php else : ?>
      No website available.
    <?php endif; ?>
  </p>

  <!-- Display Items Sold -->
  <h2>Items Sold</h2>
  <?php if (!empty($items)) : ?>
    <ul>
      <?php foreach ($items as $item) : ?>
        <li><?= htmlspecialchars($item['item_name']) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php else : ?>
    <p>No items listed for this vendor.</p>
  <?php endif; ?>

  <!-- Display Accepted Payment Methods -->
  <h2>Accepted Payment Methods</h2>
  <?php if (!empty($payment_methods)) : ?>
    <ul>
      <?php foreach ($payment_methods as $method) : ?>
        <li><?= htmlspecialchars($method) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php else : ?>
    <p>No payment methods listed for this vendor.</p>
  <?php endif; ?>

  <!-- Display Markets Attended by the Vendor -->
  <h2>Markets Attending</h2>
  <?php if (!empty($vendorMarkets)) : ?>
    <ul>
      <?php foreach ($vendorMarkets as $market) : ?>
        <li>
          <a href="market-details.php?id=<?= htmlspecialchars($market['market_id']) ?>">
            <?= htmlspecialchars($market['market_name']) ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php else : ?>
    <p>This vendor is not attending any markets.</p>
  <?php endif; ?>

  <!-- Back Button -->
  <?php if (!empty($market_id)) : ?>
    <p><a href="market-details.php?id=<?= htmlspecialchars($market_id) ?>">Back to Market Details</a></p>
  <?php elseif (!empty($vendor_id)) : ?>
    <p><a href="vendors.php">Back to Vendors</a></p>
  <?php endif; ?>



</body>

</html>
<?php include_once FOOTER_FILE; ?>
