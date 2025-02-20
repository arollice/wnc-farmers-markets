<?php
include_once('../private/config.php');

// Fetch available regions
$regionQuery = "SELECT region_id, region_name FROM region ORDER BY region_name ASC";
$regionStmt = $pdo->prepare($regionQuery);
$regionStmt->execute();
$regions = $regionStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch available items
$itemQuery = "SELECT item_id, item_name FROM item ORDER BY item_name ASC";
$itemStmt = $pdo->prepare($itemQuery);
$itemStmt->execute();
$items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch available markets
$marketQuery = "SELECT market_id, market_name FROM market ORDER BY market_name ASC";
$marketStmt = $pdo->prepare($marketQuery);
$marketStmt->execute();
$markets = $marketStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch available payment methods
$currencyQuery = "SELECT currency_id, currency_name FROM currency ORDER BY currency_name ASC";
$currencyStmt = $pdo->prepare($currencyQuery);
$currencyStmt->execute();
$currencies = $currencyStmt->fetchAll(PDO::FETCH_ASSOC);

// Include the header
include_once HEADER_FILE;
?>

<h1>Vendor Registration</h1>
<p>Register your business to be listed in the WNC Farmers Market.</p>

<form action="<?= PUBLIC_PATH ?>/process_vendor_registration.php" method="POST" enctype="multipart/form-data">
  <label for="vendor_name">Business Name:</label>
  <input type="text" id="vendor_name" name="vendor_name" required>

  <label for="vendor_website">Business Website (optional):</label>
  <input type="url" id="vendor_website" name="vendor_website">

  <label for="vendor_logo">Upload Logo (optional):</label>
  <input type="file" id="vendor_logo" name="vendor_logo" accept="image/*">

  <label for="region">Select Your Home Region:</label>
  <select id="region" name="region_id" required>
    <option value="">-- Select a Region --</option>
    <?php foreach ($regions as $region): ?>
      <option value="<?= htmlspecialchars($region['region_id']) ?>">
        <?= htmlspecialchars($region['region_name']) ?>
      </option>
    <?php endforeach; ?>
  </select>

  <label>Accepted Payments:</label>
  <div class="checkbox-group">
    <?php foreach ($currencies as $currency): ?>
      <label>
        <input type="checkbox" name="accepted_payments[]" value="<?= htmlspecialchars($currency['currency_id']) ?>">
        <?= htmlspecialchars($currency['currency_name']) ?>
      </label>
    <?php endforeach; ?>
  </div>

  <button type="submit">Register</button>
</form>

<?php
// Include the footer
include_once FOOTER_FILE;
?>


//Make sure logo has additional naming convention added so no logos over write each other
