<?php
include_once('../private/config.php');
include_once('../private/validation.php');
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>WNC Farmers Market - Admin Markets</title>
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
  $pdo = DatabaseObject::get_database();

  $markets = Market::fetchAllMarkets();

  $policies = Market::fetchMarketPolicies();

  include_once HEADER_FILE;
  ?>

  <main>
    <header class="dashboard-header">
      <h2>Manage Markets</h2>
      <a href="admin-new-market.php" class="button button--primary">
        + Add New Market
      </a>
      <p><a href="admin.php">&larr; Back to Dashboard</a></p>
    </header>
    <?php Utils::displayFlashMessages(); ?>
    <ul class="market-list">
      <?php foreach ($markets as $market): ?>
        <li>
          <a href="<?php echo 'admin-edit-market.php?market_id=' . urlencode($market['market_id']); ?>">
            <?php echo htmlspecialchars($market['market_name']); ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </main>
</body>
<?php include_once(FOOTER_FILE); ?>

</html>
