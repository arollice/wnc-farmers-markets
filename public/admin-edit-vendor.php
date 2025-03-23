<?php
include_once('../private/config.php');
include_once('../private/validation.php');

$pdo = DatabaseObject::get_database();

// Process form submissions.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Process vendor detail update.
  if (isset($_POST['update_vendor'])) {
    $vendor_id = intval($_POST['vendor_id'] ?? 0);
    if (!validateVendorId($vendor_id)) {
      Utils::setFlashMessage('error', "Invalid vendor ID.");
      header("Location: admin.php");
      exit;
    }

    $vendorData = Vendor::findVendorById($vendor_id);
    if (!$vendorData) {
      Utils::setFlashMessage('error', "Vendor not found.");
      header("Location: admin.php");
      exit;
    }

    // Convert vendor data (array) to Vendor object.
    $vendor = new Vendor();
    foreach ($vendorData as $key => $value) {
      $vendor->$key = $value;
    }

    $result = $vendor->updateDetails($_POST, $_FILES);
    if ($result['success']) {
      Utils::setFlashMessage('success', "Vendor updated successfully.");
    } else {
      Utils::setFlashMessage('error', implode("<br>", $result['errors']));
    }
    header("Location: admin-edit-vendor.php?vendor_id=" . $vendor_id);
    exit;
  }
  // Process adding a market using the Vendor class method.
  elseif (isset($_POST['add_market_btn'])) {
    $vendor_id = intval($_POST['vendor_id'] ?? 0);
    $market_to_add = intval($_POST['add_market'] ?? 0);
    if (validateVendorId($vendor_id) && validateMarketId($market_to_add)) {
      $vendorData = Vendor::findVendorById($vendor_id);
      if ($vendorData) {
        $vendor = new Vendor();
        foreach ($vendorData as $key => $value) {
          $vendor->$key = $value;
        }
        if ($vendor->addMarket($market_to_add)) {
          Utils::setFlashMessage('success', "Market added successfully.");
        } else {
          Utils::setFlashMessage('error', "Vendor is already attending that market or an error occurred.");
        }
      }
    }
    header("Location: admin-edit-vendor.php?vendor_id=" . $vendor_id);
    exit;
  }
  // Process removing a market using the Vendor class method.
  elseif (isset($_POST['remove_market_btn'])) {
    $vendor_id = intval($_POST['vendor_id'] ?? 0);
    $market_to_remove = intval($_POST['remove_market'] ?? 0);
    if (validateVendorId($vendor_id) && validateMarketId($market_to_remove)) {
      $vendorData = Vendor::findVendorById($vendor_id);
      if ($vendorData) {
        $vendor = new Vendor();
        foreach ($vendorData as $key => $value) {
          $vendor->$key = $value;
        }
        if ($vendor->removeMarket($market_to_remove)) {
          Utils::setFlashMessage('success', "Market removed successfully.");
        } else {
          Utils::setFlashMessage('error', "An error occurred while removing the market.");
        }
      }
    }
    header("Location: admin-edit-vendor.php?vendor_id=" . $vendor_id);
    exit;
  }
}

// For GET requests, retrieve vendor data.
$vendor_id = intval($_GET['vendor_id'] ?? 0);
if (!validateVendorId($vendor_id)) {
  Utils::setFlashMessage('error', "Invalid vendor ID.");
  header("Location: admin.php");
  exit;
}

$vendorData = Vendor::findVendorById($vendor_id);
if (!$vendorData) {
  Utils::setFlashMessage('error', "Vendor not found.");
  header("Location: admin.php");
  exit;
}

// Convert vendor data (array) to Vendor object.
$vendor = new Vendor();
foreach ($vendorData as $key => $value) {
  $vendor->$key = $value;
}

include HEADER_FILE;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>Edit Vendor</title>
</head>

<body>
  <main>
    <header class="dashboard-header">
      <h2>Edit Vendor: <?= htmlspecialchars($vendor->vendor_name); ?></h2>

      <p><a href="admin.php">Back to Admin Dashboard</a></p>
    </header>

    <?php Utils::displayFlashMessages(); ?>


    <!-- Main Vendor Update Form -->
    <form action="admin-edit-vendor.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="vendor_id" value="<?= htmlspecialchars($vendor_id); ?>">

      <label for="vendor_name">Vendor Name:</label>
      <input type="text" name="vendor_name" id="vendor_name" value="<?= htmlspecialchars($vendor->vendor_name); ?>" required><br>

      <label for="vendor_website">Website:</label>
      <input type="url" name="vendor_website" id="vendor_website" value="<?= htmlspecialchars($vendor->vendor_website); ?>"><br>

      <label for="vendor_description">Description:</label>
      <textarea name="vendor_description" id="vendor_description" rows="4" cols="50"><?= htmlspecialchars($vendor->vendor_description); ?></textarea><br>

      <!-- Optional: Logo Update -->
      <?php if (!empty($vendor->vendor_logo)): ?>
        <p>Current Logo:</p>
        <img src="<?= htmlspecialchars($vendor->vendor_logo); ?>" alt="Vendor Logo" width="150" id="admin-edit-logo"><br>
        <label for="delete_logo">
          <input type="checkbox" name="delete_logo" id="delete_logo" value="1"> Delete current logo
        </label><br>
      <?php endif; ?>
      <label for="vendor_logo">Select New Logo (optional):</label>
      <input type="file" name="vendor_logo" id="vendor_logo" accept="image/*"><br>

      <button type="submit" name="update_vendor">Save Changes</button>
    </form>

    <hr>

    <!-- Markets Section -->
    <?php
    // Fetch vendor markets once.
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

    <!-- Form to Add a Market -->
    <?php
    $all_markets = Market::fetchAllMarkets();
    $vendorMarkets = Vendor::findMarketsByVendor($vendor_id);
    $currentMarketIds = [];
    if (!empty($vendorMarkets)) {
      foreach ($vendorMarkets as $market) {
        $currentMarketIds[] = $market['market_id'];
      }
    }

    // Build array of available markets (those not already added)
    $available_markets = [];
    foreach ($all_markets as $market) {
      if (!in_array($market['market_id'], $currentMarketIds)) {
        $available_markets[] = $market;
      }
    }
    ?>
    <form action="vendor-dashboard.php" method="POST">
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


    <br>

    <!-- Form to Remove a Market -->
    <form action="admin-edit-vendor.php" method="POST">
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
  </main>
</body>

<?php include FOOTER_FILE; ?>

</html>
