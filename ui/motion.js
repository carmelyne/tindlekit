/**
 * GSAP Motion Utilities for Tindlekit
 * Handles entrance animations, hover effects, and micro-animations
 */

// Feature flag and motion-safe check
const MOTION_ENABLED = window.__TK_FLAGS?.motion !== false && 
  !window.matchMedia('(prefers-reduced-motion: reduce)').matches;

// GSAP CDN will be loaded dynamically to avoid build dependencies on shared hosting
let gsapLoaded = false;

async function loadGSAP() {
  if (gsapLoaded || !MOTION_ENABLED) return;
  
  try {
    // Load GSAP from CDN
    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js';
    script.onload = () => {
      gsapLoaded = true;
      console.log('GSAP loaded successfully');
    };
    document.head.appendChild(script);
    
    // Wait for GSAP to load
    await new Promise((resolve) => {
      const check = () => {
        if (window.gsap) {
          resolve();
        } else {
          setTimeout(check, 50);
        }
      };
      check();
    });
  } catch (error) {
    console.warn('GSAP failed to load:', error);
    return false;
  }
  
  return true;
}

// Animation utilities
export const TKMotion = {
  async init() {
    if (!MOTION_ENABLED) return false;
    return await loadGSAP();
  },

  // Stagger in cards animation
  staggerCards(selector = '.tk-card', delay = 0.1) {
    if (!MOTION_ENABLED || !window.gsap) return;
    
    const cards = document.querySelectorAll(selector);
    if (!cards.length) return;

    window.gsap.set(cards, { y: 16, opacity: 0 });
    window.gsap.to(cards, {
      y: 0,
      opacity: 1,
      duration: 0.4,
      stagger: delay,
      ease: "power2.out"
    });
  },

  // Animate drawer slide-in
  slideInDrawer(element, duration = 0.26) {
    if (!MOTION_ENABLED || !window.gsap || !element) return;

    window.gsap.fromTo(element, 
      { y: -20, opacity: 0 },
      { y: 0, opacity: 1, duration, ease: "power2.out" }
    );
  },

  // AI Token stat emphasis animation
  popTokenStat(element) {
    if (!MOTION_ENABLED || !window.gsap || !element) return;

    window.gsap.fromTo(element,
      { scale: 0.9, opacity: 0 },
      { scale: 1, opacity: 1, duration: 0.3, ease: "back.out(1.7)" }
    );
  },

  // Hover lift effect for cards
  setupCardHover(selector = '.tk-card') {
    if (!MOTION_ENABLED || !window.gsap) return;

    const cards = document.querySelectorAll(selector);
    cards.forEach(card => {
      card.addEventListener('mouseenter', () => {
        window.gsap.to(card, { y: -4, scale: 1.002, duration: 0.2, ease: "power2.out" });
      });
      
      card.addEventListener('mouseleave', () => {
        window.gsap.to(card, { y: 0, scale: 1, duration: 0.2, ease: "power2.out" });
      });
    });
  },

  // Toast notification animation
  showToast(element, message, type = 'success') {
    if (!element) return;

    element.textContent = message;
    element.className = `tk-toast tk-toast-${type}`;
    
    if (!MOTION_ENABLED || !window.gsap) {
      element.style.display = 'block';
      setTimeout(() => { element.style.display = 'none'; }, 3000);
      return;
    }

    window.gsap.fromTo(element,
      { y: 20, opacity: 0, display: 'block' },
      { y: 0, opacity: 1, duration: 0.3, ease: "power2.out" }
    );

    // Auto dismiss
    setTimeout(() => {
      window.gsap.to(element, {
        y: -20, opacity: 0, duration: 0.3, ease: "power2.in",
        onComplete: () => { element.style.display = 'none'; }
      });
    }, 3000);
  }
};

// Initialize motion system when DOM is ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => TKMotion.init());
} else {
  TKMotion.init();
}