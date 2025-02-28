<?php
include_once('../private/config.php');

// Fetch available regions using the Region class method
$regions = Region::fetchAllRegions();

// Fetch available payment methods (currencies) using a dedicated Currency method
$currencies = Currency::fetchAllCurrencies();

// Include the header
include_once HEADER_FILE;
?>

<h1>Vendor Registration</h1>
<p>Register your business to be listed in the WNC Farmers Market.</p>

<form action="<?= PUBLIC_PATH ?>/process_vendor_registration.php" method="POST">

  <label for="vendor_name">Business Name:</label>
  <input type="text" id="vendor_name" name="vendor_name" required>

  <!-- Username Field -->
  <label for="vendor_username">Username:</label>
  <input type="text" id="vendor_username" name="vendor_username" required>

  <label for="vendor_website">Business Website (optional):</label>
  <input type="url" id="vendor_website" name="vendor_website">

  <!-- Business Description -->
  <label for="vendor_description">Business Description (max 255 characters):</label>
  <textarea id="vendor_description" name="vendor_description" maxlength="255" rows="4" cols="50" placeholder="Enter a brief description of your business..."></textarea>

  <!-- Password Fields -->
  <label for="vendor_password">Password:</label>
  <input type="password" id="vendor_password" name="vendor_password" required
    minlength="8" pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
    placeholder="At least 8 characters, including uppercase, lowercase & numbers">

  <label for="vendor_password_confirm">Confirm Password:</label>
  <input type="password" id="vendor_password_confirm" name="vendor_password_confirm" required minlength="8">

  <label for="region">Select Your Home Region:</label>
  <select id="region" name="region_id" required>
    <option value="">-- Select a Region --</option>
    <?php foreach ($regions as $region): ?>
      <option value="<?= htmlspecialchars($region['region_id']) ?>">
        <?= htmlspecialchars($region['region_name']) ?>
      </option>
    <?php endforeach; ?>
  </select>

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
// Include the footer
include_once FOOTER_FILE;
?>
