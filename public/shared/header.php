<?php
// Ensure config.php is included
if (!defined('PUBLIC_PATH')) {
  include_once __DIR__ . '/../../private/config.php';
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>WNC Farmers Market</title>
  <link rel="stylesheet" href="<?= PUBLIC_PATH ?>/css/styles.css">
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
