<!doctype html>
<?php require_once __DIR__ . '/config.php'; ?>
<html lang="en" class="h-full">

  <?php include 'includes/head.php'; ?>

  <body class="h-full">
    <!-- Three.js background container -->
    <div id="app-bg"></div>
    <?php include 'includes/header.php'; ?>

    <main class="mx-auto max-w-5xl px-4 py-8">
      <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-tk-fg mb-3"><i class="iconoir-vegan"></i> Plant Your Idea in the Commons</h1>
        <p class="text-xl text-tk-muted">Every great project starts as a small seed. Submit your idea, and let the
          community help it sprout and thrive.</p>
      </div>

      <section class="tk-card p-8" aria-labelledby="idea-form-title">
        <h2 id="idea-form-title" class="sr-only">Idea submission form</h2>
        <form id="ideaForm" class="space-y-4" novalidate enctype="multipart/form-data">
          <!-- Anti-spam controls: honeypot, timing, CSRF-ish token -->
          <div class="sr-only" aria-hidden="true">
            <label for="fax_number">Fax Number (leave blank)</label>
            <input id="fax_number" name="fax_number" type="text" tabindex="-1" autocomplete="off" />
          </div>
          <input type="hidden" id="form_rendered_at" name="form_rendered_at" value="" />
          <input type="hidden" id="csrf_token" name="csrf_token"
            value="<?= htmlspecialchars(bin2hex(random_bytes(16)), ENT_QUOTES, 'UTF-8') ?>" />
          <div>
            <label for="submitter_name" class="tk-label">Your Name</label>
            <input id="submitter_name" name="submitter_name" type="text" class="tk-input" placeholder="Jane Doe"
              required maxlength="120" />
          </div>
          <div>
            <label for="submitter_email" class="tk-label">Your Email</label>
            <input id="submitter_email" name="submitter_email" type="email" class="tk-input" placeholder="you@email.com"
              required maxlength="254" inputmode="email" />
            <p class="text-xs text-tk-subtle mt-1">Use a real email. We may send a quick verification link to protect
              the commons.</p>
          </div>
          <div>
            <label for="title" class="tk-label">Title</label>
            <input id="title" name="title" type="text" class="tk-input" placeholder="A clear, specific title" required
              maxlength="120" />
            <p id="titleHelp" class="text-xs text-tk-subtle mt-1">Keep it short and searchable.</p>
          </div>

          <div>
            <label for="summary" class="tk-label">Summary</label>
            <textarea id="summary" name="summary" rows="8" class="tk-input"
              placeholder="What problem does it solve? Who benefits?" required maxlength="1000"></textarea>
          </div>
          <div>
            <label for="categoryCombo" class="tk-label">Category</label>
            <?php
            // Load categories from JSON (single source of truth)
            $catPath = __DIR__ . '/categories.json';
            $categories = [];
            if (is_readable($catPath)) {
              $decoded = json_decode(file_get_contents($catPath), true);
              if (is_array($decoded))
                $categories = $decoded;
            }
            if (!$categories) {
              $categories = ["Education", "Environment", "Health", "Open Source", "Art", "Productivity", "Apps", "Games", "Other"];
            }
            ?>
            <!-- Combobox container -->
            <div class="relative" x-data>
              <!-- Visible combobox input -->
              <input id="categoryCombo" type="text" class="tk-input" placeholder="Search or pick a category…"
                role="combobox" aria-expanded="false" aria-controls="categoryListbox" aria-autocomplete="list"
                autocomplete="off" />
              <!-- Hidden canonical field that the form submits -->
              <input type="hidden" id="category" name="category" required />

              <!-- Dropdown listbox -->
              <div id="categoryDropdown"
                class="absolute z-10 mt-1 w-full rounded-lg border border-tk-border bg-tk-card shadow-lg hidden">
                <ul id="categoryListbox" role="listbox" class="max-h-60 overflow-auto py-1">
                  <?php foreach ($categories as $i => $c):
                    $id = 'catopt_' . $i; ?>
                    <li id="<?= $id ?>" role="option" data-value="<?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>"
                      class="cursor-pointer px-3 py-2 text-sm text-tk-fg hover:bg-tk-border">
                      <?= htmlspecialchars($c, ENT_QUOTES, 'UTF-8') ?>
                    </li>
                  <?php endforeach; ?>
                </ul>
              </div>
              <p class="text-xs text-tk-subtle mt-2">Choose the category that best fits your idea.</p>
            </div>

            <script>
              // Accessible combobox for Category (vanilla JS, no deps)
              (function () {
                const combo = document.getElementById('categoryCombo');
                const hidden = document.getElementById('category'); // canonical value
                const dropdown = document.getElementById('categoryDropdown');
                const listbox = document.getElementById('categoryListbox');
                if (!combo || !hidden || !dropdown || !listbox) return;

                let activeIndex = -1; // keyboard focus index
                const options = Array.from(listbox.querySelectorAll('[role="option"]'));
                let filtered = options.slice();

                function open() {
                  dropdown.classList.remove('hidden');
                  combo.setAttribute('aria-expanded', 'true');
                }
                function close() {
                  dropdown.classList.add('hidden');
                  combo.setAttribute('aria-expanded', 'false');
                  activeIndex = -1;
                  setActiveDesc(null);
                }
                function setActiveDesc(el) {
                  if (el) {
                    combo.setAttribute('aria-activedescendant', el.id);
                  } else {
                    combo.removeAttribute('aria-activedescendant');
                  }
                }
                function commit(value) {
                  hidden.value = value;
                  combo.value = value;
                  close();
                }
                function filter(q) {
                  const ql = q.trim().toLowerCase();
                  filtered = [];
                  options.forEach((opt) => {
                    const match = !ql || opt.dataset.value.toLowerCase().includes(ql);
                    opt.classList.toggle('hidden', !match);
                    if (match) filtered.push(opt);
                  });
                  // reset active after filter
                  activeIndex = filtered.length ? 0 : -1;
                  setActiveDesc(filtered[0] || null);
                }

                // Click outside to close
                document.addEventListener('click', (e) => {
                  if (!dropdown.contains(e.target) && e.target !== combo) close();
                });

                // Open on focus/click
                combo.addEventListener('focus', () => { open(); filter(combo.value); });
                combo.addEventListener('click', () => { open(); filter(combo.value); });

                // Input filtering
                combo.addEventListener('input', () => {
                  open();
                  filter(combo.value);
                });

                // Keyboard navigation
                combo.addEventListener('keydown', (e) => {
                  if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    open();
                    if (filtered.length) {
                      activeIndex = Math.min(activeIndex + 1, filtered.length - 1);
                      setActiveDesc(filtered[activeIndex]);
                      filtered[activeIndex].scrollIntoView({ block: 'nearest' });
                    }
                  } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (filtered.length) {
                      activeIndex = Math.max(activeIndex - 1, 0);
                      setActiveDesc(filtered[activeIndex]);
                      filtered[activeIndex].scrollIntoView({ block: 'nearest' });
                    }
                  } else if (e.key === 'Enter') {
                    if (!dropdown.classList.contains('hidden') && activeIndex >= 0 && filtered[activeIndex]) {
                      e.preventDefault();
                      commit(filtered[activeIndex].dataset.value);
                    }
                  } else if (e.key === 'Escape') {
                    close();
                  }
                });

                // Mouse selection
                listbox.addEventListener('click', (e) => {
                  const li = e.target.closest('[role="option"]');
                  if (!li) return;
                  commit(li.dataset.value);
                });

                // Initialize from hidden (e.g., browser restore)
                if (hidden.value) combo.value = hidden.value;
                filter(combo.value);
              })();
            </script>
          </div>
          <script>
            (function () {
              var t = document.getElementById('form_rendered_at');
              if (t) { t.value = String(Date.now()); }
            })();
          </script>
          <div>
            <label for="tags" class="tk-label">Tags</label>
            <input id="tags" name="tags" type="text" class="tk-input" placeholder="#education #opensource"
              maxlength="120" pattern="^[#A-Za-z0-9\-\s_]*$" />
            <p class="text-xs text-tk-subtle mt-1">Add relevant hashtags separated by spaces.</p>
          </div>
          <div>
            <label for="license_type" class="tk-label">Preferred License</label>
            <select id="license_type" name="license_type" class="tk-input" required>
              <option value="">Select a license</option>
              <option value="MIT">MIT</option>
              <option value="Apache 2.0">Apache 2.0</option>
              <option value="GPLv3">GPLv3</option>
              <option value="Proprietary">Proprietary</option>
              <option value="Other">Other</option>
            </select>
          </div>
          <div>
            <label for="support_needs" class="tk-label">Support Needed</label>
            <textarea id="support_needs" name="support_needs" rows="4" class="tk-input"
              placeholder="Describe what support you need (mentorship, time, funding, etc.)"></textarea>
          </div>

          <div>
            <label for="url" class="tk-label">Related URL</label>
            <input id="url" name="url" type="url" class="tk-input" placeholder="https://example.com" />
          </div>

          <div>
            <label for="video_url" class="tk-label">Video URL (YouTube/Vimeo)</label>
            <input id="video_url" name="video_url" type="url" class="tk-input" placeholder="https://youtube.com/..." />
          </div>

          <div>
            <label for="attachment" class="tk-label">Attachment (PDF, Image, Markdown)</label>
            <input id="attachment" name="attachment" type="file" class="tk-input"
              accept=".pdf,.png,.jpg,.jpeg,.webp,.gif,.md" />
            <p class="text-xs text-tk-subtle mt-1">Max size: 10MB</p>
          </div>

          <!-- Cloudflare Turnstile (anti-spam verification) -->
          <?php $turnstileSiteKey = getenv('CF_TURNSTILE_SITE_KEY') ?: (isset($CF_TURNSTILE_SITE_KEY) ? $CF_TURNSTILE_SITE_KEY : ''); ?>
          <?php if (!empty($turnstileSiteKey)): ?>
            <div class="space-y-2">
              <label class="tk-label">Security Verification</label>
              <div class="cf-turnstile" data-sitekey="<?= htmlspecialchars($turnstileSiteKey, ENT_QUOTES, 'UTF-8') ?>"
                data-theme="dark"></div>
              <p class="text-xs text-tk-subtle">Please complete the security check to protect against spam.</p>
            </div>
          <?php endif; ?>
          <div class="flex items-center justify-between">
            <button class="tk-btn tk-btn-primary" type="submit" id="submitBtn">
              <i class="iconoir-light-bulb-on"></i>&nbsp; Submit Idea
            </button>
            <div class="tk-badge">
              <i class="iconoir-vegan text-tk-accent"></i>
              <span class="text-tk-muted text-sm">Plant & Earn</span>
            </div>
          </div>

          <div id="formStatus" class="text-sm text-tk-muted mt-4" aria-live="polite" aria-atomic="true"></div>
        </form>
      </section>
    </main>

    <script type="module" src="ui/tindlekit.js"></script>
    <script type="module" src="main.js"></script>

    <?php include 'includes/footer.php'; ?>
  </body>

</html>
