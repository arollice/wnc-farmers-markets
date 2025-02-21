<?php
include_once('../private/config.php'); // or your database connection file

if (!isset($_GET['id'])) {
  die("Market ID not provided.");
}

$market_id = intval($_GET['id']);

// Fetch market details from the correct table
$sql = "SELECT m.market_name, m.city, s.state_name, m.zip_code, m.parking_info, r.region_name 
        FROM market m
        JOIN region r ON m.region_id = r.region_id
        JOIN state s ON m.state_id = s.state_id
        WHERE m.market_id = :id";

$stmt = $pdo->prepare($sql);
$stmt->execute([':id' => $market_id]);
$market = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$market) {
  die("Market not found.");
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <title>Market Details - <?= htmlspecialchars($market['market_name']) ?></title>
</head>

<body>
  <h1><?= htmlspecialchars($market['market_name']) ?></h1>
  <p><strong>Region:</strong> <?= htmlspecialchars($market['region_name']) ?></p>
  <p><strong>Location:</strong> <?= htmlspecialchars($market['city']) ?>, <?= htmlspecialchars($market['state_name']) ?> <?= htmlspecialchars($market['zip_code']) ?></p>
  <p><strong>Parking Info:</strong> <?= htmlspecialchars($market['parking_info']) ?></p>

  <a href="<?= rtrim(PUBLIC_PATH, '/') ?>/regions.php">Back to Map</a>

</body>

</html>
