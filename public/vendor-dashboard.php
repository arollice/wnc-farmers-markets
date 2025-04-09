<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>WNC Farmers Market - Vendor Dashboard</title>
  <link rel="stylesheet" type="text/css" href="css/farmers-market.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://unpkg.com/leaflet/dist/leaflet.js" defer></script>
  <script src="js/leaflet-map.js" defer></script>
</head>

<body>
  <?php
  include_once('../private/config.php');
  include_once('../private/validation.php');

  if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'vendor') {
    header('Location: login.php');
    exit;
  }

  $userAccount = UserAccount::find_by_id($_SESSION['user_id']);
  if (!$userAccount || empty($userAccount->vendor_id)) {
    header('Location: logout.php');
    exit;
  }

  $vendor_id = $userAccount->vendor_id;

  $vendorData = Vendor::findVendorById($vendor_id);
  if (!$vendorData) {
    Utils::setFlashMessage('error', "Vendor record not found.");
    header("Location: vendor-dashboard.php");
    exit;
  }

  $vendor = new Vendor();
  foreach ($vendorData as $key => $value) {
    $vendor->$key = $value;
  }

  $currentCurrencies = [];
  $acceptedCurrencies = $vendor->get_accepted_currencies();
  if (!empty($acceptedCurrencies)) {
    foreach ($acceptedCurrencies as $currency) {
      $currentCurrencies[] = (int)$currency->currency_id;
    }
  }

  $status = isset($vendor->status) ? $vendor->status : 'Unknown';

  $all_markets = Market::fetchAllMarkets();
  $currencies   = Currency::fetchAllCurrencies();


  $pdo = DatabaseObject::get_database();

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['add_market_btn'])) {

      $market_to_add = intval($_POST['add_market'] ?? 0);
      if (validateMarketId($market_to_add) && $vendor->addMarket($market_to_add)) {
        Utils::setFlashMessage('success', "Market added successfully.");
      } else {
        Utils::setFlashMessage('error', "Invalid market ID or you are already attending that market.");
      }
      header("Location: vendor-dashboard.php");
      exit;
    } elseif (isset($_POST['remove_market_btn'])) {
      // Process removing a market using the Vendor class method.
      $market_to_remove = intval($_POST['remove_market'] ?? 0);
      // Debug output (optional)
      echo "<pre>DEBUG: remove_market_btn pressed. Market to remove: $market_to_remove</pre>";
      if (validateMarketId($market_to_remove) && $vendor->removeMarket($market_to_remove)) {
        Utils::setFlashMessage('success', "Market removed successfully.");
      } else {
        Utils::setFlashMessage('error', "Invalid market ID or an error occurred while removing the market.");
      }
      header("Location: vendor-dashboard.php");
      exit;
    } elseif (isset($_POST['add_item_btn'])) {
      // Process Adding an Item
      $item_name = trim($_POST['item_name']);
      echo "<pre>DEBUG: add_item_btn pressed. Item name: $item_name</pre>";
      if (empty($item_name)) {
        Utils::setFlashMessage('error', "Please enter an item name.");
        header("Location: vendor-dashboard.php");
        exit;
      }

      $corrected_item_name = Item::spellCheck($item_name);

      if ($corrected_item_name && strtolower($corrected_item_name) !== strtolower($item_name) && !isset($_POST['confirm_spell'])) {
        $_SESSION['spell_suggestion'] = [
          'original'   => $item_name,
          'suggestion' => $corrected_item_name
        ];
        header("Location: vendor-dashboard.php");
        exit;
      }

      if (isset($_POST['confirm_spell'])) {
        if ($_POST['confirm_spell'] === 'accept') {
          $item_name = $_SESSION['spell_suggestion']['suggestion'];
        } else { // 'decline'
          $item_name = $_SESSION['spell_suggestion']['original'];
        }
        unset($_SESSION['spell_suggestion']);
      }

      $stmt = $pdo->prepare("SELECT item_id FROM item WHERE LOWER(item_name) = LOWER(?)");
      $stmt->execute([$item_name]);
      $existing_item = $stmt->fetch(PDO::FETCH_ASSOC);
      echo "<pre>DEBUG: Existing item check result: " . print_r($existing_item, true) . "</pre>";

      if ($existing_item) {
        $item_id = $existing_item['item_id'];
      } else {
        $stmt = $pdo->prepare("INSERT INTO item (item_name) VALUES (?)");
        if ($stmt->execute([$item_name])) {
          $item_id = $pdo->lastInsertId();
        } else {
          Utils::setFlashMessage('error', "Error adding new item.");
          echo "<pre>DEBUG: Error inserting new item: $item_name</pre>";
          header("Location: vendor-dashboard.php");
          exit;
        }
      }

      $stmt = $pdo->prepare("SELECT COUNT(*) FROM vendor_item WHERE vendor_id = ? AND item_id = ?");
      $stmt->execute([$vendor_id, $item_id]);
      $count = $stmt->fetchColumn();
      echo "<pre>DEBUG: Number of existing vendor_item links: $count</pre>";
      if ($count == 0) {
        $stmt = $pdo->prepare("INSERT INTO vendor_item (vendor_id, item_id) VALUES (?, ?)");
        if ($stmt->execute([$vendor_id, $item_id])) {
          Utils::setFlashMessage('success', "Item added successfully.");
        } else {
          Utils::setFlashMessage('error', "Error linking item to your profile.");
        }
      } else {
        Utils::setFlashMessage('error', "Item already exists in your profile.");
      }
      header("Location: vendor-dashboard.php");
      exit;
    } elseif (isset($_POST['remove_item_btn'])) {

      $item_id = intval($_POST['item_id'] ?? 0);
      echo "<pre>DEBUG: remove_item_btn pressed. Item ID: $item_id</pre>";
      if ($item_id <= 0) {
        Utils::setFlashMessage('error', "Invalid item ID.");
        header("Location: vendor-dashboard.php");
        exit;
      }
      $stmt = $pdo->prepare("DELETE FROM vendor_item WHERE vendor_id = ? AND item_id = ?");
      if ($stmt->execute([$vendor_id, $item_id])) {
        Utils::setFlashMessage('success', "Item removed successfully.");
      } else {
        Utils::setFlashMessage('error', "Error removing the item.");
      }
      header("Location: vendor-dashboard.php");
      exit;
    } elseif (isset($_POST['update_payments'])) {
      // Process updating accepted payment methods.
      $selectedPayments = $_POST['accepted_payments'] ?? [];
      if ($vendor->associatePayments($selectedPayments)) {
        Utils::setFlashMessage('success', "Payment methods updated successfully.");
      } else {
        Utils::setFlashMessage('error', "Error updating payment methods.");
      }
      header("Location: vendor-dashboard.php#accepted-payments");
      exit;
    } elseif (isset($_POST['update_vendor'])) {

      // Set maximum file size (e.g., 100KB)
      $maxFileSize = 100 * 1024; // 100KB in bytes

      // Validate the uploaded vendor logo file
      if (!Utils::validateFileSize($_FILES['vendor_logo'], $maxFileSize)) {
        Utils::setFlashMessage('error', "The uploaded logo exceeds the maximum allowed size of 100KB.");
        header("Location: vendor-dashboard.php");
        exit;
      }

      $errors = [];
      echo "<pre>DEBUG: update_vendor pressed. POST data:\n" . print_r($_POST, true) . "</pre>";

      $result = $vendor->updateDetails($_POST, $_FILES);

      if (!empty($_POST['current_password']) || !empty($_POST['new_password']) || !empty($_POST['confirm_password'])) {
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
        if (empty($errors)) {
          $newPasswordHash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
          $stmt = $pdo->prepare("UPDATE user_account SET password_hash = ? WHERE user_id = ?");
          if (!$stmt->execute([$newPasswordHash, $_SESSION['user_id']])) {
            $errors[] = "There was an error updating your password.";
          }
        }
      }

      if (!$result['success']) {
        $errors = array_merge($errors, $result['errors']);
      }

      if (empty($errors)) {
        Utils::setFlashMessage('success', "Profile updated successfully." . (!empty($_POST['new_password']) ? " Your password has also been updated." : ""));
      } else {
        Utils::setFlashMessage('error', implode("<br>", $errors));
      }
      echo "<pre>DEBUG: update_vendor processed. Errors: " . print_r($errors, true) . "</pre>";
      header("Location: vendor-dashboard.php");
      exit;
    }
  }
  include_once HEADER_FILE;
  ?>

  <main>
    <header class="dashboard-header">
      <h2>Welcome, <?= htmlspecialchars($_SESSION['username']); ?>!</h2>
      <p>Account Status: <strong><?= htmlspecialchars($status); ?></strong></p>
      <a href="logout.php">Logout</a>
    </header>

    <?php
    Utils::displayFlashMessages();
    Utils::displaySpellSuggestion();
    ?>

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

      <h3>Modify Markets You Are Attending</h3>
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
    </section>

    <section id="vendor-items-list">
      <h3>Your Items for Sale</h3>
      <?php
      $vendorItems = Item::findItemsByVendor($vendor_id);
      if (!empty($vendorItems)) {
        echo '<form action="vendor-dashboard.php" method="POST">';
        echo '<select name="item_id">';
        foreach ($vendorItems as $item) {
          echo '<option value="' . htmlspecialchars($item['item_id']) . '">' . htmlspecialchars($item['item_name']) . '</option>';
        }
        echo '</select>';
        echo '<button type="submit" name="remove_item_btn">Remove Selected Item</button>';
        echo '</form>';
      } else {
        echo "<p>You haven't added any items yet.</p>";
      }
      ?>
    </section>

    <section id="vendor-items">
      <h3>Add Items for Sale</h3>
      <form action="vendor-dashboard.php" method="POST">
        <label for="item_name">Item Name:</label>
        <input type="text" name="item_name" id="item_name" required>
        <button type="submit" name="add_item_btn">Add Item</button>
      </form>
    </section>

    <section id="accepted-payments">
      <h3>Accepted Payment Methods</h3>

      <form action="vendor-dashboard.php" method="POST">
        <fieldset>
          <legend>Modify Payment Methods</legend>
          <?php foreach ($currencies as $currency):
            $currencyId = (int)$currency['currency_id'];
          ?>
            <label style="display:block;">
              <input type="checkbox" name="accepted_payments[]" value="<?= htmlspecialchars($currencyId); ?>"
                <?= in_array($currencyId, $currentCurrencies) ? 'checked="checked"' : '' ?>>
              <?= htmlspecialchars($currency['currency_name']); ?>
            </label>
          <?php endforeach; ?>
        </fieldset>
        <button type="submit" name="update_payments">Update Payment Methods</button>
      </form>
    </section>


    <form action="vendor-dashboard.php" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="update_vendor" value="1">
      <hr>

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

      <section id="upload-logo">
        <h3>Upload Vendor Logo</h3>
        <?php if (!empty($vendor->vendor_logo)): ?>
          <p>Current Logo:</p>
          <img src="<?= htmlspecialchars($vendor->vendor_logo); ?>" alt="Vendor Logo" width="150" height="150"><br>
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
      <button type="submit" name="update_vendor" id="update-vendor">Save Changes</button>
    </form>
  </main>

  <?php include_once FOOTER_FILE; ?>
</body>

</html>
