<?php
include_once('../private/config.php');

$currencies = Currency::fetchAllCurrencies();

$sticky = $_SESSION['sticky'] ?? [];


if (isset($_SESSION['error_message'])) {
  echo '<div class="error">' . $_SESSION['error_message'] . '</div>';
  unset($_SESSION['error_message']);
}
if (isset($_SESSION['success_message'])) {
  echo '<div class="success">' . $_SESSION['success_message'] . '</div>';
  unset($_SESSION['success_message']);
}

include_once HEADER_FILE;
?>

<main>
  <h1>Vendor Registration</h1>
  <p>Register your business to be listed in the WNC Farmers Market.</p>

  <form action="<?= PUBLIC_PATH ?>/process-vendor-registration.php" method="POST">
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
      <label for="markets">Select Markets:</label>
      <select id="markets" name="market_ids[]" multiple="multiple" style="width:300px;">
        <?php
        $all_markets = Market::fetchAllMarkets();
        $selectedMarkets = $sticky['market_ids'] ?? [];
        foreach ($all_markets as $market):
        ?>
          <option value="<?= htmlspecialchars($market['market_id']); ?>"
            <?= in_array($market['market_id'], $selectedMarkets) ? 'selected' : '' ?>>
            <?= htmlspecialchars($market['market_name']); ?>
          </option>
        <?php endforeach; ?>
      </select>

    </section>
    <p><small>Hold Control or Command key to select multiple markets</small></p>

    <label>Accepted Payments:</label>
    <div class="checkbox-group">
      <?php
      $selectedPayments = $sticky['accepted_payments'] ?? [];
      foreach ($currencies as $currency): ?>
        <label>
          <input type="checkbox" name="accepted_payments[]" value="<?= htmlspecialchars($currency['currency_id']) ?>"
            <?= in_array($currency['currency_id'], $selectedPayments) ? 'checked' : '' ?>>
          <?= htmlspecialchars($currency['currency_name']) ?>
        </label>
      <?php endforeach; ?>
    </div>

    <button type="submit">Register</button>
  </form>
</main>

<?php
unset($_SESSION['sticky']);
include_once FOOTER_FILE;
?>
