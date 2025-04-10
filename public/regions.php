<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title>WNC Farmers Market - Regions</title>
  <script src="js/farmers-market.js" defer></script>
  <script src="https://unpkg.com/leaflet/dist/leaflet.js" defer></script>
  <script src="js/leaflet-map.js" defer></script>
  <link rel="stylesheet" type="text/css" href="css/farmers-market.css">
  <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
  <?php
  include_once('../private/config.php');

  $currentPage = 'regions';

  $regions = Region::fetchAllRegions();

  include_once HEADER_FILE;
  ?>

  <main>
    <div id="region-page-info">
      <h2>Find a Market by Region</h2>
      <p>Select a region below to explore related markets.</p>
    </div>

    <!-- Map container (JavaScript-based) -->
    <div id="map-container" class="no-js">
      <div id="map" class="no-js"></div>
    </div>

    <!-- PHP-Based Fallback for Users Without JavaScript -->
    <noscript>
      <p><em>JavaScript is required to view the interactive map. However, you can still browse the regions list below:</em></p>
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

  <?php include_once FOOTER_FILE; ?>
</body>

</html>
