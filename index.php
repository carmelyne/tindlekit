<!doctype html>
<html lang="en" class="h-full">

  <?php include 'includes/head.php'; ?>

  <body class="h-full">
    <!-- Three.js background container -->
    <div id="app-bg"></div>
    <?php include 'includes/header.php'; ?>

    <main class="mx-auto max-w-5xl px-4 py-8">
      <!-- Hero Section -->
      <div class="relative -mx-4 -mt-8 mb-8 px-4 py-16">
        <div class="text-center max-w-4xl mx-auto">
          <h1 class="text-4xl font-bold mb-3 tk-hero-gradient">
            Got an AI idea that could help humanity?
          </h1>
          <p class="text-xl text-tk-muted mb-8">
            Plant it in the commons and watch it grow.
          </p>
          <div class="flex flex-wrap justify-center gap-3 mb-4">
            <a href="/submit-idea"
              class="btn btn-primary text-base px-8 py-4 shadow-md hover:shadow-lg transition-all duration-200 text-black"
              title="Submit an Idea">
              <i class="iconoir-light-bulb-on"></i>&nbsp;Submit an Idea
            </a>
            <button id="toggleCatFilter"
              class="btn btn-secondary text-base px-8 py-4 shadow-md hover:shadow-lg transition-all duration-200"
              aria-expanded="false" aria-controls="catFilterOverlay">Categories ▼</button>
          </div>
          <p class="text-sm text-tk-muted">
            Support early-stage AI projects with <span class="font-semibold text-tk-accent">💙💚🧡
              tokens</span>
          </p>
        </div>
      </div>

      <!-- Leaderboard Header -->
      <div class="mb-6 text-center">
        <h2 class="text-2xl font-semibold mb-2 text-tk-fg">Idea Commons Leaderboard</h2>
        <p class="text-sm text-tk-muted">
          Discover and back the most promising ideas
        </p>
      </div>

      <?php
      // Server-side load of categories (one source of truth)
      $catPath = __DIR__ . '/categories.json';
      $categories = [];
      if (is_readable($catPath)) {
        $tmp = json_decode(file_get_contents($catPath), true);
        if (is_array($tmp))
          $categories = $tmp;
      }
      $currentCategory = isset($_GET['category']) ? trim((string) $_GET['category']) : '';
      ?>
      <?php if ($currentCategory): ?>
        <p class="muted text-sm mb-4">Showing: <span
            class="token-badge align-middle"><?= htmlspecialchars($currentCategory, ENT_QUOTES, 'UTF-8') ?></span> · <a
            class="underline" href="/">Clear</a></p>
      <?php endif; ?>

      <?php if ($categories): ?>
        <!-- Category Drawer (top overlay) -->
        <div id="catFilterOverlay" class="fixed inset-0 z-30 hidden opacity-0 transition-all duration-300 ease-out"
          aria-hidden="true" role="dialog" aria-labelledby="categoryDrawerTitle">
          <button class="absolute inset-0 bg-black/40 backdrop-blur-sm" data-close
            aria-label="Close category filter"></button>
          <div
            class="relative mx-auto mt-16 w-[calc(100%-2rem)] max-w-5xl rounded-xl border border-zinc-200 bg-white p-6 shadow-2xl transform translate-y-4 transition-all duration-300 ease-out dark:border-zinc-800 dark:bg-zinc-900">
            <div class="mb-4 flex items-center justify-between">
              <h2 id="categoryDrawerTitle" class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Filter by
                Category</h2>
              <div class="flex items-center gap-3">
                <?php if ($currentCategory): ?>
                  <a href="/"
                    class="text-sm text-primary-600 hover:text-primary-700 dark:text-primary-400 dark:hover:text-primary-300 underline font-medium">Clear
                    filter</a>
                <?php endif; ?>
                <button
                  class="btn btn-secondary text-sm px-4 py-2 focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900"
                  data-close>Close</button>
              </div>
            </div>
            <div
              class="max-h-72 overflow-auto scrollbar-thin scrollbar-track-zinc-100 scrollbar-thumb-zinc-300 dark:scrollbar-track-zinc-800 dark:scrollbar-thumb-zinc-600">
              <div class="flex flex-wrap gap-3">
                <?php foreach ($categories as $c):
                  $active = ($currentCategory === $c); ?>
                  <a href="<?= $active ? '/' : '/category/' . rawurlencode($c) ?>"
                    class="inline-flex items-center px-4 py-2 rounded-lg border text-sm font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900 <?= $active ? 'bg-primary-50 text-primary-700 border-primary-200 shadow-sm dark:bg-primary-900/30 dark:text-primary-300 dark:border-primary-800/50' : 'border-zinc-200 text-zinc-700 hover:bg-zinc-50 hover:border-zinc-300 hover:shadow-sm dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800 dark:hover:border-zinc-600' ?>">
                    <?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>
                    <?php if ($active): ?>
                      <span class="ml-2 text-primary-500 dark:text-primary-400">✓</span>
                    <?php endif; ?>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>


      <section>
        <!-- Leaderboard Cards Grid -->
        <div id="leaderboardGrid" class="space-y-4"></div>
        <div id="lbStatus" class="muted mt-6 text-center" aria-live="polite" aria-atomic="true"></div>
      </section>

    </main>

    <script>
      (function () {
        const btn = document.getElementById('toggleCatFilter');
        const overlay = document.getElementById('catFilterOverlay');
        if (!btn || !overlay) return;

        const drawer = overlay.querySelector('[class*="relative"]');
        let focusableElements = [];
        let firstFocusable = null;
        let lastFocusable = null;

        function updateFocusableElements() {
          focusableElements = Array.from(overlay.querySelectorAll('button, a, [tabindex]:not([tabindex="-1"])'));
          firstFocusable = focusableElements[0];
          lastFocusable = focusableElements[focusableElements.length - 1];
        }

        function open() {
          overlay.classList.remove('hidden');
          overlay.setAttribute('aria-hidden', 'false');
          btn.setAttribute('aria-expanded', 'true');

          // Animate in
          requestAnimationFrame(() => {
            overlay.classList.remove('opacity-0');
            overlay.classList.add('opacity-100');
            drawer.classList.remove('translate-y-4');
            drawer.classList.add('translate-y-0');
          });

          // Focus management
          updateFocusableElements();
          if (firstFocusable) {
            setTimeout(() => firstFocusable.focus(), 100);
          }

          // Prevent background scroll
          document.body.style.overflow = 'hidden';
        }

        function close() {
          overlay.classList.remove('opacity-100');
          overlay.classList.add('opacity-0');
          drawer.classList.remove('translate-y-0');
          drawer.classList.add('translate-y-4');

          setTimeout(() => {
            overlay.classList.add('hidden');
            overlay.setAttribute('aria-hidden', 'true');
            btn.setAttribute('aria-expanded', 'false');
            btn.focus(); // Return focus to trigger button
            document.body.style.overflow = '';
          }, 300);
        }

        // Focus trap
        function handleTabKey(e) {
          if (!overlay.classList.contains('hidden')) {
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

        btn.addEventListener('click', open);

        overlay.addEventListener('click', (e) => {
          if (e.target.matches('[data-close]') || e.target.closest('[data-close]')) {
            close();
          } else if (e.target === overlay) {
            close();
          }
        });

        document.addEventListener('keydown', (e) => {
          if (!overlay.classList.contains('hidden')) {
            if (e.key === 'Escape') {
              close();
            } else if (e.key === 'Tab') {
              handleTabKey(e);
            }
          }
        });
      })();
    </script>
    <script type="module" src="/main.js"></script>

    <?php include 'includes/footer.php'; ?>
  </body>

</html>
