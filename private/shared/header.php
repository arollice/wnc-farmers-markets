<?php
// Ensure config.php is included
if (!defined('PUBLIC_PATH')) {
  include_once __DIR__ . '/../private/config.php';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>WNC Farmers Market</title>

  <!-- JS libraries loaded without defer, TRY WITH DEFER-->
  <!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script> -->


  <?php if (isset($currentPage) && $currentPage === 'regions'): ?>
    <!-- Leaflet CSS from CDN -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <!-- Leaflet JS from CDN -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js" defer></script>
    <!-- Custom JS for your regions map -->
    <script src="<?= PUBLIC_PATH ?>/js/leaflet-map.js" defer></script>
  <?php endif; ?>


  <!--<link rel="stylesheet" href="<?= PUBLIC_PATH ?>/css/styles.css">-->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>


<body>
  <header>
    <!--<h1>WNC Farmers Markets Collective</h1>-->
    <a href="<?= PUBLIC_PATH ?>/index.php">
      <img src="<?= PUBLIC_PATH ?>/img/wnc-logo-color.png" alt="WNC Farmers Markets Logo" width="200" height="auto">
    </a>

    <p>Your central resource for farmers markets in Western North Carolina</p>

    <nav>
      <ul>
        <li><a href="<?= PUBLIC_PATH ?>/index.php">Home</a></li>
        <li><a href="<?= PUBLIC_PATH ?>/regions.php">Regions</a></li>
        <li><a href="<?= PUBLIC_PATH ?>/markets.php">Markets</a></li>
        <li><a href="<?= PUBLIC_PATH ?>/vendors.php">Vendors</a></li>
        <li><a href="<?= PUBLIC_PATH ?>/about.php">About</a></li>
      </ul>
    </nav>
  </header>
