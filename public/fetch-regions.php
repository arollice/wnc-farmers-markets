<?php
include_once('../private/config.php');

header('Content-Type: application/json');

$regions = Region::fetchAllWithCoordsAndMarket();
echo json_encode($regions);
