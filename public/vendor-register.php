<?php
include_once('../private/config.php');

$currencies = Currency::fetchAllCurrencies();

include_once HEADER_FILE;


session_start();
if (isset($_SESSION['error_message'])) {
  echo '<div class="error">' . $_SESSION['error_message'] . '</div>';
  unset($_SESSION['error_message']);
}
if (isset($_SESSION['success_message'])) {
  echo '<div class="success">' . $_SESSION['success_message'] . '</div>';
  unset($_SESSION['success_message']);
}
?>

<h1>Vendor Registration</h1>
<p>Register your business to be listed in the WNC Farmers Market.</p>

<form action="<?= PUBLIC_PATH ?>/process-vendor-registration.php" method="POST">

  <label for="vendor_name">Business Name:</label>
  <input type="text" id="vendor_name" name="vendor_name" required>

  <!-- Login-Specific Fields -->
  <label for="vendor_username">Username:</label>
  <input type="text" id="vendor_username" name="vendor_username" required>

  <label for="vendor_email">Email:</label>
  <input type="email" id="vendor_email" name="vendor_email" required>

  <!-- Password Fields -->
  <label for="vendor_password">Password:</label>
  <input type="password" id="vendor_password" name="vendor_password" required
    minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
    placeholder="At least 8 characters, including uppercase, lowercase & numbers">

  <label for="vendor_password_confirm">Confirm Password:</label>
  <input type="password" id="vendor_password_confirm" name="vendor_password_confirm" required minlength="8">

  <!-- Vendor-Specific Fields -->
  <label for="vendor_website">Business Website (optional):</label>
  <input type="url" id="vendor_website" name="vendor_website">

  <label for="vendor_description">Business Description (max 255 characters):</label>
  <textarea id="vendor_description" name="vendor_description" maxlength="255" rows="4" cols="50" placeholder="Enter a brief description of your business..."></textarea>

  <!-- Multi-select for Initial Markets Selection -->
  <section id="select-markets">
    <h3>Select Markets to Attend</h3>
    <label for="markets">Select Markets:</label>
    <select id="markets" name="market_ids[]" multiple="multiple" style="width:300px;">
      <?php
      // Assuming $all_markets is fetched similarly as in the dashboard:
      $all_markets = Market::fetchAllMarkets();
      foreach ($all_markets as $market):
      ?>
        <option value="<?= htmlspecialchars($market['market_id']); ?>">
          <?= htmlspecialchars($market['market_name']); ?>
        </option>
      <?php endforeach; ?>
    </select>
  </section>
  <p><small>Hold control or command key to select multiple markets</small></p>

  <!-- Accepted Payments -->
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
include_once FOOTER_FILE;
?>
