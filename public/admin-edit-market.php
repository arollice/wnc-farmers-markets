<?php
include_once('../private/config.php');
//include_once('../private/validation.php');

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
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
  if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
  }

  // Get & validate market_id
  $market_id = intval($_GET['market_id'] ?? 0);
  if ($market_id < 1) {
    Utils::setFlashMessage('error', 'Invalid market.');
    header('Location: admin-manage-markets.php');
    exit;
  }

  // DELETE 
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1) Handle deletion
    if (isset($_POST['delete_market'])) {
      // fetch the market to know its region
      $m = Market::fetchMarketDetails($market_id);
      if (!$m) {
        Utils::setFlashMessage('error', 'Market not found.');
        header('Location: admin-manage-markets.php');
        exit;
      }
      $region_id = $m['region_id'];

      // delete market & schedule
      if (Market::deleteMarket($market_id)) {
        // if no other markets remain in that region, delete the region
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
    }
  }

  // UPDATE
  if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_market'])) {

    $_POST = Utils::sanitize($_POST);

    // Load existing market
    $m = Market::find_by_id($market_id);
    if (!$m) {
      Utils::setFlashMessage('error', 'Market not found.');
      header('Location: admin-manage-markets.php');
      exit;
    }

    // Overwrite only the editable fields
    $m->market_name  = $_POST['market_name'];
    $m->parking_info = $_POST['parking_info'];
    $m->market_open  = $_POST['market_open'];
    $m->market_close = $_POST['market_close'];

    if ($m->save()) {
      $days      = $_POST['market_days'] ?? [];
      $season_id = (int)$_POST['season_id'];
      $last_day  = $_POST['last_day_of_season']  ?? '';
      MarketSchedule::updateForMarket($market_id, $days, $season_id, $last_day);

      Utils::setFlashMessage('success', 'Market and schedule updated.');
      header("Location: admin-edit-market.php?market_id={$market_id}");
      exit;
    } else {
      Utils::setFlashMessage('error', 'There was a problem saving the market.');
    }
  }


  $market   = Market::fetchMarketDetails($market_id);
  $seasons  = Season::fetchAll();
  $schedules     = MarketSchedule::findAllByMarketId($market_id);
  $selectedDays  = array_column($schedules, 'market_day');
  $currentSched  = $schedules[0] ?? null;

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
      <label>
        Name<br>
        <input type="text" name="market_name"
          value="<?= htmlspecialchars($market['market_name']); ?>"
          required>
      </label>

      <label>
        Parking Info<br>
        <textarea name="parking_info" rows="3"><?= htmlspecialchars($market['parking_info']); ?></textarea>
      </label>

      <label>
        Opens At<br>
        <input type="time" name="market_open"
          value="<?= $market['market_open']
                    ? date('H:i', strtotime($market['market_open'])) : '' ?>">
      </label>

      <label>
        Closes At<br>
        <input type="time" name="market_close"
          value="<?= $market['market_close']
                    ? date('H:i', strtotime($market['market_close'])) : '' ?>">
      </label>

      <label>Market Days<br>
        <?php
        $daysOfWeek = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        foreach ($daysOfWeek as $day):
        ?>
          <label style="display:inline-block;margin-right:1rem;">
            <input
              type="checkbox"
              name="market_days[]"
              value="<?= $day ?>"
              <?= in_array($day, $selectedDays) ? 'checked' : '' ?>>
            <?= $day ?>
          </label>
        <?php endforeach; ?>
      </label>

      <label>
        Season<br>
        <select name="season_id" required>
          <option value="">— Select season —</option>
          <?php foreach ($seasons as $sn): ?>
            <option value="<?= $sn->season_id ?>"
              <?= $currentSched && $sn->season_id == $currentSched->season_id ? 'selected' : '' ?>>
              <?= htmlspecialchars($sn->season_name) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label>
        Last Day of Season<br>
        <input type="date" name="last_day_of_season"
          value="<?= $currentSched
                    ? date('Y-m-d', strtotime($currentSched->last_day_of_season))
                    : '' ?>">
      </label>

      <button type="submit">Save Changes</button>
    </form>

    <!-- Delete market -->
    <form method="post"
      onsubmit="return confirm('Delete this market?');">
      <input type="hidden" name="delete_market" value="1">
      <button type="submit" class="button danger">Delete Market</button>
    </form>

  </main>

  <?php include_once FOOTER_FILE; ?>
</body>

</html>
