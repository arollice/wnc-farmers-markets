<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>WNC Farmers Market - Vendors</title>
  <link rel="stylesheet" type="text/css" href="css/farmers-market.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://unpkg.com/leaflet/dist/leaflet.js" defer></script>
</head>

<body>
  <?php
  include_once('../private/config.php');

  $vendors = Vendor::findAllWithFilters(['approved' => true]);

  // Sort vendors alphabetically by vendor name
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

  <?php
  include_once FOOTER_FILE;
  ?>
</body>

</html>
