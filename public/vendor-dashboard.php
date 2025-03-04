<?php
// vendor-dashboard.php
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

// Retrieve the user account record and vendor information.
$userAccount = UserAccount::find_by_id($_SESSION['user_id']);
if (!$userAccount || empty($userAccount->vendor_id)) {
  header('Location: logout.php');
  exit;
}
$vendor_id = $userAccount->vendor_id;
$vendor = Vendor::findVendorById($vendor_id);
if (!$vendor) {
  $_SESSION['error_message'] = "Vendor record not found.";
  header("Location: vendor-dashboard.php");
  exit;
}
$status = isset($vendor['status']) ? $vendor['status'] : 'Unknown';

$all_markets = Market::fetchAllMarkets();
$currencies = Currency::fetchAllCurrencies();

$pdo = DatabaseObject::get_database();

// --- Process Form Submissions --- //
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Process adding a single market.
  if (isset($_POST['add_market'])) {
    $market_to_add = intval($_POST['add_market']);
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM vendor_market WHERE vendor_id = ? AND market_id = ?");
    $stmt->execute([$vendor_id, $market_to_add]);
    if ($stmt->fetchColumn() == 0) {
      $stmt = $pdo->prepare("INSERT INTO vendor_market (vendor_id, market_id) VALUES (?, ?)");
      $stmt->execute([$vendor_id, $market_to_add]);
      $_SESSION['success_message'] = "Market added successfully.";
    } else {
      $_SESSION['error_message'] = "You are already attending that market.";
    }
    header("Location: vendor-dashboard.php");
    exit;
  }
  // Process removing a single market.
  elseif (isset($_POST['remove_market'])) {
    $market_to_remove = intval($_POST['remove_market']);
    $stmt = $pdo->prepare("DELETE FROM vendor_market WHERE vendor_id = ? AND market_id = ?");
    $stmt->execute([$vendor_id, $market_to_remove]);
    $_SESSION['success_message'] = "Market removed successfully.";
    header("Location: vendor-dashboard.php");
    exit;
  }
  // Process main vendor update.
  elseif (isset($_POST['update_vendor'])) {

    $vendor_website = isset($_POST['vendor_website']) ? trim($_POST['vendor_website']) : null;
    if ($vendor_website === '') {
      $vendor_website = null;
    }
    $vendor_description = !empty($_POST['vendor_description']) ? trim($_POST['vendor_description']) : $vendor['vendor_description'];

    // Process accepted payments from checkboxes.
    $stmt = $pdo->prepare("DELETE FROM vendor_currency WHERE vendor_id = ?");
    $stmt->execute([$vendor_id]);
    if (isset($_POST['accepted_payments']) && is_array($_POST['accepted_payments'])) {
      $accepted_payments = $_POST['accepted_payments'];
      Currency::associateVendorPayments($vendor_id, $accepted_payments);
    }

    // Process logo deletion and file upload.
    $vendor_logo = $vendor['vendor_logo'];
    if (isset($_POST['delete_logo']) && $_POST['delete_logo'] == '1') {
      if (!empty($vendor_logo)) {
        $filePath = PROJECT_ROOT . '/public/' . $vendor_logo;
        if (file_exists($filePath)) {
          unlink($filePath);
        }
      }
      $vendor_logo = '';
    } elseif (isset($_FILES['vendor_logo']) && $_FILES['vendor_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
      if ($_FILES['vendor_logo']['error'] !== UPLOAD_ERR_OK) {
        echo "Upload error code: " . $_FILES['vendor_logo']['error'] . "<br>";
        echo "<pre>" . print_r($_FILES['vendor_logo'], true) . "</pre>";
        $_SESSION['error_message'] = "There was an error uploading your file. Error code: " . $_FILES['vendor_logo']['error'];
        header("Location: vendor-dashboard.php");
        exit;
      }
      $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
      $finfo = finfo_open(FILEINFO_MIME_TYPE);
      $mime_type = finfo_file($finfo, $_FILES['vendor_logo']['tmp_name']);
      finfo_close($finfo);
      if (!in_array($mime_type, $allowed_types)) {
        $_SESSION['error_message'] = "Only JPG, PNG, and GIF files are allowed. Detected type: $mime_type";
        header("Location: vendor-dashboard.php");
        exit;
      }
      $maxSize = 2 * 1024 * 1024; // 2MB limit
      if ($_FILES['vendor_logo']['size'] > $maxSize) {
        $_SESSION['error_message'] = "The file is too large. Maximum allowed size is 2MB.";
        header("Location: vendor-dashboard.php");
        exit;
      }
      $target_dir = UPLOADS_PATH;
      if (!is_dir($target_dir)) {
        $_SESSION['error_message'] = "Upload directory does not exist: $target_dir";
        header("Location: vendor-dashboard.php");
        exit;
      }
      $extension = strtolower(pathinfo($_FILES["vendor_logo"]["name"], PATHINFO_EXTENSION));
      $unique_name = "vendor_" . $vendor_id . "_" . time() . "_" . uniqid() . "." . $extension;
      $target_file = $target_dir . '/' . $unique_name;
      if (!move_uploaded_file($_FILES["vendor_logo"]["tmp_name"], $target_file)) {
        $_SESSION['error_message'] = "There was an error uploading your file. Please try again.";
        header("Location: vendor-dashboard.php");
        exit;
      }
      $vendor_logo = 'uploads/' . $unique_name;
    }

    // Update the vendor record.
    $vendorObj = new Vendor();
    $vendorObj->vendor_id = $vendor_id;
    $vendorObj->vendor_name = $vendor['vendor_name'];
    $vendorObj->vendor_website = $vendor_website;
    $vendorObj->vendor_description = $vendor_description;
    $vendorObj->vendor_logo = $vendor_logo;
    $vendorObj->status = $vendor['status'];

    if ($vendorObj->save()) {
      $_SESSION['success_message'] = "Profile updated successfully.";
    } else {
      $_SESSION['error_message'] = "There was an error updating your profile.";
    }
    header("Location: vendor-dashboard.php");
    exit;
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Vendor Dashboard</title>
  <!-- Optionally include Select2 CSS for enhanced multi-select -->
</head>

<body>
  <h2>Welcome, <?= htmlspecialchars($_SESSION['username']); ?>!</h2>
  <p>Account Status: <strong><?= htmlspecialchars($status); ?></strong></p>
  <a href="logout.php">Logout</a>

  <!-- Session Messages -->
  <?php
  if (isset($_SESSION['success_message'])) {
    echo "<div style='padding: 10px; background: #d4edda; color: #155724; border: 1px solid #c3e6cb; margin-bottom: 10px;'>";
    echo htmlspecialchars($_SESSION['success_message']);
    echo "</div>";
    unset($_SESSION['success_message']);
  }
  if (isset($_SESSION['error_message'])) {
    echo "<div style='padding: 10px; background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; margin-bottom: 10px;'>";
    echo htmlspecialchars($_SESSION['error_message']);
    echo "</div>";
    unset($_SESSION['error_message']);
  }
  ?>

  <!-- Main Form to Update Vendor Details -->
  <form action="vendor-dashboard.php" method="POST" enctype="multipart/form-data">
    <!-- Hidden field to identify main form submission -->
    <input type="hidden" name="update_vendor" value="1">

    <!-- Upload Vendor Logo -->
    <section id="upload-logo">
      <h3>Upload Vendor Logo</h3>
      <?php if (!empty($vendor['vendor_logo'])): ?>
        <p>Current Logo:</p>
        <img src="<?= htmlspecialchars($vendor['vendor_logo']); ?>" alt="Vendor Logo" width="150">
        <!-- Delete Logo Checkbox -->
        <br>
        <label for="delete_logo">
          <input type="checkbox" name="delete_logo" id="delete_logo" value="1">
          Delete current logo
        </label>
      <?php endif; ?>
      <br>
      <label for="vendor_logo">Select New Logo (optional):</label>
      <input type="file" id="vendor_logo" name="vendor_logo" accept="image/*">
    </section>
    <hr>

    <!-- Update Vendor Website -->
    <section id="update-website">
      <h3>Update Vendor Website</h3>
      <label for="vendor_website">Website URL:</label>
      <input type="url" id="vendor_website" name="vendor_website" value="<?= htmlspecialchars($vendor['vendor_website']); ?>">
    </section>
    <hr>

    <!-- Update Business Description -->
    <section id="update-description">
      <h3>Update Business Description</h3>
      <textarea name="vendor_description" rows="4" cols="50" required><?= htmlspecialchars($vendor['vendor_description']); ?></textarea>
    </section>
    <hr>

    <!-- Update Accepted Payment Methods -->
    <section id="update-payments">
      <h3>Update Accepted Payment Methods</h3>
      <div class="checkbox-group">
        <?php
        $currentPayments = Currency::findPaymentMethodsByVendor($vendor_id);
        foreach ($currencies as $currency):
          $isChecked = in_array($currency['currency_name'], $currentPayments) ? 'checked' : '';
        ?>
          <label>
            <input type="checkbox" name="accepted_payments[]" value="<?= htmlspecialchars($currency['currency_id']); ?>" <?= $isChecked; ?>>
            <?= htmlspecialchars($currency['currency_name']); ?>
          </label>
          <br>
        <?php endforeach; ?>
      </div>
    </section>
    <hr>

    <button type="submit">Save Changes</button>
  </form>
  <hr>

  <!-- Display Current Markets -->
  <section id="current-markets">
    <h3>Markets You Are Attending</h3>
    <?php
    $vendorMarkets = Vendor::findMarketsByVendor($vendor_id);
    if (!empty($vendorMarkets)) {
      echo "<ul>";
      foreach ($vendorMarkets as $market) {
        echo "<li>" . htmlspecialchars($market['market_name']) . "</li>";
      }
      echo "</ul>";
    } else {
      echo "<p>You are not attending any markets.</p>";
    }
    ?>
  </section>
  <hr>

  <!-- Incremental Market Update Forms -->
  <h3>Modify Markets You Are Attending</h3>
  <!-- Form to Add a Market -->
  <form action="vendor-dashboard.php" method="POST">
    <label for="add_market">Add a Market:</label>
    <select name="add_market" id="add_market">
      <?php
      // List markets not currently attended.
      $vendorMarkets = Vendor::findMarketsByVendor($vendor_id);
      $currentMarketIds = [];
      if (!empty($vendorMarkets)) {
        foreach ($vendorMarkets as $market) {
          $currentMarketIds[] = $market['market_id'];
        }
      }
      foreach ($all_markets as $market) {
        if (!in_array($market['market_id'], $currentMarketIds)) {
          echo '<option value="' . htmlspecialchars($market['market_id']) . '">' . htmlspecialchars($market['market_name']) . '</option>';
        }
      }
      ?>
    </select>
    <button type="submit">Add Market</button>
  </form>
  <br>
  <!-- Form to Remove a Market -->
  <form action="vendor-dashboard.php" method="POST">
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
    <button type="submit">Remove Market</button>
  </form>

  <!-- Optionally include Select2 JS for enhanced multi-select -->
</body>

</html>
