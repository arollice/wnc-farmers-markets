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

<main id="markets">
  <h1>Market Directory</h1>
  <div>
    <?php foreach ($markets as $market): ?>
      <?= Market::renderCollapsibleMarketCard($market, $policies) ?>
    <?php endforeach; ?>
  </div>
</main>

<?php include_once FOOTER_FILE; ?>
