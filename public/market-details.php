<?php
require_once('../private/config.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_GET['id'])) {
  die("Market ID not provided.");
}

$market_id = intval($_GET['id']);
$market = Market::fetchMarketDetails($market_id);  // Fetch market details
$policies = Market::fetchMarketPolicies();           // Fetch selected policies

if (!$market) {
  die("Market not found.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Market Details - <?= htmlspecialchars($market['market_name']) ?></title>
  <link rel="stylesheet" href="styles.css">
</head>

<body>

  <?= Market::renderMarketCard($market, $policies) ?>

  <a href="<?= rtrim(PUBLIC_PATH, '/') ?>/regions.php">Back to Map</a>

</body>

</html>
