'use strict';

if (document.getElementById('map')) {
document.addEventListener("DOMContentLoaded", function () {
  // Remove the "no-js" class from the map container, if present
  var mapContainer = document.getElementById('map-container');
  var map = document.getElementById('map');
  if (mapContainer) {
    mapContainer.classList.remove('no-js');
    map.classList.remove('no-js');
  }

  var map = L.map("map").setView([35.5951, -82.5515], 10); // Keep initial view on all markets

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
            .bindTooltip(region.region_name, { 
              permanent: false, 
              direction: 'top'
            });
        
          marker.on('tooltipopen', function(e) {
            // Set tooltip text color to black
            e.tooltip._container.style.color = '#333';
            var closeBtn = e.tooltip._container.querySelector('.leaflet-popup-close-button');
            if (closeBtn) {
              closeBtn.style.color = '#333';
            }
          });
        
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

  // Handle Geolocation (ask once per session)
  if ("geolocation" in navigator) {
      let geoPermission = sessionStorage.getItem("geoPermission");

      if (geoPermission === null) {  // Only ask if not set
          if (window.confirm("We'd like to show your location relative to the markets. Allow geolocation?")) {
              sessionStorage.setItem("geoPermission", "granted");
              showUserLocation();
          } else {
              sessionStorage.setItem("geoPermission", "denied");
              console.log("User opted out of geolocation.");
          }
      } else if (geoPermission === "granted") {
          showUserLocation();
      }
  } else {
      console.log("Geolocation is not available in your browser.");
  }

  function showUserLocation() {
      navigator.geolocation.getCurrentPosition(
          function (position) {
              var userLatLng = [position.coords.latitude, position.coords.longitude];
              var userMarker = L.marker(userLatLng, {
                  icon: L.icon({
                    iconUrl: 'img/icon.png', 
                    iconSize: [42, 38], 
                    iconAnchor: [21, 38], 
                    popupAnchor: [0, -38] 
                  })
              }).addTo(map);
              userMarker.bindPopup("You are here").openPopup();
              console.log("User location added to the map.");
          },
          function (error) {
              console.warn("Geolocation error: " + error.message);
          }
      );
  }
});
}

//Admin add market preview map
export function createMap(containerId, center = [35.6, -82.5], zoom = 10) {
  console.log(`Creating map in container: ${containerId}`);
  try {
    const map = L.map(containerId).setView(center, zoom);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);
    console.log("Map created successfully");
    return map;
  } catch (e) {
    console.error("Error creating map:", e);
    throw e;
  }
}

export function addDraggableMarker(map, position = [35.6, -82.5], onDragEnd) {
  console.log(`Adding marker at position: ${position}`);
  const marker = L.marker(position, { draggable: true }).addTo(map);
  marker.on('dragend', e => {
    const { lat, lng } = e.target.getLatLng();
    if (typeof onDragEnd === 'function') onDragEnd({ lat, lng });
  });
  return marker;
}
