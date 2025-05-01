<?php
include_once('../private/config.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
  header('Location: login.php');
  exit;
}

$market_id = intval($_GET['market_id'] ?? 0);
if ($market_id < 1) {
  Utils::setFlashMessage('error', 'Invalid market.');
  header('Location: admin-manage-markets.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // CSRF CHECK 
  if (!Utils::validateCsrf($_POST['csrf_token'] ?? null)) {
    Utils::setFlashMessage('error', 'Invalid form submission.');
    header("Location: admin-edit-market.php?market_id={$market_id}");
    exit;
  }

  if (isset($_POST['delete_market'])) {
    // DELETE logic
    $m = Market::fetchMarketDetails($market_id);
    if (! $m) {
      Utils::setFlashMessage('error', 'Market not found.');
      header('Location: admin-manage-markets.php');
      exit;
    }
    $region_id = $m['region_id'];

    if (Market::deleteMarket($market_id)) {
      $remaining = Market::fetchByRegionId($region_id);
      if (empty($remaining)) {
        Region::deleteRegion($region_id);
      }
      Utils::setFlashMessage('success', 'Market (and empty region) deleted.');
    } else {
      Utils::setFlashMessage('error', 'Error deleting market.');
    }
    header('Location: admin-manage-markets.php');
    exit;
  } else {
    // UPDATE logic
    $_POST = Utils::sanitize($_POST);

    $m = Market::find_by_id($market_id);
    if (! $m) {
      Utils::setFlashMessage('error', 'Market not found.');
      header('Location: admin-manage-markets.php');
      exit;
    }

    $m->market_name  = $_POST['market_name'];
    $m->parking_info = $_POST['parking_info'];
    $m->market_open  = $_POST['market_open'];
    $m->market_close = $_POST['market_close'];

    if ($m->save()) {
      $days      = $_POST['market_days']        ?? [];
      $season_id = (int)$_POST['season_id'];
      $last_day  = $_POST['last_day_of_season'] ?? '';
      MarketSchedule::updateForMarket($market_id, $days, $season_id, $last_day);

      Utils::setFlashMessage('success', 'Market and schedule updated.');
      header("Location: admin-edit-market.php?market_id={$market_id}");
      exit;
    } else {
      Utils::setFlashMessage('error', 'There was a problem saving the market.');
    }
  }
}

$market   = Market::fetchMarketDetails($market_id);
$seasons  = Season::fetchAll();
$schedules     = MarketSchedule::findAllByMarketId($market_id);
$selectedDays  = array_column($schedules, 'market_day');
$currentSched  = $schedules[0] ?? null;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>WNC Farmers Market - Admin Edit Market</title>
  <script src="js/farmers-market.js" defer></script>
  <link rel="icon" type="image/svg+xml" href="img/wnc-favicon.svg">
  <link rel="icon" type="image/png" sizes="32x32" href="img/wnc-favicon32.png">
  <link rel="stylesheet" type="text/css" href="css/farmers-market.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
  <?php
  include_once HEADER_FILE;
  ?>

  <main>
    <header class="dashboard-header">
      <h2>Edit Market</h2>
      <p><a href="admin-manage-markets.php">&larr; Back to Markets</a></p>
    </header>
    <?php Utils::displayFlashMessages(); ?>

    <!-- Market fields -->
    <form method="post">
      <?= Utils::csrfInputTag() ?>
      <label for="market_name">
        Name<br>
        <input type="text" name="market_name" id="market_name"
          value="<?= htmlspecialchars($market['market_name']); ?>"
          required>
      </label>
      <label for="parking_info">
        Parking Info<br>
        <textarea name="parking_info" id="parking_info" rows="3"><?= htmlspecialchars($market['parking_info']); ?></textarea>
      </label>
      <label for="market_open">
        Opens At<br>
        <input type="time" name="market_open" id="market_open"
          value="<?= $market['market_open']
                    ? date('H:i', strtotime($market['market_open'])) : '' ?>">
      </label>
      <label for="market_close">
        Closes At<br>
        <input type="time" name="market_close" id="market_close"
          value="<?= $market['market_close']
                    ? date('H:i', strtotime($market['market_close'])) : '' ?>">
      </label>
      <fieldset class="market-days">
        <legend>Market Days</legend>
        <?php
        $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        foreach ($daysOfWeek as $day):
        ?>
          <label class="market-day">
            <input
              type="checkbox"
              name="market_days[]"
              value="<?= $day ?>"
              <?= in_array($day, $selectedDays) ? 'checked' : '' ?>>
            <?= $day ?>
          </label>
        <?php endforeach; ?>
      </fieldset>
      <label for="season_id">
        Season<br>
        <select name="season_id" id="season_id" required>
          <option value="">— Select season —</option>
          <?php foreach ($seasons as $sn): ?>
            <option value="<?= $sn->season_id ?>"
              <?= $currentSched && $sn->season_id == $currentSched->season_id ? 'selected' : '' ?>>
              <?= htmlspecialchars($sn->season_name) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
      <label for="last_day_of_season">
        Last Day of Season<br>
        <input type="date" name="last_day_of_season" id="last_day_of_season"
          value="<?= $currentSched
                    ? date('Y-m-d', strtotime($currentSched->last_day_of_season))
                    : '' ?>">
      </label>
      <button type="submit">Save Changes</button>
    </form>

    <!-- Delete market -->
    <form method="post">
      <?= Utils::csrfInputTag() ?>
      <input type="hidden" name="delete_market" value="1">
      <button type="submit" class="button danger">Delete Market</button>
    </form>
  </main>

  <?php include_once FOOTER_FILE; ?>
</body>

</html>
