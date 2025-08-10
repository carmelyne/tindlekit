/**
 * Typography utilities for Tindlekit
 * Handles word/character splitting and micro-animations without GSAP SplitText plugin
 */

export const TKTypography = {
  /**
   * Split text into words for animation (no GSAP SplitText plugin)
   * @param {HTMLElement} element - Element containing text to split
   * @returns {HTMLElement[]} Array of word span elements
   */
  splitToWords(element) {
    if (!element) return [];
    
    const text = element.textContent;
    const words = text.split(' ');
    
    element.innerHTML = '';
    const wordElements = [];
    
    words.forEach((word, index) => {
      const span = document.createElement('span');
      span.textContent = word;
      span.style.display = 'inline-block';
      span.classList.add('tk-word');
      element.appendChild(span);
      wordElements.push(span);
      
      // Add space after word (except last)
      if (index < words.length - 1) {
        element.appendChild(document.createTextNode(' '));
      }
    });
    
    return wordElements;
  },

  /**
   * Split text into characters for animation
   * @param {HTMLElement} element - Element containing text to split
   * @returns {HTMLElement[]} Array of character span elements
   */
  splitToChars(element) {
    if (!element) return [];
    
    const text = element.textContent;
    const chars = text.split('');
    
    element.innerHTML = '';
    const charElements = [];
    
    chars.forEach(char => {
      const span = document.createElement('span');
      span.textContent = char === ' ' ? '\u00A0' : char; // Non-breaking space
      span.style.display = 'inline-block';
      span.classList.add('tk-char');
      element.appendChild(span);
      charElements.push(span);
    });
    
    return charElements;
  },

  /**
   * Animate idea title words with stagger
   * @param {HTMLElement} titleElement - Title element to animate
   */
  animateIdeaTitle(titleElement) {
    if (!titleElement || !window.gsap) return;
    
    const words = this.splitToWords(titleElement);
    if (!words.length) return;

    // Set initial state
    window.gsap.set(words, { y: 8, opacity: 0 });
    
    // Animate words in sequence
    window.gsap.to(words, {
      y: 0,
      opacity: 1,
      duration: 0.4,
      stagger: 0.08,
      ease: "power2.out"
    });
  },

  /**
   * Setup hover underline reveal effect for links
   * @param {string} selector - CSS selector for links
   */
  setupHoverUnderlines(selector = '.stretched-link') {
    const links = document.querySelectorAll(selector);
    
    links.forEach(link => {
      // Add underline element
      const underline = document.createElement('span');
      underline.className = 'tk-underline-reveal';
      underline.style.cssText = `
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 100%;
        height: 2px;
        background: var(--tk-accent);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
      `;
      
      link.style.position = 'relative';
      link.appendChild(underline);
      
      link.addEventListener('mouseenter', () => {
        underline.style.transform = 'scaleX(1)';
        if (window.gsap) {
          window.gsap.to(link, { letterSpacing: '0.2px', duration: 0.2 });
        }
      });
      
      link.addEventListener('mouseleave', () => {
        underline.style.transform = 'scaleX(0)';
        if (window.gsap) {
          window.gsap.to(link, { letterSpacing: '0px', duration: 0.2 });
        }
      });
    });
  },

  /**
   * Animate text reveal for loading states
   * @param {HTMLElement} element - Element to animate
   * @param {string} finalText - Text to reveal
   */
  revealText(element, finalText) {
    if (!element || !window.gsap) {
      element.textContent = finalText;
      return;
    }

    const chars = this.splitToChars(element);
    if (!chars.length) return;

    window.gsap.set(chars, { y: 10, opacity: 0, rotationX: 90 });
    window.gsap.to(chars, {
      y: 0,
      opacity: 1,
      rotationX: 0,
      duration: 0.3,
      stagger: 0.02,
      ease: "back.out(1.7)"
    });
  }
};

// Auto-setup on DOM ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    TKTypography.setupHoverUnderlines();
  });
} else {
  TKTypography.setupHoverUnderlines();
}