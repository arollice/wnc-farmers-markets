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

  // Check if geolocation is available
  if ("geolocation" in navigator) {
      // Show a prompt explaining why we want to use geolocation
      if (window.confirm("We'd like to show your location relative to the markets. Allow geolocation?")) {
          navigator.geolocation.getCurrentPosition(
              function (position) {
                  var userLatLng = [position.coords.latitude, position.coords.longitude];
                  var userMarker = L.marker(userLatLng, {
                      icon: L.icon({
                          iconUrl: 'path/to/your-user-icon.png', // Replace with your custom icon path
                          iconSize: [25, 41],
                          iconAnchor: [12, 41],
                          popupAnchor: [0, -41]
                      })
                  }).addTo(map);
                  userMarker.bindPopup("You are here").openPopup();
                  // Optionally recenter the map on the user's location:
                  map.setView(userLatLng, 13);
              },
              function (error) {
                  console.warn("Geolocation error: " + error.message);
                  // If geolocation fails, do nothing and the map remains at the default center.
              }
          );
      } else {
          console.log("User opted out of geolocation; map remains at the default center.");
      }
  } else {
      console.log("Geolocation is not available in your browser.");
  }
});

//Add personalize icon for "you are here"
