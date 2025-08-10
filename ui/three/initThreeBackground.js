/**
 * Three.js Background Effect for Tindlekit
 * Subtle grain/particles layer that works with shared hosting
 */

let threeScene = null;
let isThreeActive = false;
let lastFrameAt = 0;
let watchdogId = null;
const TK_THREE_CONFIG = {
  countDesktop: 24,
  countMobile: 12,
  sizeDesktop: 1.0,
  sizeMobile: 0.8,
  baseOpacity: 0.08,     // base global opacity
  opacityJitter: 0.04,    // +/- modulation
  yDrift: 0.004,          // vertical drift per frame
  rotX: 0.02,             // slow rotations keep it calm
  rotY: 0.04
};

export const TKThree = {
  async init(container = '#app-bg', force = false) {
    // Check feature flags and device capabilities
    const tkFlags = window.__TK_FLAGS || {};
    if (tkFlags.three === false) {
      console.log('🚫 Three.js disabled via flag');
      return false;
    }
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const devicePixelRatio = window.devicePixelRatio || 1;

    const userOverride = tkFlags.three === true;
    const shouldLoad = userOverride || (!prefersReducedMotion && devicePixelRatio >= 0.5);

    console.log('🎬 Three.js init check:', {
      shouldLoad,
      isActive: isThreeActive,
      flags: tkFlags,
      prefersReducedMotion,
      devicePixelRatio,
      'tkFlags.three': tkFlags.three,
      'tkFlags.three !== false': tkFlags.three !== false
    });

    if (!shouldLoad || (isThreeActive && !force)) {
      console.log('🚫 Three.js initialization skipped:', {
        shouldLoad,
        isThreeActive,
        force,
        reason: !shouldLoad ? 'shouldLoad is false' : 'already active (use force=true to override)'
      });
      return false;
    }

    // If forcing reinit, clean up existing instance first
    if (force && isThreeActive) {
      console.log('🔄 Force reinitializing Three.js...');
      this.destroy();
    }

    try {
      console.log('📦 Loading Three.js...');
      // Load Three.js from CDN for shared hosting compatibility
      await this.loadThree();

      const containerEl = typeof container === 'string' ?
        document.querySelector(container) : container;

      if (!containerEl) {
        console.warn('❌ Three.js container not found:', container);
        return false;
      }

      console.log('🎭 Creating Three.js scene...');
      this.createScene(containerEl);
      isThreeActive = true;
      console.log('✅ Three.js background active!');
      return true;
    } catch (error) {
      console.error('❌ Three.js background failed to initialize:', error);
      return false;
    }
  },

  async loadThree() {
    if (window.THREE) {
      console.log('📦 Three.js already loaded');
      return;
    }

    console.log('🌐 Loading Three.js from CDN...');
    const script = document.createElement('script');
    script.src = 'https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js';

    return new Promise((resolve, reject) => {
      script.onload = () => {
        console.log('✅ Three.js loaded successfully');
        resolve();
      };
      script.onerror = (error) => {
        console.error('❌ Failed to load Three.js:', error);
        reject(error);
      };
      document.head.appendChild(script);
    });
  },

  createScene(container) {
    const { THREE } = window;

    // Scene setup
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);

    // Try WebGL first, fallback to Canvas 2D
    let renderer;
    try {
      renderer = new THREE.WebGLRenderer({
        alpha: true,
        antialias: false,
        powerPreference: 'low-power',
        failIfMajorPerformanceCaveat: true
      });
      console.log('🎨 Using WebGL renderer');
    } catch (webglError) {
      console.warn('⚠️ WebGL failed, trying Canvas renderer:', webglError.message);
      try {
        renderer = new THREE.CanvasRenderer({ alpha: true });
        console.log('🎨 Using Canvas renderer');
      } catch (canvasError) {
        console.error('❌ Both WebGL and Canvas failed:', canvasError.message);
        // Fallback to CSS-only particle effect
        this.createCSSParticles(container);
        return;
      }
    }

    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setClearColor(0x000000, 0); // Transparent
    container.appendChild(renderer.domElement);

    // Subtle particle system
    const particleCount = window.innerWidth > 768 ? TK_THREE_CONFIG.countDesktop : TK_THREE_CONFIG.countMobile;
    const particles = new THREE.BufferGeometry();
    const positions = new Float32Array(particleCount * 3);
    const colors = new Float32Array(particleCount * 3);

    // Create particles with Tindlekit accent colors
    for (let i = 0; i < particleCount; i++) {
      // Position - spread across viewport
      positions[i * 3] = (Math.random() - 0.5) * 40;     // X
      positions[i * 3 + 1] = (Math.random() - 0.5) * 30; // Y
      positions[i * 3 + 2] = (Math.random() - 0.5) * 20; // Z

      // Colors (using Tindlekit accent palette)
      const colorChoice = Math.random();
      if (colorChoice < 0.4) {
        // Accent cyan (#22d3ee) - brighter
        colors[i * 3] = 0.13;     // R
        colors[i * 3 + 1] = 0.83; // G
        colors[i * 3 + 2] = 0.93; // B
      } else if (colorChoice < 0.8) {
        // Accent start (#06b6d4)
        colors[i * 3] = 0.02;     // R
        colors[i * 3 + 1] = 0.71; // G
        colors[i * 3 + 2] = 0.83; // B
      } else {
        // Add some white particles for contrast
        colors[i * 3] = 0.9;      // R
        colors[i * 3 + 1] = 0.95; // G
        colors[i * 3 + 2] = 1.0;  // B
      }
    }

    particles.setAttribute('position', new THREE.BufferAttribute(positions, 3));
    particles.setAttribute('color', new THREE.BufferAttribute(colors, 3));

    // Particle material - quieter
    const particleMaterial = new THREE.PointsMaterial({
      size: window.innerWidth > 768 ? TK_THREE_CONFIG.sizeDesktop : TK_THREE_CONFIG.sizeMobile,
      vertexColors: true,
      transparent: true,
      opacity: TK_THREE_CONFIG.baseOpacity, // subtle
      blending: THREE.NormalBlending, // calmer than additive
      sizeAttenuation: true // Size varies with distance
    });

    const particleSystem = new THREE.Points(particles, particleMaterial);
    scene.add(particleSystem);

    camera.position.z = 10;

    // Store references
    threeScene = {
      scene,
      camera,
      renderer,
      particleSystem,
      container
    };

    // Start animation loop
    this.animate();

    // Watchdog: if no frames render for 2s while active, kick the loop
    if (watchdogId) { clearInterval(watchdogId); watchdogId = null; }
    watchdogId = setInterval(() => {
      if (isThreeActive && threeScene && Date.now() - lastFrameAt > 2000) {
        console.log('⏱️ three watchdog kick');
        this.animate();
      }
    }, 2000);

    // Handle resize
    window.addEventListener('resize', this.onWindowResize.bind(this));

    // Handle visibility change (pause when tab not visible)
    document.addEventListener('visibilitychange', this.onVisibilityChange.bind(this));
  },

  animate() {
    if (!isThreeActive || !threeScene) return;

    requestAnimationFrame(() => this.animate());

    // Slow rotation for subtle movement
    const time = Date.now() * 0.0005;
    threeScene.particleSystem.rotation.y = time * TK_THREE_CONFIG.rotY;
    threeScene.particleSystem.rotation.x = time * TK_THREE_CONFIG.rotX;

    // Gentle floating motion (quieter)
    const positions = threeScene.particleSystem.geometry.attributes.position.array;
    for (let i = 0; i < positions.length; i += 3) {
      positions[i + 1] += Math.sin(time + positions[i]) * TK_THREE_CONFIG.yDrift;
    }

    // Subtle global opacity breathing
    const base = TK_THREE_CONFIG.baseOpacity;
    const jit = TK_THREE_CONFIG.opacityJitter;
    threeScene.particleSystem.material.opacity = base + Math.sin(time * 0.5) * jit;

    threeScene.particleSystem.geometry.attributes.position.needsUpdate = true;

    threeScene.renderer.render(threeScene.scene, threeScene.camera);
    lastFrameAt = Date.now();
  },

  onWindowResize() {
    if (!threeScene) return;

    threeScene.camera.aspect = window.innerWidth / window.innerHeight;
    threeScene.camera.updateProjectionMatrix();
    threeScene.renderer.setSize(window.innerWidth, window.innerHeight);
  },

  onVisibilityChange() {
    if (!threeScene) return;

    if (document.hidden) {
      // Pause animation when tab is hidden
      isThreeActive = false;
    } else {
      // Resume animation when tab is visible
      isThreeActive = true;
      this.animate();
    }
  },

  destroy() {
    if (!threeScene) {
      console.log('🧹 No Three.js scene to destroy');
      isThreeActive = false;
      return;
    }

    console.log('🧹 Destroying Three.js scene...');
    isThreeActive = false;

    if (watchdogId) { clearInterval(watchdogId); watchdogId = null; }
    lastFrameAt = 0;

    if (threeScene.type === 'css') {
      // Clean up CSS particles
      const particles = threeScene.container.querySelectorAll('div[style*="animation"]');
      particles.forEach(p => p.remove());
    } else {
      // Clean up Three.js resources
      if (threeScene.renderer) {
        threeScene.renderer.dispose();
      }
      if (threeScene.particleSystem) {
        threeScene.particleSystem.geometry.dispose();
        threeScene.particleSystem.material.dispose();
      }

      // Remove DOM element
      if (threeScene.container && threeScene.renderer && threeScene.renderer.domElement) {
        threeScene.container.removeChild(threeScene.renderer.domElement);
      }

      // Remove event listeners
      window.removeEventListener('resize', this.onWindowResize);
      document.removeEventListener('visibilitychange', this.onVisibilityChange);
    }

    threeScene = null;
    console.log('✅ Three.js cleanup complete');
  },

  // Public method to reset and reinitialize
  async reset(container = '#app-bg') {
    console.log('🔄 Resetting Three.js...');
    this.destroy();
    return await this.init(container, true);
  },

  // CSS-only fallback particle effect
  createCSSParticles(container) {
    console.log('🌟 Creating CSS particle fallback');

    // Create 12 subtle floating particles with CSS animations
    for (let i = 0; i < 12; i++) {
      const particle = document.createElement('div');
      particle.style.cssText = `
        position: absolute;
        width: ${Math.random() > 0.5 ? '4px' : '6px'};
        height: ${Math.random() > 0.5 ? '4px' : '6px'};
        background: ${i % 3 === 0 ? 'rgba(34, 211, 238, 0.02)' : i % 3 === 1 ? 'rgba(6, 182, 212, 0.015)' : 'rgba(159, 179, 200, 0.012)'};
        border-radius: ${Math.random() > 0.5 ? '8px' : '16px'};
        opacity: 0.08;
        left: ${Math.random() * 100}%;
        top: ${Math.random() * 100}%;
        animation: tkFloat${i % 3} ${6 + Math.random() * 4}s ease-in-out infinite;
        pointer-events: none;
      `;
      container.appendChild(particle);
    }

    // Add CSS animations
    const style = document.createElement('style');
    style.textContent = `
      @keyframes tkFloat0 {
        0%, 100% { transform: translate(0, 0); opacity: 0.15; }
        50% { transform: translate(2px, -4px); opacity: 0.08; }
      }
      @keyframes tkFloat1 {
        0%, 100% { transform: translate(0, 0); opacity: 0.12; }
        50% { transform: translate(-3px, -6px); opacity: 0.18; }
      }
      @keyframes tkFloat2 {
        0%, 100% { transform: translate(0, 0); opacity: 0.12; }
        33% { transform: translate(4px, -2px); opacity: 0.06; }
        66% { transform: translate(-2px, 3px); opacity: 0.14; }
      }
    `;
    document.head.appendChild(style);

    // Set up basic cleanup
    isThreeActive = true;
    threeScene = { container, type: 'css' };
  }
};

// Auto-initialize if container exists
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    const bgContainer = document.querySelector('#app-bg');
    const flags = window.__TK_FLAGS || {};
    if (bgContainer && flags.three !== false) {
      TKThree.init('#app-bg');
    }
  });
} else {
  const bgContainer = document.querySelector('#app-bg');
  const flags = window.__TK_FLAGS || {};
  if (bgContainer && flags.three !== false) {
    TKThree.init('#app-bg');
  }
}
