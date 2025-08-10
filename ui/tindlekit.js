/**
 * Tindlekit UI System - Main Entry Point
 * Initializes all UI components and systems for shared hosting environment
 */

import { TKMotion } from './motion.js';
import { TKTypography } from './typography.js';
import { TKThree } from './three/initThreeBackground.js';
import { enhanceCards } from './components/Card.js';
import { enhanceButtons } from './components/Button.js';
import { tkToast } from './components/Toast.js';

class TindlekitUI {
  constructor() {
    this.initialized = false;
    this.features = {
      motion: true,
      three: true,
      dev: false,
      ...window.__TK_FLAGS
    };
  }

  async init() {
    if (this.initialized) return;
    
    console.log('🚀 Initializing Tindlekit UI System...');
    
    try {
      // Apply theme class to body
      this.applyTheme();
      
      // Initialize motion system
      if (this.features.motion) {
        await TKMotion.init();
        console.log('✅ Motion system loaded');
      }

      // Three.js auto-initializes itself via its own module
      // No need to explicitly call TKThree.init() here to avoid duplicates
      if (this.features.three) {
        console.log('✅ Three.js background will auto-initialize');
      }

      // Enhance existing components
      this.enhanceComponents();

      // Setup typography effects
      TKTypography.setupHoverUnderlines();
      
      // Initialize feature-specific enhancements
      this.initializeFeatures();

      this.initialized = true;
      console.log('🎉 Tindlekit UI System ready!');

      // Dispatch ready event
      window.dispatchEvent(new CustomEvent('tindlekit:ready', {
        detail: { features: this.features }
      }));

    } catch (error) {
      console.error('❌ Tindlekit initialization failed:', error);
    }
  }

  applyTheme() {
    // Add background div for Three.js if it doesn't exist
    let appBg = document.getElementById('app-bg');
    if (!appBg) {
      appBg = document.createElement('div');
      appBg.id = 'app-bg';
      document.body.appendChild(appBg);
    }
    
    // Ensure proper styling for Three.js background
    appBg.style.cssText = `
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
      pointer-events: none;
    `;
    
    console.log('🎨 App background container ready:', appBg);

    // Apply Tindlekit theme classes
    document.body.classList.add('tk-theme', 'tk-gradient-bg');
    
    // Set CSS custom properties for runtime theme switching
    document.documentElement.style.setProperty('--tk-runtime-bg', '#0b1020');
  }

  enhanceComponents() {
    // Enhance cards with new hover effects
    enhanceCards('.group, .card, [data-tk-card]');
    
    // Enhance buttons
    enhanceButtons('.btn, button[class*="btn"], [data-tk-button]');
    
    // Setup enhanced leaderboard cards if present
    this.enhanceLeaderboardCards();
  }

  enhanceLeaderboardCards() {
    const leaderboardGrid = document.getElementById('leaderboardGrid');
    if (!leaderboardGrid) return;

    // Observe for new cards being added (for load more functionality)
    const observer = new MutationObserver((mutations) => {
      mutations.forEach((mutation) => {
        mutation.addedNodes.forEach((node) => {
          if (node.nodeType === Node.ELEMENT_NODE && node.classList.contains('group')) {
            this.enhanceSingleCard(node);
            
            // Animate in new cards
            if (this.features.motion && window.gsap) {
              window.gsap.fromTo(node, 
                { y: 20, opacity: 0 },
                { y: 0, opacity: 1, duration: 0.4, ease: "power2.out" }
              );
            }
          }
        });
      });
    });

    observer.observe(leaderboardGrid, { childList: true });
  }

  enhanceSingleCard(cardElement) {
    // Apply Tindlekit styling to individual card
    cardElement.style.cssText = `
      margin-bottom: 1rem;
      border-radius: var(--tk-radius);
      border: 1px solid var(--tk-border);
      background-color: var(--tk-card);
      box-shadow: var(--tk-shadow-sm);
      overflow: hidden;
      transition: all var(--tk-duration-normal) var(--tk-ease-out);
    `;

    // Enhanced hover effects
    cardElement.addEventListener('mouseenter', () => {
      cardElement.style.transform = 'translateY(-4px) scale(1.002)';
      cardElement.style.boxShadow = 'var(--tk-shadow-lg)';
    });

    cardElement.addEventListener('mouseleave', () => {
      cardElement.style.transform = 'translateY(0px) scale(1)';
      cardElement.style.boxShadow = 'var(--tk-shadow-sm)';
    });
  }

  initializeFeatures() {
    // Like button enhancements
    this.enhanceLikeButtons();
    
    // Form enhancements
    this.enhanceForms();
    
    // Navigation enhancements
    this.enhanceNavigation();
  }

  enhanceLikeButtons() {
    document.addEventListener('click', async (e) => {
      const likeBtn = e.target.closest('button[data-id]');
      if (!likeBtn) return;

      // Visual feedback
      const originalText = likeBtn.textContent;
      likeBtn.style.transform = 'scale(0.95)';
      
      setTimeout(() => {
        likeBtn.style.transform = 'scale(1)';
      }, 150);

      // Show loading state
      likeBtn.textContent = '⏳ Liking...';

      // The actual API call is handled by the existing main.js
      // We just provide visual enhancements here
    });
  }

  enhanceForms() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
      form.addEventListener('submit', (e) => {
        const submitBtn = form.querySelector('[type="submit"], button');
        if (submitBtn && this.features.motion && window.gsap) {
          window.gsap.to(submitBtn, { scale: 0.95, duration: 0.1, yoyo: true, repeat: 1 });
        }
      });
    });
  }

  enhanceNavigation() {
    // Enhance existing category drawer
    const drawer = document.getElementById('catFilterOverlay');
    if (drawer) {
      // Already has good accessibility, just add motion enhancements
      const drawerContent = drawer.querySelector('[class*="relative"]');
      if (drawerContent && this.features.motion) {
        // Motion is already handled by existing script, but we could enhance it
      }
    }
  }

  // Public API methods
  showToast(message, type = 'success') {
    return tkToast.show(message, type);
  }

  reflow() {
    // Re-initialize components after dynamic content changes
    this.enhanceComponents();
  }

  destroy() {
    // Cleanup method for if needed
    document.body.classList.remove('tk-theme', 'tk-gradient-bg');
    this.initialized = false;
  }
}

// Create and initialize global instance
const tindlekit = new TindlekitUI();

// Auto-initialize when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => tindlekit.init());
} else {
  tindlekit.init();
}

// Make available globally
window.Tindlekit = tindlekit;
export default tindlekit;