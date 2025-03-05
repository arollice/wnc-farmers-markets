<?php
include_once('../private/config.php');
include_once('../private/validation.php');
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

// Get vendor data as an array.
$vendorData = Vendor::findVendorById($vendor_id);
if (!$vendorData) {
  $_SESSION['error_message'] = "Vendor record not found.";
  header("Location: vendor-dashboard.php");
  exit;
}
// Convert the array to a Vendor object.
$vendor = new Vendor();
foreach ($vendorData as $key => $value) {
  $vendor->$key = $value;
}

$status = isset($vendor->status) ? $vendor->status : 'Unknown';

$all_markets = Market::fetchAllMarkets();
$currencies   = Currency::fetchAllCurrencies();

$pdo = DatabaseObject::get_database();

// --- Process Form Submissions --- //
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  // Process adding a market using the Vendor class method.
  if (isset($_POST['add_market_btn'])) {
    $market_to_add = intval($_POST['add_market'] ?? 0);
    if (validateMarketId($market_to_add) && $vendor->addMarket($market_to_add)) {
      $_SESSION['success_message'] = "Market added successfully.";
    } else {
      $_SESSION['error_message'] = "Invalid market ID or you are already attending that market.";
    }
    header("Location: vendor-dashboard.php");
    exit;
  }

  // Process removing a market using the Vendor class method.
  elseif (isset($_POST['remove_market_btn'])) {
    $market_to_remove = intval($_POST['remove_market'] ?? 0);
    if (validateMarketId($market_to_remove) && $vendor->removeMarket($market_to_remove)) {
      $_SESSION['success_message'] = "Market removed successfully.";
    } else {
      $_SESSION['error_message'] = "Invalid market ID or an error occurred while removing the market.";
    }
    header("Location: vendor-dashboard.php");
    exit;
  }
  // Process main vendor update and optional password change.
  elseif (isset($_POST['update_vendor'])) {
    $errors = [];

    // Update vendor details using the Vendor class method.
    $result = $vendor->updateDetails($_POST, $_FILES);

    // Process password change if any of the password fields are provided.
    if (!empty($_POST['current_password']) || !empty($_POST['new_password']) || !empty($_POST['confirm_password'])) {
      // Ensure all password fields are provided.
      if (empty($_POST['current_password']) || empty($_POST['new_password']) || empty($_POST['confirm_password'])) {
        $errors[] = "Please fill in all password fields.";
      }
      if ($_POST['new_password'] !== $_POST['confirm_password']) {
        $errors[] = "New password and confirmation do not match.";
      }
      if (strlen($_POST['new_password']) < 8) {
        $errors[] = "New password must be at least 8 characters long.";
      }
      if (!password_verify($_POST['current_password'], $userAccount->password_hash)) {
        $errors[] = "Current password is incorrect.";
      }
      // If no password errors, update the password.
      if (empty($errors)) {
        $newPasswordHash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE user_account SET password_hash = ? WHERE user_id = ?");
        if (!$stmt->execute([$newPasswordHash, $_SESSION['user_id']])) {
          $errors[] = "There was an error updating your password.";
        }
      }
    }

    // Combine errors from updateDetails() with password errors.
    if (!$result['success']) {
      $errors = array_merge($errors, $result['errors']);
    }

    if (empty($errors)) {
      $_SESSION['success_message'] = "Profile updated successfully." .
        (!empty($_POST['new_password']) ? " Your password has also been updated." : "");
    } else {
      $_SESSION['error_message'] = implode("<br>", $errors);
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
</head>

<body>
  <h2>Welcome, <?= htmlspecialchars($_SESSION['username']); ?>!</h2>
  <p>Account Status: <strong><?= htmlspecialchars($status); ?></strong></p>
  <a href="logout.php">Logout</a>

  <!-- Session Messages -->
  <?php
  if (isset($_SESSION['success_message'])) {
    echo "<div style='padding:10px; background:#d4edda; color:#155724; border:1px solid #c3e6cb; margin-bottom:10px;'>";
    echo htmlspecialchars($_SESSION['success_message']);
    echo "</div>";
    unset($_SESSION['success_message']);
  }
  if (isset($_SESSION['error_message'])) {
    echo "<div style='padding:10px; background:#f8d7da; color:#721c24; border:1px solid #f5c6cb; margin-bottom:10px;'>";
    echo htmlspecialchars($_SESSION['error_message']);
    echo "</div>";
    unset($_SESSION['error_message']);
  }
  ?>

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

  <!-- Incremental Market Update Forms -->
  <h3>Modify Markets You Are Attending</h3>
  <!-- Form to Add a Market -->
  <form action="vendor-dashboard.php" method="POST">
    <label for="add_market">Add a Market:</label>
    <select name="add_market" id="add_market">
      <?php
      $all_markets = Market::fetchAllMarkets();
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
    <button type="submit" name="add_market_btn">Add Market</button>
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
    <button type="submit" name="remove_market_btn">Remove Market</button>
  </form>

  <!-- Main Form to Update Vendor Details & Change Password -->
  <form action="vendor-dashboard.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="update_vendor" value="1">
    <hr>
    <!-- Update Vendor Details Section -->
    <section id="update-details">
      <h3>Update Vendor Details</h3>
      <label for="vendor_name">Vendor Name:</label>
      <input type="text" name="vendor_name" id="vendor_name" value="<?= htmlspecialchars($vendor->vendor_name); ?>" required><br>

      <label for="vendor_website">Website URL:</label>
      <input type="url" name="vendor_website" id="vendor_website" value="<?= htmlspecialchars($vendor->vendor_website); ?>"><br>

      <label for="vendor_description">Business Description (max 255 characters):</label>
      <textarea name="vendor_description" id="vendor_description" rows="4" cols="50" required><?= htmlspecialchars($vendor->vendor_description); ?></textarea>
    </section>

    <hr>
    <!-- Optional: Logo Update Section -->
    <section id="upload-logo">
      <h3>Upload Vendor Logo</h3>
      <?php if (!empty($vendor->vendor_logo)): ?>
        <p>Current Logo:</p>
        <img src="<?= htmlspecialchars($vendor->vendor_logo); ?>" alt="Vendor Logo" width="150"><br>
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
    <!-- Password Change Section -->
    <section id="change-password">
      <h3>Change Password</h3>
      <p>If you wish to change your password, please fill in all fields below.</p>
      <label for="current_password">Current Password:</label>
      <input type="password" name="current_password" id="current_password"><br>

      <label for="new_password">New Password:</label>
      <input type="password" name="new_password" id="new_password"><br>

      <label for="confirm_password">Confirm New Password:</label>
      <input type="password" name="confirm_password" id="confirm_password"><br>
    </section>

    <hr>
    <button type="submit" name="update_vendor">Save Changes</button>
  </form>
</body>

</html>
