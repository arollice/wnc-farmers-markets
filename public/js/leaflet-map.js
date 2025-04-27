'use strict';

document.addEventListener("DOMContentLoaded", async function () {
  // Remove no-js classes
  const mapContainer = document.getElementById('map-container');
  const mapEl        = document.getElementById('map');
  if (mapContainer) mapContainer.classList.remove('no-js');
  if (mapEl)        mapEl.classList.remove('no-js');

  // Initialize the Leaflet map
  const map = L.map("map").setView([35.5951, -82.5515], 10);
  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OSM</a>',
  }).addTo(map);

  try {
    // Fetch region data
    const res     = await fetch("fetch-regions.php");
    const regions = await res.json();
    console.log("REGIONS JSON:", regions);

    // Create markers and collect them
    const markers = regions.map(r => {
      const lat = parseFloat(r.latitude);
      const lng = parseFloat(r.longitude);
      const marker = L.marker([lat, lng])
        .addTo(map)
        .bindTooltip(r.region_name, { direction: 'top' });

      // Tooltip styling
      marker.on('tooltipopen', e => {
        e.tooltip._container.style.color = '#333';
        const btn = e.tooltip._container.querySelector('.leaflet-popup-close-button');
        if (btn) btn.style.color = '#333';
      });

      // Click handler
      marker.on('click', () => {
        if (r.market_id) {
          window.location.href = `market-details.php?id=${r.market_id}`;
        } else {
          alert("No market found for this region.");
        }
      });

      return marker;
    });

    // Auto-fit all markers in view
    if (markers.length) {
      const group = L.featureGroup(markers);
      map.fitBounds(group.getBounds().pad(0.2));
    }
  } catch (err) {
    console.error("Error loading regions:", err);
  }

  // Geolocation handling
  if ("geolocation" in navigator) {
    const geoPermission = sessionStorage.getItem("geoPermission");

    if (geoPermission === null) {
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
      position => {
        const userLatLng = [position.coords.latitude, position.coords.longitude];
        const userMarker = L.marker(userLatLng, {
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
      error => console.warn("Geolocation error: " + error.message)
    );
  }
});
