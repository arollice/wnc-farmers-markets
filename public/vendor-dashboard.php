<?php
include_once('../private/config.php');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Redirect if not logged in as vendor.
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
  header('Location: login.php');
  exit;
}

// Retrieve the user account record using the user_id stored in session.
// This assumes that your DatabaseObject class provides a findById() method.
$userAccount = UserAccount::find_by_id($_SESSION['user_id']);
if (!$userAccount || empty($userAccount->vendor_id)) {
  // If there is an issue retrieving the vendor id, log out or redirect.
  header('Location: logout.php');
  exit;
}

$vendor_id = $userAccount->vendor_id;

// Retrieve vendor information using the actual vendor id.
$vendor = Vendor::findVendorById($vendor_id);
$status = isset($vendor['status']) ? $vendor['status'] : 'Unknown';

// Create a Vendor object to use its methods.
$vendorObj = new Vendor();
$vendorObj->vendor_id = $vendor_id;
$items = $vendorObj->get_items();

$all_markets = [];
if (method_exists('Market', 'fetchAllMarkets')) {
  $all_markets = Market::fetchAllMarkets();
}

$currencies = Currency::fetchAllCurrencies();

$accepted = [];
$acceptedCurrencies = $vendorObj->get_accepted_currencies();
if ($acceptedCurrencies) {
  foreach ($acceptedCurrencies as $currency) {
    $accepted[] = $currency->currency_id;
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Vendor Dashboard</title>
  <!-- Optional: Include Select2 CSS for enhanced multi-select 
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" /> -->
</head>

<body>
  <h2>Welcome, <?= htmlspecialchars($_SESSION['username']); ?>!</h2>
  <p>Account Status: <strong><?= htmlspecialchars($status); ?></strong></p>
  <a href="logout.php">Logout</a>

  <hr>

  <!-- Feature: Change Password -->
  <section id="change-password">
    <h3>Change Password</h3>
    <form action="change-password.php" method="POST">
      <label for="current_password">Current Password:</label>
      <input type="password" id="current_password" name="current_password" required>
      <br>
      <label for="new_password">New Password:</label>
      <input type="password" id="new_password" name="new_password" required minlength="8">
      <br>
      <label for="confirm_password">Confirm New Password:</label>
      <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
      <br>
      <button type="submit">Change Password</button>
    </form>
  </section>

  <!-- Feature 1: Manage Items -->
  <section id="manage-items">
    <h3>Manage Items</h3>
    <!-- Display existing items -->
    <div>
      <h4>Your Items</h4>
      <?php if (!empty($items)): ?>
        <ul>
          <?php foreach ($items as $item): ?>
            <li><?= htmlspecialchars($item['item_name']); ?></li>
          <?php endforeach; ?>
        </ul>
      <?php else: ?>
        <p>You have no items added yet.</p>
      <?php endif; ?>
    </div>
    <!-- Add new item -->
    <div>
      <h4>Add New Item</h4>
      <form action="add-item.php" method="POST">
        <label for="new_item">Item Name:</label>
        <input type="text" id="new_item" name="new_item" required>
        <button type="submit">Add Item</button>
      </form>
      <!-- Note: In add-item.php, perform backend validation to check for duplicates before insertion -->
    </div>
  </section>

  <!-- Feature 2: Multi-select for Attending Markets -->
  <section id="update-markets">
    <h3>Select Markets to Attend</h3>
    <form action="update-markets.php" method="POST">
      <label for="markets">Select Markets:</label>
      <select id="markets" name="market_ids[]" multiple="multiple" style="width:300px;">
        <?php foreach ($all_markets as $market): ?>
          <option value="<?= htmlspecialchars($market['market_id']); ?>">
            <?= htmlspecialchars($market['market_name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
      <button type="submit">Update Markets</button>
    </form>
  </section>

  <!-- Feature 3: Upload Vendor Logo -->
  <section id="upload-logo">
    <h3>Upload Vendor Logo</h3>
    <form action="upload-logo.php" method="POST" enctype="multipart/form-data">
      <label for="vendor_logo">Select Logo:</label>
      <input type="file" id="vendor_logo" name="vendor_logo" accept="image/*" required>
      <button type="submit">Upload Logo</button>
      <!-- Note: In upload-logo.php, implement a naming convention (e.g., vendorID_timestamp.ext) to prevent overwriting -->
    </form>
  </section>

  <!-- Feature 4: Update Accepted Payment Methods -->
  <section id="update-payments">
    <h3>Update Accepted Payment Methods</h3>
    <form action="update-payments.php" method="POST">
      <div class="checkbox-group">
        <?php foreach ($currencies as $currency): ?>
          <label>
            <input type="checkbox" name="accepted_payments[]" value="<?= htmlspecialchars($currency['currency_id']); ?>"
              <?= in_array($currency['currency_id'], $accepted) ? 'checked' : ''; ?>>
            <?= htmlspecialchars($currency['currency_name']); ?>
          </label>
        <?php endforeach; ?>
      </div>
      <button type="submit">Update Payment Methods</button>
    </form>
  </section>

  <!-- Feature 5: Update Business Description -->
  <section id="update-description">
    <h3>Update Business Description</h3>
    <form action="update-description.php" method="POST">
      <textarea name="vendor_description" rows="4" cols="50" required><?= htmlspecialchars($vendor['vendor_description']); ?></textarea>
      <br>
      <button type="submit">Update Description</button>
    </form>
  </section>

  <!-- Feature 6: Update Vendor Website -->
  <section id="update-website">
    <h3>Update Vendor Website</h3>
    <form action="update-website.php" method="POST">
      <input type="url" name="vendor_website" value="<?= htmlspecialchars($vendor['vendor_website']); ?>">
      <button type="submit">Update Website</button>
    </form>
  </section>

  <!-- Optional: Include Select2 JS for enhanced multi-select 
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const marketsSelect = document.getElementById('markets');
      if (marketsSelect) {
        $(marketsSelect).select2();
      }
    });
  </script> -->
</body>

</html>
