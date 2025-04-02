<?php
include_once('../private/config.php');

$currentPage = 'regions';

$regions = Region::fetchAllRegions();

include_once HEADER_FILE;
?>

<!-- Hide map container if JavaScript is disabled -->
<noscript>
  <style>
    #map-container {
      display: none;
    }
  </style>
</noscript>

<!-- Map container (JavaScript-based) -->
<main>
  <div id="region-page-info">
    <h1>Find a Market by Region</h1>
    <p>Select a region below to explore related markets.</p>
  </div>

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
          <a href="market-details.php?id=<?= htmlspecialchars($region['region_id']) ?>&source=regions.php">
            <?= htmlspecialchars($region['region_name']) ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </noscript>
</main>

<?php
include_once FOOTER_FILE;
?>
