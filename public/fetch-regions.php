<?php
include_once('../private/config.php');

header('Content-Type: application/json');

// Fetch regions along with at least one associated market
$sql = "SELECT r.region_id, r.region_name, r.latitude, r.longitude, 
               m.market_id, m.market_name 
        FROM region r
        LEFT JOIN market m ON r.region_id = m.region_id
        GROUP BY r.region_id, m.market_id";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$regions = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($regions);
