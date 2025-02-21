'use strict';

document.addEventListener("DOMContentLoaded", function () {
  var map = L.map("map").setView([35.5951, -82.5515], 10);

  // Add OpenStreetMap tiles
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>',
  }).addTo(map);

  // Fetch regions and their associated market data
  fetch("fetch-regions.php")
      .then((response) => response.json())
      .then((data) => {
          data.forEach((region) => {
              let marker = L.marker([region.latitude, region.longitude])
                  .addTo(map)
                  .bindTooltip(region.region_name, { permanent: false, direction: 'top' });

              marker.on('click', function () {
                  if (region.market_id) {
                      console.log("Redirecting to market:", region.market_id);
                      window.location.href = `market-details.php?id=${region.market_id}`;
                  } else {
                      alert("No market found for this region.");
                  }
              });
          });
      })
      .catch((error) => console.error("Error loading regions:", error));
});
