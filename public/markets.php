<?php
include_once('../private/config.php');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>WNC Farmers Market - Markets</title>
  <script src="js/farmers-market.js" defer></script>
  <link rel="stylesheet" type="text/css" href="css/farmers-market.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
  <?php
  $markets = Market::fetchAllMarkets();

  if (!$markets) {
    die("No markets found.");
  }

  // Fetch policies once
  $policies = Market::fetchMarketPolicies();

  include_once HEADER_FILE;
  ?>

  <main id="markets">
    <h1>Market Directory</h1>
    <div>
      <?php foreach ($markets as $market): ?>
        <?= Market::renderCollapsibleMarketCard($market, $policies) ?>
      <?php endforeach; ?>
    </div>
  </main>

  <?php include_once FOOTER_FILE; ?>
</body>

</html>
