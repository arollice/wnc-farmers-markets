<?php
require_once('../private/config.php');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once HEADER_FILE;

$markets = Market::fetchAllMarkets();


if (!$markets) {
  die("No markets found.");
}

// Fetch policies once
$policies = Market::fetchMarketPolicies();
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
        <?= Market::renderMarketCard($market) ?>
      <?php endforeach; ?>
    </div>
    <hr>
    <?php if ($policies): ?>
      <section class="market-policies">
        <h2>Market Policies</h2>
        <ul>
          <?php foreach ($policies as $policy): ?>
            <li><?= htmlspecialchars($policy['policy_description']) ?></li>
          <?php endforeach; ?>
        </ul>
      </section>
    <?php endif; ?>
  </main>

</body>

</html>
