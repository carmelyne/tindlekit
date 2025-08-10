/**
 * Tindlekit Button Component
 * Enhanced button with proper states and accessibility
 */

export class TKButton {
  constructor(element, options = {}) {
    this.element = element;
    this.options = {
      variant: 'primary', // primary, secondary, ghost
      size: 'default', // sm, default, lg
      loading: false,
      ...options
    };
    
    this.originalText = element.textContent;
    this.init();
  }

  init() {
    if (!this.element) return;

    // Apply base button styling
    this.element.classList.add('tk-btn');
    this.applyVariant();
    this.setupInteractions();
  }

  applyVariant() {
    const { variant, size } = this.options;
    
    // Remove existing variant classes
    this.element.classList.remove('tk-btn-primary', 'tk-btn-secondary', 'tk-btn-ghost');
    this.element.classList.remove('tk-btn-sm', 'tk-btn-lg');
    
    // Apply variant
    this.element.classList.add(`tk-btn-${variant}`);
    
    // Apply size
    if (size !== 'default') {
      this.element.classList.add(`tk-btn-${size}`);
    }

    // Ensure minimum touch target
    if (!this.element.style.minHeight) {
      this.element.style.minHeight = '44px';
    }
  }

  setupInteractions() {
    const { element } = this;
    
    // Hover effects
    element.addEventListener('mouseenter', () => {
      if (!element.disabled && !this.options.loading) {
        element.style.transform = 'translateY(-1px)';
      }
    });

    element.addEventListener('mouseleave', () => {
      element.style.transform = 'translateY(0px)';
    });

    // Click feedback
    element.addEventListener('mousedown', () => {
      if (!element.disabled && !this.options.loading) {
        element.style.transform = 'translateY(0px) scale(0.98)';
      }
    });

    element.addEventListener('mouseup', () => {
      element.style.transform = 'translateY(-1px) scale(1)';
    });
  }

  setLoading(loading = true) {
    this.options.loading = loading;
    
    if (loading) {
      this.element.disabled = true;
      this.element.innerHTML = `
        <svg class="tk-spinner" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M21 12a9 9 0 11-6.219-8.56"/>
        </svg>
        Loading...
      `;
      this.element.style.transform = 'translateY(0px)';
    } else {
      this.element.disabled = false;
      this.element.textContent = this.originalText;
    }
  }

  setText(newText) {
    this.originalText = newText;
    if (!this.options.loading) {
      this.element.textContent = newText;
    }
  }

  destroy() {
    this.element.classList.remove('tk-btn', 'tk-btn-primary', 'tk-btn-secondary', 'tk-btn-ghost');
    this.element.style.transform = '';
    this.element.style.minHeight = '';
  }
}

// Auto-enhance existing buttons
export function enhanceButtons(selector = '.btn, button[class*="btn"]') {
  const buttons = document.querySelectorAll(selector);
  buttons.forEach(button => {
    // Determine variant from existing classes
    let variant = 'primary';
    if (button.classList.contains('btn-secondary')) variant = 'secondary';
    if (button.classList.contains('btn-ghost')) variant = 'ghost';
    
    new TKButton(button, { variant });
  });
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => enhanceButtons());
} else {
  enhanceButtons();
}