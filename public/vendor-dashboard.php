<?php
include_once('../private/config.php');
include_once('../private/validation.php');

// Ensure the user is logged in as a vendor
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

// Fetch vendor data
$vendorData = Vendor::findVendorById($vendor_id);
if (!$vendorData) {
  Utils::setFlashMessage('error', "Vendor record not found.");
  header("Location: logout.php");
  exit;
}

$vendor = new Vendor();
foreach ($vendorData as $key => $value) {
  $vendor->$key = $value;
}

// Prepare supporting data
$currentCurrencies = [];
foreach ($vendor->get_accepted_currencies() as $currency) {
  $currentCurrencies[] = (int)$currency->currency_id;
}
$status      = $vendor->status ?? 'Unknown';
$all_markets = Market::fetchAllMarkets();
$currencies  = Currency::fetchAllCurrencies();
$pdo         = DatabaseObject::get_database();

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // CSRF & sanitize
  if (!Utils::validateCsrf($_POST['csrf_token'] ?? null)) {
    Utils::setFlashMessage('error', 'Invalid form submission.');
    header('Location: vendor-dashboard.php');
    exit;
  }
  $_POST = Utils::sanitize($_POST);

  // Add market
  if (isset($_POST['add_market_btn'])) {
    $market_to_add = intval($_POST['add_market'] ?? 0);
    $errors = validateMarketSelection([$market_to_add]);
    if (empty($errors)) {
      $vendor->addMarket($market_to_add)
        ? Utils::setFlashMessage('success', "Market added successfully.")
        : Utils::setFlashMessage('error', "You're already attending that market.");
    } else {
      Utils::setFlashMessage('error', array_shift($errors));
    }
    header("Location: vendor-dashboard.php");
    exit;
  }
  // Remove market
  elseif (isset($_POST['remove_market_btn'])) {
    $market_to_remove = intval($_POST['remove_market'] ?? 0);
    if (validateMarketId($market_to_remove) && $vendor->removeMarket($market_to_remove)) {
      Utils::setFlashMessage('success', "Market removed successfully.");
    } else {
      Utils::setFlashMessage('error', "Invalid market ID or an error occurred.");
    }
    header("Location: vendor-dashboard.php");
    exit;
  }
  // Add item
  elseif (isset($_POST['add_item_btn'])) {
    $item_name = trim($_POST['item_name'] ?? '');
    if ($item_name === '' || mb_strlen($item_name) > 50) {
      Utils::setFlashMessage('error', "Item name is required (max 50 chars).");
      header("Location: vendor-dashboard.php");
      exit;
    }

    // Spell-check suggestion
    $corrected = Item::spellCheck($item_name);
    if (
      $corrected
      && strcasecmp($corrected, $item_name) !== 0
      && !isset($_POST['confirm_spell'])
    ) {
      $_SESSION['spell_suggestion'] = [
        'original'   => $item_name,
        'suggestion' => $corrected
      ];
      header("Location: vendor-dashboard.php");
      exit;
    }

    // If user accepted/declined suggestion
    if (isset($_POST['confirm_spell'])) {
      if ($_POST['confirm_spell'] === 'accept') {
        $item_name = $_SESSION['spell_suggestion']['suggestion'];
      } else {
        $item_name = $_SESSION['spell_suggestion']['original'];
      }
      unset($_SESSION['spell_suggestion']);
    }

    // Look up or create the item
    $stmt = $pdo->prepare("SELECT item_id FROM item WHERE LOWER(item_name)=LOWER(?)");
    $stmt->execute([$item_name]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
      $item_id = $row['item_id'];
    } else {
      $stmt = $pdo->prepare("INSERT INTO item (item_name) VALUES (?)");
      if (! $stmt->execute([$item_name])) {
        Utils::setFlashMessage('error', "Error adding new item.");
        header("Location: vendor-dashboard.php");
        exit;
      }
      $item_id = $pdo->lastInsertId();
    }

    // Link to vendor if not already present
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM vendor_item WHERE vendor_id=? AND item_id=?");
    $stmt->execute([$vendor_id, $item_id]);
    if ($stmt->fetchColumn() == 0) {
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
  }

  // Remove item
  elseif (isset($_POST['remove_item_btn'])) {
    $item_id = intval($_POST['item_id'] ?? 0);
    if ($item_id <= 0) {
      Utils::setFlashMessage('error', "Invalid item ID.");
    } else {
      $stmt = $pdo->prepare("DELETE FROM vendor_item WHERE vendor_id = ? AND item_id = ?");
      $stmt->execute([$vendor_id, $item_id])
        ? Utils::setFlashMessage('success', "Item removed successfully.")
        : Utils::setFlashMessage('error', "Error removing the item.");
    }
    header("Location: vendor-dashboard.php");
    exit;
  }
  // Update payments
  elseif (isset($_POST['update_payments'])) {
    $selected = $_POST['accepted_payments'] ?? [];
    $errors   = validatePaymentSelection($selected);
    if (empty($errors)) {
      $vendor->associatePayments($selected)
        ? Utils::setFlashMessage('success', "Payment methods updated successfully.")
        : Utils::setFlashMessage('error', "Error updating payment methods.");
    } else {
      Utils::setFlashMessage('error', array_shift($errors));
    }
    header("Location: vendor-dashboard.php#accepted-payments");
    exit;
  }
  // Update vendor profile
  elseif (isset($_POST['update_vendor'])) {
    // File size validation
    $maxFileSize = 100 * 1024;
    if (!Utils::validateFileSize($_FILES['vendor_logo'], $maxFileSize)) {
      Utils::setFlashMessage('error', "The uploaded logo exceeds 100KB.");
      header("Location: vendor-dashboard.php");
      exit;
    }
    $errors = [];
    // Vendor name
    $name = trim($_POST['vendor_name'] ?? '');
    if ($name === '') {
      $errors['vendor_name'] = "Vendor name can't be blank.";
    } elseif (mb_strlen($name) > 100) {
      $errors['vendor_name'] = "Vendor name must be under 100 characters.";
    }
    // Website (optional)
    $website = trim($_POST['vendor_website'] ?? '');
    if ($website !== '' && !filter_var($website, FILTER_VALIDATE_URL)) {
      $errors['vendor_website'] = "Please enter a valid URL (http:// or https://).";
    }
    // Description
    $desc = trim($_POST['vendor_description'] ?? '');
    if (mb_strlen($desc) > 255) {
      $errors['vendor_description'] = "Description must be at most 255 characters.";
    }
    // Password change (if any)
    $curr = $_POST['current_password'] ?? '';
    $new  = $_POST['new_password']     ?? '';
    $conf = $_POST['confirm_password'] ?? '';
    if ($curr || $new || $conf) {
      if ($curr === '' || $new === '' || $conf === '') {
        $errors['password'] = "Fill in all password fields to change password.";
      } elseif ($new !== $conf) {
        $errors['password'] = "New password and confirmation do not match.";
      } elseif (strlen($new) < 8) {
        $errors['password'] = "New password must be at least 8 characters.";
      } elseif (!password_verify($curr, $userAccount->password_hash)) {
        $errors['password'] = "Current password is incorrect.";
      }
    }
    // Persist if no errors
    if (empty($errors)) {
      $result = $vendor->updateDetails($_POST, $_FILES);
      if (!$result['success']) {
        $errors = array_merge($errors, $result['errors']);
      }
    }
    // Flash result & redirect
    if (empty($errors)) {
      $msg = "Profile updated successfully.";
      if (!empty($new)) {
        $msg .= " Your password has also been changed.";
      }
      Utils::setFlashMessage('success', $msg);
    } else {
      Utils::setFlashMessage('error', implode("<br>", $errors));
    }
    header("Location: vendor-dashboard.php");
    exit;
  }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>WNC Farmers Market - Vendor Dashboard</title>
  <script src="js/farmers-market.js" defer></script>
  <link rel="icon" type="image/svg+xml" href="img/wnc-favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="img/wnc-favicon32.png">
  <link rel="stylesheet" type="text/css" href="css/farmers-market.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
  <?php include_once HEADER_FILE; ?>
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
      // Build array of markets to add
      $available_markets = [];
      foreach ($all_markets as $market) {
        if (!in_array($market['market_id'], $currentMarketIds)) {
          $available_markets[] = $market;
        }
      }
      ?>

      <form action="vendor-dashboard.php" method="POST">
        <?= Utils::csrfInputTag() ?>
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
        <?= Utils::csrfInputTag() ?>
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
        echo Utils::csrfInputTag();
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
        <?= Utils::csrfInputTag() ?>
        <label for="item_name">Item Name:</label>
        <input type="text" name="item_name" id="item_name" required>
        <button type="submit" name="add_item_btn">Add Item</button>
      </form>
    </section>

    <section id="accepted-payments">
      <h3>Accepted Payment Methods</h3>
      <form action="vendor-dashboard.php" method="POST">
        <?= Utils::csrfInputTag() ?>
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
      <?= Utils::csrfInputTag() ?>
      <input type="hidden" name="update_vendor" value="1">
      <section id="update-details">
        <h3>Update Vendor Details</h3>
        <label for="vendor_name">Vendor Name:</label>
        <input type="text" name="vendor_name" id="vendor_name" value="<?= htmlspecialchars($vendor->vendor_name); ?>" required><br>
        <label for="vendor_website">Website URL:</label>
        <input type="url" name="vendor_website" id="vendor_website" value="<?= htmlspecialchars($vendor->vendor_website); ?>"><br>
        <label for="vendor_description">Business Description (max 255 characters):</label>
        <textarea name="vendor_description" id="vendor_description" rows="4" cols="50" required><?= htmlspecialchars($vendor->vendor_description); ?></textarea>
      </section>

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
      <button type="submit" name="update_vendor" id="update-vendor">Save Changes</button>
    </form>
  </main>

  <?php include_once FOOTER_FILE; ?>
</body>

</html>
