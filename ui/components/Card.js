/**
 * Tindlekit Card Component
 * Enhanced card component with hover effects and accessibility
 */

export class TKCard {
  constructor(element, options = {}) {
    this.element = element;
    this.options = {
      hover: true,
      focus: true,
      ...options
    };
    
    this.init();
  }

  init() {
    if (!this.element) return;

    // Apply base card styling
    this.element.classList.add('tk-card');
    
    // Setup hover effects
    if (this.options.hover) {
      this.setupHoverEffects();
    }

    // Setup focus management
    if (this.options.focus) {
      this.setupFocusStates();
    }
  }

  setupHoverEffects() {
    const { element } = this;
    
    element.addEventListener('mouseenter', () => {
      element.style.transform = 'translateY(-4px) scale(1.002)';
      element.style.boxShadow = 'var(--tk-shadow-lg)';
    });

    element.addEventListener('mouseleave', () => {
      element.style.transform = 'translateY(0px) scale(1)';
      element.style.boxShadow = 'var(--tk-shadow-sm)';
    });
  }

  setupFocusStates() {
    const { element } = this;
    const focusableChild = element.querySelector('a, button, [tabindex]');
    
    if (focusableChild) {
      focusableChild.addEventListener('focus', () => {
        element.classList.add('tk-card-focused');
      });

      focusableChild.addEventListener('blur', () => {
        element.classList.remove('tk-card-focused');
      });
    }
  }

  // Update content while preserving interactions
  updateContent(newContent) {
    const focusedEl = document.activeElement;
    const wasFocused = this.element.contains(focusedEl);
    
    this.element.innerHTML = newContent;
    this.init(); // Re-initialize after content change
    
    // Restore focus if it was in this card
    if (wasFocused) {
      const newFocusable = this.element.querySelector('a, button, [tabindex]');
      if (newFocusable) {
        newFocusable.focus();
      }
    }
  }

  destroy() {
    // Clean up event listeners would go here if we tracked them
    this.element.classList.remove('tk-card', 'tk-card-focused');
  }
}

// Auto-enhance existing cards
export function enhanceCards(selector = '.card, .group') {
  const cards = document.querySelectorAll(selector);
  cards.forEach(card => new TKCard(card));
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => enhanceCards());
} else {
  enhanceCards();
}