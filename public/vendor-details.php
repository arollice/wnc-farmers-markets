<?php
require_once('../private/config.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>WNC Farmers Market - Vendor Details</title>
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

  $vendorMarkets = Vendor::findMarketsByVendor($vendor_id);

  include_once HEADER_FILE;
  ?>

  <main id="detail-page">
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

    <h2><?= htmlspecialchars($vendor['vendor_name']) ?></h2>
    <section id="vendor-details">
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
    </section>

    <section>
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
    </section>

    <section>
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
    </section>

    <section>
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
    </section>

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
