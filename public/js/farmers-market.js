
'use strict';


document.addEventListener('DOMContentLoaded', function() {
 // Select all collapsible cards
 const cards = document.querySelectorAll('.collapsible-card');
  cards.forEach(card => {
   const header = card.querySelector('.collapsible-header');
   header.addEventListener('click', function() {
     // Toggle the expanded class for this card
     card.classList.toggle('expanded');
   });
 });
});
