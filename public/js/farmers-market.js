'use strict'

// Market Cards
document.addEventListener("DOMContentLoaded", function() {
  // Add the 'collapsed' class to all collapsible content elements
  document.querySelectorAll('.collapsible-content').forEach(el => {
    el.classList.add('collapsed');
  });

  const cards = document.querySelectorAll('.collapsible-card');
  
  cards.forEach(card => {
    const header = card.querySelector('.collapsible-header');
    header.addEventListener('click', function() {
      // Close any other open cards
      cards.forEach(c => {
        if (c !== card) {
          c.classList.remove('expanded');
          const otherContent = c.querySelector('.collapsible-content');
          if (otherContent) {
            otherContent.classList.remove('open');
          }
        }
      });
      
      // Toggle the current card's collapsible content
      const content = card.querySelector('.collapsible-content');
      if (content) {
        content.classList.toggle('open');
        if (content.classList.contains('open')) {
          card.classList.add('expanded');
        } else {
          card.classList.remove('expanded');
        }
      }
    });
  });
});
