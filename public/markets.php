<?php
require_once('../private/config.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once HEADER_FILE;

// Retrieve all markets using the new function
$markets = Market::fetchAllMarkets();
if (!$markets) {
  die("No markets found.");
}

// Fetch policies once (assuming these apply to all markets)
$policies = Market::fetchMarketPolicies();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All Markets</title>
  <link rel="stylesheet" href="styles.css">
</head>

<body>
  <h1>All Markets</h1>

  <?php foreach ($markets as $market): ?>
    <?= Market::renderMarketCard($market, $policies) ?>
  <?php endforeach; ?>

</body>

</html>
