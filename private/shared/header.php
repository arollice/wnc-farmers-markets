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
  <!-- JS libraries loaded without defer -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
  <!--<link rel="stylesheet" href="<?= PUBLIC_PATH ?>/css/styles.css"> -->
  <link href="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/css/select2.min.css" rel="stylesheet">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>


<body>
  <header>
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
