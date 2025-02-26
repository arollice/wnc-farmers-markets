<?php
include_once('../private/config.php');

$currentPage = 'regions';

$regions = Region::fetchAllRegions();

include_once HEADER_FILE;

error_reporting(E_ALL);
ini_set('display_errors', 1);

?>

<h1>Find a Market by Region</h1>
<p>Select a region below to explore related markets.</p>

<!-- Hide map container if JavaScript is disabled -->
<noscript>
  <style>
    #map-container {
      display: none;
    }

    /* Prevent empty space if JS is disabled */
  </style>
</noscript>

<!-- Map container (JavaScript-based) -->
<div id="map-container">
  <div id="map" style="height: 600px;"></div>
</div>

<!-- PHP-Based Fallback for Users Without JavaScript -->
<noscript>
  <h2>Regions List</h2>
  <p>JavaScript is required to view the interactive map. However, you can still browse regions below:</p>
  <ul>
    <?php foreach ($regions as $region) : ?>
      <li>
        <a href="market-details.php?id=<?= htmlspecialchars($region['region_id']) ?>">
          <?= htmlspecialchars($region['region_name']) ?>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</noscript>


<?php
// Include the footer
include_once FOOTER_FILE;
?>
