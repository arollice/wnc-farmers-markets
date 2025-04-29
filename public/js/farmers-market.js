
'use strict';


document.addEventListener('DOMContentLoaded', function() {
 const cards = document.querySelectorAll('.collapsible-card');
  cards.forEach(card => {
   const header = card.querySelector('.collapsible-header');
   header.addEventListener('click', function() {
     card.classList.toggle('expanded');
   });
 });
});


//Admin Market - New Region js
document.addEventListener('DOMContentLoaded', function() {
  const regionSelect   = document.getElementById('region_id');
  const newRegionGroup = document.getElementById('new-region-fields');
  if (!regionSelect || !newRegionGroup) return;

  function toggleNewRegionFields() {
    if (regionSelect.value === '__new__') {
      newRegionGroup.style.display = 'block';
    } else {
      newRegionGroup.style.display = 'none';
    }
  }

  regionSelect.addEventListener('change', toggleNewRegionFields);
  // fire on load in case the form was re-rendered with “__new__” selected
  toggleNewRegionFields();
});
