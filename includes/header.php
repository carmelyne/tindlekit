<?php
// Normalize current route from pretty URLs:
//  "/" -> "index"
//  "/submit-idea" -> "submit-idea"
//  "/idea/123" -> "idea"
//  "/community" -> "community"
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$segment = trim($path, '/');
$currentRoute = $segment === '' ? 'index' : explode('/', $segment, 2)[0];
?>
<header class="sticky top-0 z-50 border-b border-tk-border bg-tk-card/80 backdrop-blur-sm">
  <div class="mx-auto max-w-5xl px-4 py-4">
    
    <!-- Desktop Navigation -->
    <nav class="hidden lg:flex items-center justify-between">
      <div class="flex items-center space-x-8">
        <a href="/" class="text-lg font-bold text-tk-accent hover:text-tk-accent-start transition-colors">
          Tindlekit
        </a>
        
        <div class="flex items-center space-x-6">
          <a href="/" 
             class="nav-link <?= $currentRoute === 'index' ? 'nav-link-active' : '' ?>"
             <?= $currentRoute === 'index' ? 'aria-current="page"' : '' ?>>
            Leaderboard
          </a>
          <a href="/submit-idea" 
             class="nav-link <?= $currentRoute === 'submit-idea' ? 'nav-link-active' : '' ?>"
             <?= $currentRoute === 'submit-idea' ? 'aria-current="page"' : '' ?>>
            Submit an Idea
          </a>
          <a href="/community" 
             class="nav-link <?= $currentRoute === 'community' ? 'nav-link-active' : '' ?>"
             <?= $currentRoute === 'community' ? 'aria-current="page"' : '' ?>>
            Community Support
          </a>
        </div>
      </div>
    </nav>

    <!-- Mobile Navigation -->
    <nav class="lg:hidden flex items-center justify-between">
      <a href="/" class="text-lg font-bold text-tk-accent">Tindlekit</a>
      
      <button id="mobileNavToggle" 
              class="tk-btn tk-btn-secondary p-2"
              aria-expanded="false" 
              aria-controls="mobileNavDrawer"
              aria-label="Toggle navigation menu">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="3" y1="6" x2="21" y2="6"></line>
          <line x1="3" y1="12" x2="21" y2="12"></line>
          <line x1="3" y1="18" x2="21" y2="18"></line>
        </svg>
      </button>
    </nav>

    <!-- Mobile Navigation Drawer -->
    <div id="mobileNavDrawer" 
         class="fixed inset-0 z-40 hidden opacity-0 transition-all duration-300 lg:hidden"
         aria-hidden="true"
         role="dialog"
         aria-labelledby="mobileNavTitle">
      
      <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" data-close></div>
      
      <div class="relative ml-auto h-full w-80 bg-tk-card border-l border-tk-border transform translate-x-full transition-transform duration-300">
        <div class="flex items-center justify-between p-4 border-b border-tk-border">
          <h2 id="mobileNavTitle" class="text-lg font-semibold text-tk-fg">Navigation</h2>
          <button class="tk-btn tk-btn-secondary p-2" data-close aria-label="Close navigation">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"></line>
              <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
          </button>
        </div>
        
        <div class="p-4 space-y-4">
          <a href="/" 
             class="mobile-nav-link <?= $currentRoute === 'index' ? 'mobile-nav-link-active' : '' ?>"
             <?= $currentRoute === 'index' ? 'aria-current="page"' : '' ?>>
            Leaderboard
          </a>
          <a href="/submit-idea" 
             class="mobile-nav-link <?= $currentRoute === 'submit-idea' ? 'mobile-nav-link-active' : '' ?>"
             <?= $currentRoute === 'submit-idea' ? 'aria-current="page"' : '' ?>>
            Submit an Idea
          </a>
          <a href="/community" 
             class="mobile-nav-link <?= $currentRoute === 'community' ? 'mobile-nav-link-active' : '' ?>"
             <?= $currentRoute === 'community' ? 'aria-current="page"' : '' ?>>
            Community Support
          </a>
        </div>
      </div>
    </div>
  </div>
</header>

<style>
/* Navigation Styles */
.nav-link {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem 0;
  font-size: 0.875rem;
  font-weight: 500;
  color: var(--tk-muted);
  text-decoration: none;
  transition: all var(--tk-duration-fast) var(--tk-ease-out);
  position: relative;
  min-height: 44px;
}

.nav-link:hover {
  color: var(--tk-fg);
}

.nav-link::after {
  content: '';
  position: absolute;
  bottom: -1px;
  left: 0;
  right: 0;
  height: 2px;
  background: var(--tk-accent);
  transform: scaleX(0);
  transform-origin: left;
  transition: transform var(--tk-duration-normal) var(--tk-ease-out);
}

.nav-link:hover::after {
  transform: scaleX(1);
}

.nav-link-active {
  color: var(--tk-accent);
}

.nav-link-active::after {
  transform: scaleX(1);
}

.mobile-nav-link {
  display: block;
  padding: 0.75rem 1rem;
  font-size: 1rem;
  font-weight: 500;
  color: var(--tk-muted);
  text-decoration: none;
  border-radius: var(--tk-radius-sm);
  transition: all var(--tk-duration-fast) var(--tk-ease-out);
  min-height: 44px;
}

.mobile-nav-link:hover {
  color: var(--tk-fg);
  background: rgba(159, 179, 200, 0.1);
}

.mobile-nav-link-active {
  color: var(--tk-accent);
  background: rgba(34, 211, 238, 0.1);
}
</style>

<script>
// Mobile Navigation Functionality
(function() {
  const toggle = document.getElementById('mobileNavToggle');
  const drawer = document.getElementById('mobileNavDrawer');
  
  if (!toggle || !drawer) return;

  const drawerPanel = drawer.querySelector('.relative');
  let focusableElements = [];
  let firstFocusable = null;
  let lastFocusable = null;

  function updateFocusableElements() {
    focusableElements = Array.from(drawer.querySelectorAll('button, a, [tabindex]:not([tabindex="-1"])'));
    firstFocusable = focusableElements[0];
    lastFocusable = focusableElements[focusableElements.length - 1];
  }

  function open() {
    drawer.classList.remove('hidden');
    drawer.setAttribute('aria-hidden', 'false');
    toggle.setAttribute('aria-expanded', 'true');

    requestAnimationFrame(() => {
      drawer.classList.remove('opacity-0');
      drawer.classList.add('opacity-100');
      drawerPanel.classList.remove('translate-x-full');
      drawerPanel.classList.add('translate-x-0');
    });

    updateFocusableElements();
    if (firstFocusable) {
      setTimeout(() => firstFocusable.focus(), 100);
    }

    document.body.style.overflow = 'hidden';
  }

  function close() {
    drawer.classList.remove('opacity-100');
    drawer.classList.add('opacity-0');
    drawerPanel.classList.remove('translate-x-0');
    drawerPanel.classList.add('translate-x-full');

    setTimeout(() => {
      drawer.classList.add('hidden');
      drawer.setAttribute('aria-hidden', 'true');
      toggle.setAttribute('aria-expanded', 'false');
      toggle.focus();
      document.body.style.overflow = '';
    }, 300);
  }

  function handleTabKey(e) {
    if (!drawer.classList.contains('hidden')) {
      if (e.shiftKey) {
        if (document.activeElement === firstFocusable) {
          e.preventDefault();
          lastFocusable.focus();
        }
      } else {
        if (document.activeElement === lastFocusable) {
          e.preventDefault();
          firstFocusable.focus();
        }
      }
    }
  }

  toggle.addEventListener('click', open);

  drawer.addEventListener('click', (e) => {
    if (e.target.matches('[data-close]') || e.target.closest('[data-close]')) {
      close();
    } else if (e.target === drawer.querySelector('.absolute')) {
      close();
    }
  });

  document.addEventListener('keydown', (e) => {
    if (!drawer.classList.contains('hidden')) {
      if (e.key === 'Escape') {
        close();
      } else if (e.key === 'Tab') {
        handleTabKey(e);
      }
    }
  });
})();
</script>
