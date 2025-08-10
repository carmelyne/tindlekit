<!doctype html>
<?php require_once __DIR__ . '/config.php'; ?>
<html lang="en" class="h-full">

  <?php $pageType = 'submit';
  $pageTitle = 'Submit an Idea — Tindlekit';
  $metaDesc = 'Share your idea with the commons and get early support.';
  $canonicalURL = 'https://tindlekit.com/submit-idea';
  include 'includes/head.php'; ?>

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
          <div class="relative">
            <label for="license_type" class="tk-label">Preferred License</label>
            
            <!-- Custom dropdown button -->
            <button type="button" id="licenseCombo" class="tk-input text-left cursor-pointer flex items-center justify-between w-full"
              aria-haspopup="listbox" aria-expanded="false" aria-labelledby="license_type">
              <span id="licenseSelected" class="text-tk-muted">Select a license</span>
              <i class="iconoir-nav-arrow-down"></i>
            </button>
            
            <!-- Hidden input for form submission -->
            <input type="hidden" id="license_type" name="license_type" required />

            <!-- Custom dropdown menu -->
            <div id="licenseDropdown"
              class="absolute z-10 mt-1 w-full rounded-lg border border-tk-border bg-tk-card shadow-lg hidden">
              <ul id="licenseListbox" role="listbox" class="max-h-60 overflow-auto py-1">
                <!-- Permissive -->
                <li class="px-3 py-1 text-xs font-semibold text-tk-muted uppercase tracking-wide">Permissive</li>
                <li role="option" data-value="MIT" class="cursor-pointer px-3 py-2 text-sm text-tk-fg hover:bg-tk-border">MIT</li>
                <li role="option" data-value="Apache-2.0" class="cursor-pointer px-3 py-2 text-sm text-tk-fg hover:bg-tk-border">Apache 2.0</li>
                <li role="option" data-value="BSD-2-Clause" class="cursor-pointer px-3 py-2 text-sm text-tk-fg hover:bg-tk-border">BSD 2-Clause</li>
                <li role="option" data-value="BSD-3-Clause" class="cursor-pointer px-3 py-2 text-sm text-tk-fg hover:bg-tk-border">BSD 3-Clause</li>
                <li role="option" data-value="Unlicense" class="cursor-pointer px-3 py-2 text-sm text-tk-fg hover:bg-tk-border">The Unlicense</li>
                <li role="option" data-value="CC0-1.0" class="cursor-pointer px-3 py-2 text-sm text-tk-fg hover:bg-tk-border">CC0 1.0 (Public Domain)</li>
                
                <!-- Copyleft -->
                <li class="px-3 py-1 text-xs font-semibold text-tk-muted uppercase tracking-wide border-t border-tk-border mt-2 pt-3">Copyleft</li>
                <li role="option" data-value="GPL-2.0" class="cursor-pointer px-3 py-2 text-sm text-tk-fg hover:bg-tk-border">GPL v2.0</li>
                <li role="option" data-value="GPL-3.0" class="cursor-pointer px-3 py-2 text-sm text-tk-fg hover:bg-tk-border">GPL v3.0</li>
                <li role="option" data-value="AGPL-3.0" class="cursor-pointer px-3 py-2 text-sm text-tk-fg hover:bg-tk-border">AGPL v3.0</li>
                <li role="option" data-value="LGPL-2.1" class="cursor-pointer px-3 py-2 text-sm text-tk-fg hover:bg-tk-border">LGPL v2.1</li>
                <li role="option" data-value="LGPL-3.0" class="cursor-pointer px-3 py-2 text-sm text-tk-fg hover:bg-tk-border">LGPL v3.0</li>
                
                <!-- Creative Commons -->
                <li class="px-3 py-1 text-xs font-semibold text-tk-muted uppercase tracking-wide border-t border-tk-border mt-2 pt-3">Creative Commons</li>
                <li role="option" data-value="CC-BY-4.0" class="cursor-pointer px-3 py-2 text-sm text-tk-fg hover:bg-tk-border">CC BY 4.0</li>
                <li role="option" data-value="CC-BY-SA-4.0" class="cursor-pointer px-3 py-2 text-sm text-tk-fg hover:bg-tk-border">CC BY-SA 4.0</li>
                <li role="option" data-value="CC-BY-NC-4.0" class="cursor-pointer px-3 py-2 text-sm text-tk-fg hover:bg-tk-border">CC BY-NC 4.0</li>
                <li role="option" data-value="CC-BY-NC-SA-4.0" class="cursor-pointer px-3 py-2 text-sm text-tk-fg hover:bg-tk-border">CC BY-NC-SA 4.0</li>
                
                <!-- Polyform -->
                <li class="px-3 py-1 text-xs font-semibold text-tk-muted uppercase tracking-wide border-t border-tk-border mt-2 pt-3">Polyform</li>
                <li role="option" data-value="Polyform-Noncommercial-1.0.0" class="cursor-pointer px-3 py-2 text-sm text-tk-fg hover:bg-tk-border">Polyform Noncommercial 1.0.0</li>
                <li role="option" data-value="Polyform-Small-Business-1.0.0" class="cursor-pointer px-3 py-2 text-sm text-tk-fg hover:bg-tk-border">Polyform Small Business 1.0.0</li>
                
                <!-- Other -->
                <li class="px-3 py-1 text-xs font-semibold text-tk-muted uppercase tracking-wide border-t border-tk-border mt-2 pt-3">Other</li>
                <li role="option" data-value="Public-Domain" class="cursor-pointer px-3 py-2 text-sm text-tk-fg hover:bg-tk-border">Public Domain (other)</li>
                <li role="option" data-value="Proprietary" class="cursor-pointer px-3 py-2 text-sm text-tk-fg hover:bg-tk-border">Proprietary</li>
                <li role="option" data-value="Other" class="cursor-pointer px-3 py-2 text-sm text-tk-fg hover:bg-tk-border">Other / Not listed</li>
              </ul>
            </div>
            
            <script>
              // License dropdown functionality
              (function () {
                const combo = document.getElementById('licenseCombo');
                const selected = document.getElementById('licenseSelected');
                const hidden = document.getElementById('license_type');
                const dropdown = document.getElementById('licenseDropdown');
                const listbox = document.getElementById('licenseListbox');
                if (!combo || !selected || !hidden || !dropdown || !listbox) return;

                let activeIndex = -1;
                const options = Array.from(listbox.querySelectorAll('[role="option"]'));

                function open() {
                  dropdown.classList.remove('hidden');
                  combo.setAttribute('aria-expanded', 'true');
                  activeIndex = -1;
                  updateActiveOption();
                }

                function close() {
                  dropdown.classList.add('hidden');
                  combo.setAttribute('aria-expanded', 'false');
                  activeIndex = -1;
                  updateActiveOption();
                }

                function updateActiveOption() {
                  options.forEach((opt, i) => {
                    if (i === activeIndex) {
                      opt.setAttribute('aria-selected', 'true');
                      opt.classList.add('bg-tk-accent', 'text-tk-bg');
                    } else {
                      opt.removeAttribute('aria-selected');
                      opt.classList.remove('bg-tk-accent', 'text-tk-bg');
                    }
                  });
                }

                function commit(value) {
                  hidden.value = value;
                  const selectedOption = options.find(opt => opt.dataset.value === value);
                  if (selectedOption) {
                    selected.textContent = selectedOption.textContent;
                    selected.classList.remove('text-tk-muted');
                    selected.classList.add('text-tk-fg');
                  }
                  close();
                  combo.focus();
                }

                // Toggle dropdown
                combo.addEventListener('click', () => {
                  if (dropdown.classList.contains('hidden')) {
                    open();
                  } else {
                    close();
                  }
                });

                // Keyboard navigation
                combo.addEventListener('keydown', (e) => {
                  if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (dropdown.classList.contains('hidden')) {
                      open();
                    } else {
                      activeIndex = Math.min(activeIndex + 1, options.length - 1);
                      updateActiveOption();
                    }
                  } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (!dropdown.classList.contains('hidden')) {
                      activeIndex = Math.max(activeIndex - 1, 0);
                      updateActiveOption();
                    }
                  } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (!dropdown.classList.contains('hidden') && activeIndex >= 0) {
                      commit(options[activeIndex].dataset.value);
                    } else {
                      open();
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

                // Close on outside click
                document.addEventListener('click', (e) => {
                  if (!combo.contains(e.target) && !dropdown.contains(e.target)) {
                    close();
                  }
                });
              })();
            </script>
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

    <script>
      (function(){
        const form = document.getElementById('ideaForm');
        if (!form) return;
        const btn = document.getElementById('submitBtn');
        const statusEl = document.getElementById('formStatus');

        function setStatus(msg){ if (statusEl) { statusEl.textContent = msg; } }
        function disable(v){ if (btn) { btn.disabled = !!v; btn.ariaBusy = v ? 'true' : 'false'; } }

        form.addEventListener('submit', async function(e){
          e.preventDefault();
          setStatus('Submitting…');
          disable(true);
          try {
            const fd = new FormData(form);
            // Cloudflare Turnstile: ensure token is present if widget is rendered
            const ts = document.querySelector('input[name="cf-turnstile-response"]');
            if (ts && !fd.get('cf-turnstile-response')) {
              fd.append('cf-turnstile-response', ts.value || '');
            }

            const res = await fetch('api.php?action=create_idea', {
              method: 'POST',
              body: fd,
              credentials: 'same-origin'
            });

            const data = await res.json().catch(() => ({}));
            if (!res.ok || !data || data.error) {
              const msg = (data && (data.message || data.error)) ? (data.message || data.error) : ('Request failed ('+res.status+')');
              setStatus('Error: ' + msg);
              disable(false);
              return;
            }

            // Success → redirect to canonical
            if (data.idea_url) {
              setStatus('Success! Redirecting…');
              window.location.assign(data.idea_url);
              return;
            }

            // Fallback: still show success if id exists
            if (data.id) {
              setStatus('Success!');
              window.location.assign('/idea.php?id=' + encodeURIComponent(String(data.id)));
              return;
            }

            setStatus('Submitted, but no redirect URL returned.');
            disable(false);
          } catch (err) {
            setStatus('Network error. Please try again.');
            disable(false);
          }
        });
      })();
    </script>
    <script type="module" src="ui/tindlekit.js"></script>
    <script type="module" src="main.js"></script>

    <?php include 'includes/footer.php'; ?>
  </body>

</html>
