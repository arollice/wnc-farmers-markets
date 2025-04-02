<?php
include_once('../private/config.php');

$vendors = Vendor::findAllWithFilters(['approved' => true]);

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
