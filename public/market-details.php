<?php
require_once('../private/config.php');
require_once('../private/db-functions.php');

if (!isset($_GET['id'])) {
  die("Market ID not provided.");
}

$market_id = intval($_GET['id']);
$market = fetchMarketDetails($market_id);  // Fetch market details
$policies = fetchMarketPolicies();         // Fetch only the selected policies

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

  <h1><?= htmlspecialchars($market['market_name']) ?></h1>

  <p><strong>Location:</strong> <?= htmlspecialchars($market['city']) ?>, <?= htmlspecialchars($market['state_name']) ?> <?= htmlspecialchars($market['zip_code']) ?></p>
  <p><strong>Parking Info:</strong> <?= htmlspecialchars($market['parking_info']) ?></p>

  <?php if (!empty($market['market_days'])) : ?>
    <p><strong>Market Days:</strong> <?= htmlspecialchars($market['market_days']) ?></p>
  <?php endif; ?>

  <?php if (!empty($market['market_season'])) : ?>
    <p><strong>Market Season:</strong> <?= htmlspecialchars($market['market_season']) ?></p>
  <?php endif; ?>

  <?php if (!empty($market['last_market_date'])) : ?>
    <p><strong>Last Market Date:</strong> <?= date('F j, Y', strtotime($market['last_market_date'])) ?></p>
  <?php endif; ?>

  <?php if (!empty($policies)) : ?>
    <h2>Market Policies</h2>
    <ul>
      <?php foreach ($policies as $policy) : ?>
        <li><strong><?= htmlspecialchars($policy['policy_name']) ?>:</strong> <?= nl2br(htmlspecialchars($policy['policy_description'])) ?></li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>

  <a href="<?= rtrim(PUBLIC_PATH, '/') ?>/regions.php">Back to Map</a>

</body>

</html>
