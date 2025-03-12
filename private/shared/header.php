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

  <?php if (isset($currentPage) && $currentPage === 'regions'): ?>
    <!-- Leaflet CSS from CDN -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <!-- Leaflet JS from CDN -->
    <script src="https://unpkg.com/leaflet/dist/leaflet.js" defer></script>
    <!-- Custom JS for your regions map -->
    <script src="<?= PUBLIC_PATH ?>/js/leaflet-map.js" defer></script>
  <?php endif; ?>

  <link rel="stylesheet" type="text/css" href="<?= PUBLIC_PATH ?>/css/farmers-market.css">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>


<body>
  <header>
    <a href="<?= PUBLIC_PATH ?>/index.php">
      <img src="<?= PUBLIC_PATH ?>/img/wnc-logo-color.png" alt="WNC Farmers Markets Logo" width="200" height="auto">
    </a>

    <p><em>Your central resource for farmers markets in Western North Carolina</em></p>

    <input type="checkbox" id="menu-toggle-checkbox" hidden>
    <!-- Label acts as the hamburger icon -->
    <label for="menu-toggle-checkbox" id="menu-toggle-label" aria-label="Toggle navigation">&#9776;
    </label>
    <!-- Navigation Menu -->
    <nav id="nav-menu">
      <ul>
        <li><a href="<?= PUBLIC_PATH ?>/index.php">Home</a></li>
        <li><a href="<?= PUBLIC_PATH ?>/regions.php">Regions</a></li>
        <li><a href="<?= PUBLIC_PATH ?>/markets.php">Markets</a></li>
        <li><a href="<?= PUBLIC_PATH ?>/vendors.php">Vendors</a></li>
        <li><a href="<?= PUBLIC_PATH ?>/about.php">About</a></li>
      </ul>
    </nav>
  </header>
