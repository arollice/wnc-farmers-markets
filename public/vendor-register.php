<?php
require_once('../private/config.php');
include_once('../private/validation.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>WNC Farmers Market - Vendor Registration</title>
  <script src="js/farmers-market.js" defer></script>
  <link rel="icon" type="image/svg+xml" href="img/wnc-favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="img/wnc-favicon32.png">
  <link rel="stylesheet" type="text/css" href="css/farmers-market.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
  <?php
  $currencies = Currency::fetchAllCurrencies();

  $errors = $_SESSION['errors']   ?? [];
  $sticky = $_SESSION['sticky']   ?? [];
  unset($_SESSION['errors'], $_SESSION['sticky']);

  include_once HEADER_FILE;
  ?>

  <main>
    <h2>Vendor Registration</h2>
    <p>Register your business to be listed in the WNC Farmers Market.</p>
    <!-- Error message if one exists -->
    <?php if (!empty($errors)): ?>
      <div class="register-error">
        <p>Please fix the following errors:</p>
        <ul>
          <?php foreach ($errors as $msg): ?>
            <li><?= htmlspecialchars($msg) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form action="<?= PUBLIC_PATH ?>/process-vendor-registration.php" method="POST">
      <?= Utils::csrfInputTag() ?>
      <label for="vendor_name">Business Name:</label>
      <input type="text" id="vendor_name" name="vendor_name" required
        value="<?= htmlspecialchars($sticky['vendor_name'] ?? '') ?>">
      <label for="vendor_username">Username:</label>
      <input type="text" id="vendor_username" name="vendor_username" required
        value="<?= htmlspecialchars($sticky['vendor_username'] ?? '') ?>">
      <label for="vendor_email">Email:</label>
      <input type="email" id="vendor_email" name="vendor_email" required
        value="<?= htmlspecialchars($sticky['vendor_email'] ?? '') ?>">
      <label for="vendor_password">Password:</label>
      <input type="password" id="vendor_password" name="vendor_password" required
        minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
        placeholder="At least 8 characters, including uppercase, lowercase & numbers">
      <label for="vendor_password_confirm">Confirm Password:</label>
      <input type="password" id="vendor_password_confirm" name="vendor_password_confirm" required minlength="8">
      <label for="vendor_website">Business Website (optional):</label>
      <input type="url" id="vendor_website" name="vendor_website"
        value="<?= htmlspecialchars($sticky['vendor_website'] ?? '') ?>">
      <label for="vendor_description">Business Description (max 255 characters):</label>
      <textarea id="vendor_description" name="vendor_description" maxlength="255" rows="4" cols="50"
        placeholder="Enter a brief description of your business..."><?= htmlspecialchars($sticky['vendor_description'] ?? '') ?></textarea>

      <section id="select-markets">
        <h3>Select Markets to Attend</h3>
        <div class="checkbox-markets">
          <fieldset class="market-selection">
            <legend>Markets</legend>
            <?php
            $all_markets     = Market::fetchAllMarkets();
            $selectedMarkets = $sticky['market_ids'] ?? [];
            foreach ($all_markets as $market):
            ?>
              <label class="market-option">
                <input
                  type="checkbox"
                  name="market_ids[]"
                  value="<?= htmlspecialchars($market['market_id']); ?>"
                  <?= in_array($market['market_id'], $selectedMarkets) ? 'checked' : '' ?>>
                <?= htmlspecialchars($market['market_name']); ?>
              </label>
            <?php endforeach; ?>
          </fieldset>
        </div>
      </section>

      <section>
        <h3>Select Payments Methods</h3>
        <fieldset class="accepted-payments">
          <legend>Accepted Payments</legend>
          <?php
          $selectedPayments = $sticky['accepted_payments'] ?? [];
          foreach ($currencies as $currency):
          ?>
            <label class="payment-option">
              <input
                type="checkbox"
                name="accepted_payments[]"
                value="<?= htmlspecialchars($currency['currency_id']) ?>"
                <?= in_array($currency['currency_id'], $selectedPayments) ? 'checked' : '' ?>>
              <?= htmlspecialchars($currency['currency_name']) ?>
            </label>
          <?php endforeach; ?>
        </fieldset>
      </section>

      <button type="submit">Register</button>
    </form>
  </main>

  <?php
  unset($_SESSION['sticky']);
  include_once FOOTER_FILE;
  ?>
</body>

</html>
