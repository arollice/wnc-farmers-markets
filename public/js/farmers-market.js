'use strict';

import { createMap, addDraggableMarker } from './leaflet-map.js';

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', () => {
  console.log("DOM loaded, initializing features");
  
  const cards = document.querySelectorAll('.collapsible-card');
  cards.forEach(card => {
    const header = card.querySelector('.collapsible-header');
    if (header) {
      header.addEventListener('click', () => card.classList.toggle('expanded'));
    }
  });
  
  // Initialize region toggle if on admin page
  const sel = document.getElementById('region_id');
  const newFields = document.getElementById('new-region-fields');
  if (sel && newFields) {
    console.log("Found region selector, initializing toggle");
    const toggleNew = () => {
      newFields.style.display = (sel.value === '__new__') ? 'block' : 'none';
    };
    sel.addEventListener('change', toggleNew);
    toggleNew();
  }
  
  // Initialize admin map if on the appropriate page
  const mapContainer = document.getElementById('preview-map');
  if (mapContainer) {
    console.log("Found map container, initializing map");
    initAdminMap();
  }
});

// Admin map initialization function
function initAdminMap() {  
  const mapContainer = document.getElementById('preview-map');
  const select = document.getElementById('region_id');
  
  if (!mapContainer || !select) {
    console.error("Required elements not found for map initialization");
    return;
  }
  
  console.log("Building map with container dimensions:", 
              mapContainer.offsetWidth + "x" + mapContainer.offsetHeight);
  
  try {
    const map = createMap('preview-map');
    let marker = null;
    
    // Helper to read coords from <option>
    function getCoords() {
      const opt = select.selectedOptions[0];
      return [
        parseFloat(opt.dataset.lat) || 35.6,
        parseFloat(opt.dataset.lng) || -82.5
      ];
    }
    
    // Place or move marker
    function updateMarker() {
      const [lat, lng] = getCoords();
      console.log("Updating marker position to:", lat, lng);
      
      if (marker) {
        marker.setLatLng([lat, lng]);
      } else {
        marker = addDraggableMarker(map, [lat, lng], ({lat, lng}) => {
          console.log('Marker dragged to', lat, lng);
        });
      }
      map.setView([lat, lng], 10);
    }
    
    // Listen & initialize
    select.addEventListener('change', updateMarker);
    updateMarker();
  } catch (e) {
    console.error("Error initializing map:", e);
  }
}
