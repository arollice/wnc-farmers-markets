<?php
require_once('../private/config.php');
$markets = Market::fetchAllMarkets();

if (!$markets) {
  die("No markets found.");
}

// Fetch policies once
$policies = Market::fetchMarketPolicies();

include_once HEADER_FILE;
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <title>All Markets</title>
</head>

<body>
  <main>
    <h1>All Markets</h1>
    <div id="markets">
      <?php foreach ($markets as $market): ?>
        <?= Market::renderCollapsibleMarketCard($market, $policies) ?>
      <?php endforeach; ?>
    </div>
  </main>
</body>

</html>
