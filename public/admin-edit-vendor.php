<?php
include_once('../private/config.php');
include_once('../private/validation.php');

$currencies = Currency::fetchAllCurrencies();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}

// Determine vendor ID and sanitize inputs
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $_POST = Utils::sanitize($_POST);
  $vendor_id = intval($_POST['vendor_id'] ?? 0);
} else {
  $vendor_id = intval($_GET['vendor_id'] ?? 0);
}

if (!validateVendorId($vendor_id)) {
  Utils::setFlashMessage('error', "Invalid vendor ID.");
  header("Location: admin.php");
  exit;
}

// Retrieve vendor data
$vendorData = Vendor::findVendorById($vendor_id);
if (!$vendorData) {
  Utils::setFlashMessage('error', "Vendor not found.");
  header("Location: admin.php");
  exit;
}
$vendor = new Vendor();
foreach ($vendorData as $key => $value) {
  $vendor->$key = $value;
}

// Build the current currencies array
$currentCurrencies = [];
$acceptedCurrencies = $vendor->get_accepted_currencies();
foreach ($acceptedCurrencies as $currency) {
  $currentCurrencies[] = (int)$currency->currency_id;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // CSRF
  if (!Utils::validateCsrf($_POST['csrf_token'] ?? null)) {
    Utils::setFlashMessage('error', 'Invalid form submission.');
    header("Location: admin-edit-vendor.php?vendor_id={$vendor_id}");
    exit;
  }

  if (isset($_POST['update_vendor'])) {
    UserAccount::updateVendor($vendor, $_POST, $_FILES, $vendor_id);
  } elseif (isset($_POST['add_market_btn'])) {
    UserAccount::addMarket($vendor, $vendor_id, intval($_POST['add_market'] ?? 0));
  } elseif (isset($_POST['remove_market_btn'])) {
    UserAccount::removeMarket($vendor, $vendor_id, intval($_POST['remove_market'] ?? 0));
  } elseif (isset($_POST['update_payments'])) {
    UserAccount::updatePayments($vendor, $vendor_id, $_POST['accepted_payments'] ?? []);
  }

  header("Location: admin-edit-vendor.php?vendor_id={$vendor_id}");
  exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>WNC Farmers Market - Admin Edit Vendor</title>
  <script src="js/farmers-market.js" defer></script>
  <link rel="icon" type="image/svg+xml" href="img/wnc-favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="img/wnc-favicon32.png">
  <link rel="stylesheet" type="text/css" href="css/farmers-market.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
  <?php
  include HEADER_FILE;
  ?>

  <main>
    <header class="dashboard-header">
      <h2>Edit Vendor: <?= htmlspecialchars($vendor->vendor_name); ?></h2>
      <p><a href="admin.php">&larr; Back to Admin Dashboard</a></p>
    </header>
    <?php Utils::displayFlashMessages(); ?>

    <!-- Main Vendor Update Form -->
    <form action="admin-edit-vendor.php" method="POST" enctype="multipart/form-data">
      <?= Utils::csrfInputTag() ?>
      <input type="hidden" name="vendor_id" value="<?= htmlspecialchars($vendor_id); ?>">
      <label for="vendor_name">Vendor Name:</label>
      <input type="text" name="vendor_name" id="vendor_name" value="<?= htmlspecialchars($vendor->vendor_name); ?>" required><br>
      <label for="vendor_website">Website:</label>
      <input type="url" name="vendor_website" id="vendor_website" value="<?= htmlspecialchars($vendor->vendor_website); ?>"><br>
      <label for="vendor_description">Description:</label>
      <textarea name="vendor_description" id="vendor_description" rows="4" cols="50"><?= htmlspecialchars($vendor->vendor_description); ?></textarea>
      <br>

      <!-- Logo Update -->
      <?php if (!empty($vendor->vendor_logo)): ?>
        <p>Current Logo:</p>
        <img src="<?= htmlspecialchars($vendor->vendor_logo); ?>" alt="Vendor Logo" width="150" id="admin-edit-logo"><br>
        <label for="delete_logo">
          <input type="checkbox" name="delete_logo" id="delete_logo" value="1"> Delete current logo
        </label>
        <br>
      <?php endif; ?>
      <label for="vendor_logo">Select New Logo (optional):</label>
      <input type="file" name="vendor_logo" id="vendor_logo" accept="image/*"><br>
      <button type="submit" name="update_vendor">Save Changes</button>
    </form>

    <!-- Markets Section -->
    <?php
    $vendorMarkets = Vendor::findMarketsByVendor($vendor_id);
    ?>
    <h3>Markets Vendor is Attending</h3>
    <?php
    if (!empty($vendorMarkets)) {
      echo "<ul>";
      foreach ($vendorMarkets as $market) {
        echo "<li>" . htmlspecialchars($market['market_name']) . "</li>";
      }
      echo "</ul>";
    } else {
      echo "<p>Vendor is not attending any markets.</p>";
    }
    ?>

    <!-- Add a Market -->
    <?php
    $all_markets = Market::fetchAllMarkets();
    $vendorMarkets = Vendor::findMarketsByVendor($vendor_id);
    $currentMarketIds = [];
    if (!empty($vendorMarkets)) {
      foreach ($vendorMarkets as $market) {
        $currentMarketIds[] = $market['market_id'];
      }
    }

    // Build array of markets to add
    $available_markets = [];
    foreach ($all_markets as $market) {
      if (!in_array($market['market_id'], $currentMarketIds)) {
        $available_markets[] = $market;
      }
    }
    ?>
    <form action="admin-edit-vendor.php" method="POST">
      <?= Utils::csrfInputTag() ?>
      <input type="hidden" name="vendor_id" value="<?= htmlspecialchars($vendor_id); ?>">
      <label for="add_market">Add a Market:</label>
      <select name="add_market" id="add_market" <?php if (empty($available_markets)) echo 'disabled'; ?>>
        <?php
        if (empty($available_markets)) {
          echo '<option value="">All markets added</option>';
        } else {
          foreach ($available_markets as $market) {
            echo '<option value="' . htmlspecialchars($market['market_id']) . '">' . htmlspecialchars($market['market_name']) . '</option>';
          }
        }
        ?>
      </select>
      <button type="submit" name="add_market_btn" <?php if (empty($available_markets)) echo 'disabled'; ?>>Add Market</button>
    </form>

    <!-- Remove a Market -->
    <form action="admin-edit-vendor.php" method="POST">
      <?= Utils::csrfInputTag() ?>
      <input type="hidden" name="vendor_id" value="<?= htmlspecialchars($vendor_id); ?>">
      <label for="remove_market">Remove a Market:</label>
      <select name="remove_market" id="remove_market">
        <?php
        if (!empty($vendorMarkets)) {
          foreach ($vendorMarkets as $market) {
            echo '<option value="' . htmlspecialchars($market['market_id']) . '">' . htmlspecialchars($market['market_name']) . '</option>';
          }
        }
        ?>
      </select>
      <button type="submit" name="remove_market_btn">Remove Market</button>
    </form>

    <!-- Accepted Payment Methods -->
    <section id="accepted-payment-methods">
      <h3>Accepted Payment Methods for <?= htmlspecialchars($vendor->vendor_name); ?></h3>
      <form action="admin-edit-vendor.php" method="POST">
        <?= Utils::csrfInputTag() ?>
        <input type="hidden" name="vendor_id" value="<?= htmlspecialchars($vendor_id); ?>">
        <fieldset>
          <legend>Modify Payment Methods</legend>
          <?php foreach ($currencies as $currency):
            $currencyId = (int)$currency['currency_id'];
          ?>
            <label>
              <input type="checkbox" name="accepted_payments[]" value="<?= htmlspecialchars($currencyId); ?>"
                <?= in_array($currencyId, $currentCurrencies) ? 'checked="checked"' : '' ?>>
              <?= htmlspecialchars($currency['currency_name']); ?>
            </label>
          <?php endforeach; ?>
        </fieldset>
        <button type="submit" name="update_payments">Update Payment Methods</button>
      </form>
    </section>
  </main>

  <?php include FOOTER_FILE; ?>
</body>

</html>
