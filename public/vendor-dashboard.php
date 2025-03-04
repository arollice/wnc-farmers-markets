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

// Retrieve auxiliary data.
$all_markets = Market::fetchAllMarkets();
$currencies = Currency::fetchAllCurrencies();

// Process the form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Process website and description updates.
  $vendor_website = !empty($_POST['vendor_website']) ? trim($_POST['vendor_website']) : $vendor['vendor_website'];
  $vendor_description = !empty($_POST['vendor_description']) ? trim($_POST['vendor_description']) : $vendor['vendor_description'];

  // Process accepted payments from checkboxes.
  $pdo = DatabaseObject::get_database();
  $stmt = $pdo->prepare("DELETE FROM vendor_currency WHERE vendor_id = ?");
  $stmt->execute([$vendor_id]);
  if (isset($_POST['accepted_payments']) && is_array($_POST['accepted_payments'])) {
    $accepted_payments = $_POST['accepted_payments'];
    Currency::associateVendorPayments($vendor_id, $accepted_payments);
  }

  // Process markets update using the vendor_market junction table.
  if (isset($_POST['market_ids']) && is_array($_POST['market_ids'])) {
    $stmt = $pdo->prepare("DELETE FROM vendor_market WHERE vendor_id = ?");
    $stmt->execute([$vendor_id]);
    $stmtInsert = $pdo->prepare("INSERT INTO vendor_market (vendor_id, market_id) VALUES (?, ?)");
    foreach ($_POST['market_ids'] as $market_id) {
      $stmtInsert->execute([$vendor_id, $market_id]);
    }
  }

  // Process file upload for vendor logo.
  $vendor_logo = $vendor['vendor_logo'];
  if (isset($_FILES['vendor_logo']) && $_FILES['vendor_logo']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['vendor_logo']['error'] !== UPLOAD_ERR_OK) {
      // Debug output for file upload error:
      echo "Upload error code: " . $_FILES['vendor_logo']['error'] . "<br>";
      echo "<pre>" . print_r($_FILES['vendor_logo'], true) . "</pre>";
      $_SESSION['error_message'] = "There was an error uploading your file. Error code: " . $_FILES['vendor_logo']['error'];
      header("Location: vendor-dashboard.php");
      exit;
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    // Use FileInfo for MIME type detection:
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
    // Check if the directory exists; if not, show an error.
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
  // Include all necessary fields including vendor_name and status.
  $vendorObj = new Vendor();
  $vendorObj->vendor_id = $vendor_id;
  $vendorObj->vendor_name = $vendor['vendor_name']; // Retain existing vendor name
  $vendorObj->vendor_website = $vendor_website;
  $vendorObj->vendor_description = $vendor_description;
  $vendorObj->vendor_logo = $vendor_logo;
  $vendorObj->status = $vendor['status']; // Retain existing status

  if ($vendorObj->save()) {
    $_SESSION['success_message'] = "Profile updated successfully.";
  } else {
    $_SESSION['error_message'] = "There was an error updating your profile.";
  }

  // Redirect to avoid form resubmission.
  header("Location: vendor-dashboard.php");
  exit;
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

  <!-- Single Form to Update All Vendor Settings -->
  <form action="vendor-dashboard.php" method="POST" enctype="multipart/form-data">

    <!-- Upload Vendor Logo -->
    <section id="upload-logo">
      <h3>Upload Vendor Logo</h3>
      <?php if (!empty($vendor['vendor_logo'])): ?>
        <p>Current Logo:</p>
        <img src="<?= htmlspecialchars($vendor['vendor_logo']); ?>" alt="Vendor Logo" width="150">
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

    <!-- Multi-select for Attending Markets -->
    <section id="update-markets">
      <h3>Select Markets to Attend</h3>
      <label for="markets">Select Markets:</label>
      <select id="markets" name="market_ids[]" multiple="multiple" style="width:300px;">
        <?php
        $currentMarkets = [];
        $vendorMarkets = (new Vendor())->get_markets();
        if (!empty($vendorMarkets)) {
          foreach ($vendorMarkets as $market) {
            $currentMarkets[] = $market['market_id'];
          }
        }
        foreach ($all_markets as $market):
          $selected = in_array($market['market_id'], $currentMarkets) ? 'selected' : '';
        ?>
          <option value="<?= htmlspecialchars($market['market_id']); ?>" <?= $selected; ?>>
            <?= htmlspecialchars($market['market_name']); ?>
          </option>
        <?php endforeach; ?>
      </select>
    </section>
    <hr>

    <button type="submit">Save Changes</button>
  </form>

  <!-- Optionally include Select2 JS for enhanced multi-select -->
</body>

</html>
