'use strict';

document.addEventListener('DOMContentLoaded', function() {
  // Remove 'no-js' and add 'js-enabled' to enable JS-specific CSS rules.
  const mainElement = document.querySelector('main.no-js');
  if (mainElement) {
    mainElement.classList.remove('no-js');
    mainElement.classList.add('js-enabled');
  }
  
  // Set up accordion functionality
  const cards = document.querySelectorAll('.collapsible-card');
  
  cards.forEach(card => {
    const header = card.querySelector('.collapsible-header');
    header.addEventListener('click', function() {
      // Optionally close other cards if you want only one expanded at a time.
      cards.forEach(c => {
        if (c !== card) {
          c.classList.remove('expanded');
          const otherContent = c.querySelector('.collapsible-content');
          if (otherContent) {
            otherContent.classList.remove('open');
          }
        }
      });
      
      // Toggle this card's collapsible content
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
