<?php
require_once('../private/config.php');
require_once('../private/validation.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}

$stickyDays = $_POST['market_days'] ?? [];

// Fetch lookups
$regions = Region::fetchAllWithCoords();
$states  = State::fetchAllStates();
$seasons = Season::fetchAll();

$errors = [];
$market = new Market();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $_POST = Utils::sanitize($_POST);

  if ($_POST['region_id'] === '__new__') {
    // pull new‐region inputs
    $newName = trim($_POST['new_region_name'] ?? '');
    $newLat  = trim($_POST['new_region_lat']  ?? '');
    $newLng  = trim($_POST['new_region_lng']  ?? '');

    // validate them
    if ($newName === '') {
      $errors['new_region_name'] = 'Name can’t be blank.';
    }
    if (!is_numeric($newLat)) {
      $errors['new_region_lat']  = 'Valid latitude is required.';
    }
    if (!is_numeric($newLng)) {
      $errors['new_region_lng']  = 'Valid longitude is required.';
    }

    if (!isset(
      $errors['new_region_name'],
      $errors['new_region_lat'],
      $errors['new_region_lng']
    )) {
      $region = Region::findByName($newName)
        ?: Region::createNewRegion($newName, $newLat, $newLng);
      $region_id = $region->region_id;
    }
  } else {
    $region_id = (int)$_POST['region_id'];
  }

  // Hydrate your Market object with final $region_id
  $market = new Market();
  $market->market_name  = trim($_POST['market_name'] ?? '');
  $market->region_id    = $region_id   ?? 0;
  $market->city         = trim($_POST['city']        ?? '');
  $market->state_id     = (int)($_POST['state_id']  ?? 0);
  $market->zip_code     = trim($_POST['zip_code']    ?? '');
  $market->parking_info = trim($_POST['parking_info'] ?? '');
  $market->market_open  = $_POST['market_open']      ?? '';
  $market->market_close = $_POST['market_close']     ?? '';

  // Validate market fields
  if ($market->market_name === '') {
    $errors['market_name'] = 'Name can’t be blank.';
  }
  if ($market->region_id < 1) {
    $errors['region_id']   = 'Please select a region.';
  }
  if ($market->city === '') {
    $errors['city']        = 'City can’t be blank.';
  }
  if ($market->state_id < 1) {
    $errors['state_id']    = 'Please select a state.';
  }
  if (!preg_match('/^\d{5}$/', $market->zip_code)) {
    $errors['zip_code'] = 'ZIP must be 5 digits.';
  }

  if (empty($errors)) {
    $pdo->beginTransaction();
    try {
      $market->save();
      MarketSchedule::updateForMarket(
        $market->market_id,
        $_POST['market_days']        ?? [],
        (int)($_POST['season_id']    ?? 0),
        $_POST['last_day_of_season'] ?? null
      );
      $pdo->commit();
      header('Location: admin-manage-markets.php');
      exit;
    } catch (Exception $e) {
      $pdo->rollBack();
      $errors['save'] = $e->getMessage();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>WNC Farmers Market - Admin Add New Market</title>
  <link rel="icon" type="image/svg+xml" href="img/wnc-favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="img/wnc-favicon32.png">
  <link rel="stylesheet" type="text/css" href="css/farmers-market.css">
  <script src="js/farmers-market.js" defer></script>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
  <?php include_once HEADER_FILE; ?>

  <main class="container">
    <h2>Add New Market</h2>

    <?php if ($errors): ?>
      <div class="error-summary">
        <p>Please fix the following errors:</p>
        <ul>
          <?php foreach ($errors as $msg): ?>
            <li><?= htmlspecialchars($msg) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form action="admin-new-market.php" method="post" novalidate>
      <!-- Market Name -->
      <div class="form-group">
        <label for="market_name">Market Name</label>
        <input id="market_name" name="market_name" type="text"
          value="<?= htmlspecialchars($market->market_name) ?>">
      </div>

      <!-- Region -->
      <label for="region_id">Region</label>
      <select id="region_id" name="region_id" required>
        <option value="">— Select Region —</option>
        <!-- Existing regions -->
        <?php foreach ($regions as $r): ?>
          <option
            data-lat="<?= htmlspecialchars($r['latitude']) ?>"
            data-lng="<?= htmlspecialchars($r['longitude']) ?>"
            value="<?= $r['region_id'] ?>"
            <?= $r['region_id'] == $market->region_id ? 'selected' : '' ?>>
            <?= htmlspecialchars($r['region_name']) ?>
          </option>
        <?php endforeach; ?>
        <!-- “Add new” trigger -->
        <option value="__new__"
          <?= (($_POST['region_id'] ?? '') === '__new__') ? 'selected' : '' ?>>
          + Add a new region
        </option>
      </select>

      <!-- Hidden inputs for a new region -->
      <div id="new-region-fields" style="display:none; margin-top:.5rem;">
        <label for="new_region_name">New Region Name</label>
        <input id="new_region_name" name="new_region_name" type="text"
          value="<?= htmlspecialchars($_POST['new_region_name'] ?? '') ?>">
        <label for="new_region_lat">Latitude</label>
        <input id="new_region_lat" name="new_region_lat" type="text"
          value="<?= htmlspecialchars($_POST['new_region_lat'] ?? '') ?>">
        <label for="new_region_lng">Longitude</label>
        <input id="new_region_lng" name="new_region_lng" type="text"
          value="<?= htmlspecialchars($_POST['new_region_lng'] ?? '') ?>">
      </div>

      <!-- City / State / ZIP -->
      <div class="form-group">
        <label for="city">City</label>
        <input id="city" name="city" type="text"
          value="<?= htmlspecialchars($market->city) ?>">
      </div>
      <div class="form-group">
        <label for="state_id">State</label>
        <select id="state_id" name="state_id" required>
          <option value="">— Select State —</option>
          <?php foreach ($states as $s): ?>
            <option value="<?= $s['state_id'] ?>"
              <?= $s['state_id'] == $market->state_id ? 'selected' : '' ?>>
              <?= htmlspecialchars($s['state_name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label for="zip_code">ZIP Code</label>
        <input id="zip_code" name="zip_code" type="text"
          value="<?= htmlspecialchars($market->zip_code) ?>">
      </div>

      <!-- Parking & Hours -->
      <div class="form-group">
        <label for="parking_info">Parking Info</label>
        <textarea id="parking_info" name="parking_info"><?= htmlspecialchars($market->parking_info) ?></textarea>
      </div>
      <div class="form-group">
        <label for="market_open">Opens At</label>
        <input id="market_open" name="market_open" type="time"
          value="<?= htmlspecialchars($market->market_open) ?>">
      </div>
      <div class="form-group">
        <label for="market_close">Closes At</label>
        <input id="market_close" name="market_close" type="time"
          value="<?= htmlspecialchars($market->market_close) ?>">
      </div>

      <!-- Schedule: Days checkboxes -->
      <fieldset class="form-group">
        <legend>Market Days</legend>
        <?php foreach (['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day): ?>
          <label>
            <input
              type="checkbox"
              name="market_days[]"
              value="<?= $day ?>"
              <?= in_array($day, $stickyDays) ? 'checked' : '' ?>>
            <?= $day ?>
          </label>
        <?php endforeach; ?>
      </fieldset>

      <!-- Schedule: Season & Last Day -->
      <div class="form-group">
        <label for="season_id">Season</label>
        <select id="season_id" name="season_id">
          <option value="">— Select Season —</option>
          <?php foreach ($seasons as $sn): ?>
            <option value="<?= $sn->season_id ?>"
              <?= (int)($_POST['season_id'] ?? '') === $sn->season_id ? 'selected' : '' ?>>
              <?= htmlspecialchars($sn->season_name) ?>
            </option>
          <?php endforeach; ?>

        </select>
      </div>
      <div class="form-group">
        <label for="last_day_of_season">Last Day of Season</label>
        <input id="last_day_of_season"
          name="last_day_of_season"
          type="date"
          value="<?= htmlspecialchars($_POST['last_day_of_season'] ?? '') ?>">

      </div>

      <button type="submit">Create Market</button>
      <a href="admin-manage-markets.php" class="button secondary">Cancel</a>

    </form>

  </main>

  <?php include_once FOOTER_FILE; ?>
</body>

</html>
