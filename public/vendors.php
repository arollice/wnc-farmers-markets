<?php
require_once('../private/config.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>WNC Farmers Market - Vendors</title>
  <script src="js/farmers-market.js" defer></script>
  <link rel="icon" type="image/svg+xml" href="img/wnc-favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="img/wnc-favicon32.png">
  <link rel="stylesheet" type="text/css" href="css/farmers-market.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
  <?php
  $vendors = Vendor::findAllWithFilters(['approved' => true]);

  usort($vendors, function ($a, $b) {
    return strcasecmp($a['vendor_name'], $b['vendor_name']);
  });

  include_once HEADER_FILE;
  ?>

  <section id="login">
    <a href="<?= PUBLIC_PATH ?>/vendor-register.php" class="btn">Become a Vendor</a>
    <p>Already a vendor? <a href="login.php">Login here</a>.</p>
  </section>

  <main>
    <h1>Vendor Directory</h1>
    <p>Meet the growers, artisans, and food crafters bringing fresh, local goods to your table!</p>
    <section class="vendor-directory">
      <?php if (empty($vendors)) : ?>
        <p>No vendors found.</p>
      <?php else : ?>
        <ul class="vendor-list">
          <?php foreach ($vendors as $vendor) : ?>
            <li class="vendor-item">
              <a href="<?= PUBLIC_PATH ?>/vendor-details.php?id=<?= htmlspecialchars($vendor['vendor_id']) ?>&source=vendors.php">
                <img src="<?= htmlspecialchars($vendor['vendor_logo']) ?>" alt="<?= htmlspecialchars($vendor['vendor_name']) ?> Logo">
                <h3><?= htmlspecialchars($vendor['vendor_name']) ?></h3>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>
  </main>

  <?php include_once FOOTER_FILE; ?>
</body>

</html>
