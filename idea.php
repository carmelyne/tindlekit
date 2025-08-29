<?php
// idea.php — Idea Details page
require __DIR__ . '/config.php';

try {
  $options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ];
  // Only set MySQL init command when using a MySQL DSN and the constant exists
  if (isset($DB_DSN) && is_string($DB_DSN) && str_starts_with($DB_DSN, 'mysql:') && defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
    $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4";
  }
  $pdo = new PDO($DB_DSN, $DB_USER, $DB_PASS, $options);
} catch (Throwable $e) {
  http_response_code(500);
  echo 'DB connection failed';
  exit;
}

$slug = isset($_GET['slug']) ? trim((string)$_GET['slug']) : '';
$id   = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($slug !== '') {
  // Validate slug pattern: lowercase letters, numbers, dashes only
  if (!preg_match('~^[a-z0-9-]+$~', $slug)) {
    http_response_code(400);
    echo 'Invalid slug';
    exit;
  }
  $stmt = $pdo->prepare('SELECT id, slug, title, summary, tokens, likes, url, video_url, file_path, file_mime, file_size, created_at, submitter_name, submitter_email, submitter_user_id, license_type, support_needs, category, tags FROM ideas WHERE slug = ?');
  $stmt->execute([$slug]);
} else {
  if ($id <= 0) {
    http_response_code(400);
    echo 'Invalid id';
    exit;
  }
  $stmt = $pdo->prepare('SELECT id, slug, title, summary, tokens, likes, url, video_url, file_path, file_mime, file_size, created_at, submitter_name, submitter_email, submitter_user_id, license_type, support_needs, category, tags FROM ideas WHERE id = ?');
  $stmt->execute([$id]);
}
$idea = $stmt->fetch();
if (!$idea) {
  http_response_code(404);
  echo 'Not found';
  exit;
}
// Normalize ID from fetched row for downstream queries
$id = (int)($idea['id'] ?? 0);

// If request used ?id= and we have a canonical slug, 301 redirect to /idea/{slug}
// Skip redirect under PHP built-in server or when explicitly disabled for tests/CI
if (isset($_GET['id']) && !headers_sent()) {
  $disablePretty = (getenv('DISABLE_PRETTY_REDIRECTS') === '1') || (PHP_SAPI === 'cli-server');
  if (!$disablePretty) {
    $slugCanonical = '';
    if (!empty($idea['slug']) && preg_match('~^[a-z0-9-]+$~', (string)$idea['slug'])) {
      $slugCanonical = (string)$idea['slug'];
    }
    if ($slugCanonical !== '') {
      header('Location: /idea/' . $slugCanonical, true, 301);
      exit;
    }
  }
}

// Aggregate stats for display: total token count and distinct contributors
$totalTokens = (int) ($idea['tokens'] ?? 0);
$contributorsStmt = $pdo->prepare('SELECT COUNT(DISTINCT supporter_email) AS c FROM idea_interest WHERE idea_id = ?');
$contributorsStmt->execute([$id]);
$contributorsRow = $contributorsStmt->fetch();
$totalContributors = (int) ($contributorsRow['c'] ?? 0);

function h($s)
{
  return htmlspecialchars((string) $s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

// Helpers for category badge and tags chips
function parse_tags($tagsRaw)
{
  if (!$tagsRaw)
    return [];

  // 1) Try JSON array first
  if (is_string($tagsRaw)) {
    $decoded = json_decode($tagsRaw, true);
    if (is_array($decoded)) {
      $tags = $decoded;
    } else {
      // Fallbacks:
      //  - CSV like "ai, opensource, community"
      //  - Hashtags separated by spaces like "#ai #openSource #community"
      //  - Mixed commas/spaces
      $s = trim($tagsRaw);
      // Normalize separators: replace commas with spaces
      $s = str_replace([",", "\n", "\r", "\t"], " ", $s);
      // Split on any whitespace
      $parts = preg_split('/\s+/', $s, -1, PREG_SPLIT_NO_EMPTY);
      $tags = $parts ?: [];
    }
  } elseif (is_array($tagsRaw)) {
    $tags = $tagsRaw;
  } else {
    $tags = [];
  }

  // 2) Normalize: strip leading '#', lowercase, trim punctuation, dedupe, remove empties
  $norm = [];
  foreach ($tags as $t) {
    $t = (string) $t;
    // remove leading '#'
    if (strlen($t) && $t[0] === '#')
      $t = substr($t, 1);
    // trim whitespace
    $t = trim($t);
    // collapse internal spaces (treat multi-word as separate tokens)
    // if user pasted "#open source", split into two tokens
    if (strpos($t, ' ') !== false) {
      foreach (preg_split('/\s+/', $t, -1, PREG_SPLIT_NO_EMPTY) as $w) {
        $w = ltrim($w, '#');
        $w = strtolower(trim($w));
        if ($w !== '' && !in_array($w, $norm, true))
          $norm[] = $w;
      }
      continue;
    }
    $t = strtolower($t);
    if ($t !== '' && !in_array($t, $norm, true))
      $norm[] = $t;
  }

  return $norm;
}
function category_badge($cat)
{
  $c = $cat ? (string) $cat : 'Other';
  return '<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-50 text-primary-700 border border-primary-200 dark:bg-primary-900/20 dark:text-primary-300 dark:border-primary-800/30" aria-label="category">' . h($c) . '</span>';
}
function render_tags_chips($tagsArr)
{
  if (!$tagsArr || !is_array($tagsArr) || count($tagsArr) === 0)
    return '';
  $chips = array_map(function ($t) {
    return '<span class="inline-flex items-center px-3 py-1 mr-2 mb-2 rounded-full text-xs font-medium bg-zinc-100 text-zinc-600 border border-zinc-200 hover:bg-zinc-200 hover:border-zinc-300 transition-all duration-200 dark:bg-zinc-800 dark:text-zinc-400 dark:border-zinc-700 dark:hover:bg-zinc-700 dark:hover:border-zinc-600" aria-label="tag">' . h($t) . '</span>';
  }, $tagsArr);
  return '<div class="flex flex-wrap gap-2 -mb-2">' . implode('', $chips) . '</div>';
}

if (!function_exists('video_embed')) {
  function video_embed($url)
  {
    $url = trim((string) $url);
    if ($url === '')
      return '';
    // YouTube
    if (preg_match('~(?:youtube\.com/watch\?v=|youtu\.be/)([A-Za-z0-9_-]{6,})~i', $url, $m)) {
      $id = $m[1];
      return "https://www.youtube.com/embed/" . htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
    }
    // Vimeo
    if (preg_match('~vimeo\.com/(\d+)~', $url, $m)) {
      $id = $m[1];
      return "https://player.vimeo.com/video/" . htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
    }
    return '';
  }
}

if (!function_exists('slugify')) {
  function slugify(string $s): string {
    $s = strtolower($s);
    // replace any non letter/number with a dash
    $s = preg_replace('~[^a-z0-9]+~', '-', $s);
    // collapse multiple dashes
    $s = preg_replace('~-+~', '-', $s);
    // trim dashes from ends
    $s = trim($s, '-');
    return $s ?: 'idea';
  }
}
$embedUrl = video_embed($idea['video_url'] ?? '');

$hasFile = !empty($idea['file_path']);
$filePublic = $hasFile ? ('uploads/' . basename($idea['file_path'])) : null;
$fileMime = $idea['file_mime'] ?? '';
$category = $idea['category'] ?? 'Other';
$tagsArr = parse_tags($idea['tags'] ?? '');
// Resolve profile link: prefer /user/{username}, fallback to user.php?id={id} (no email in URL)
$profileHref = '#';
$profileDataAttr = '';
$submitterEmail = $idea['submitter_email'] ?? '';
$submitterUserId = isset($idea['submitter_user_id']) ? (int)$idea['submitter_user_id'] : 0;

try {
  // First try via known user_id
  if ($submitterUserId > 0) {
    $uStmt = $pdo->prepare('SELECT id, username FROM users WHERE id = ? LIMIT 1');
    $uStmt->execute([$submitterUserId]);
    $u = $uStmt->fetch();
  } elseif ($submitterEmail !== '') {
    // Fallback lookup by email (server-side only; do not expose in URL)
    $uStmt = $pdo->prepare('SELECT id, username FROM users WHERE email = ? LIMIT 1');
    $uStmt->execute([$submitterEmail]);
    $u = $uStmt->fetch();
  } else {
    $u = null;
  }

  if ($u && !empty($u['username'])) {
    $profileHref = '/user/' . rawurlencode($u['username']);
    $profileDataAttr = 'data-user-username="' . h($u['username']) . '"';
  } elseif ($u && !empty($u['id'])) {
    $profileHref = '/user.php?id=' . (int)$u['id'];
    $profileDataAttr = 'data-user-id="' . (int)$u['id'] . '"';
  } elseif ($submitterUserId > 0) {
    $profileHref = '/user.php?id=' . $submitterUserId;
    $profileDataAttr = 'data-user-id="' . $submitterUserId . '"';
  } else {
    // Last resort: no link
    $profileHref = '#';
    $profileDataAttr = '';
  }
} catch (Throwable $e) {
  $profileHref = ($submitterUserId > 0) ? ('/user.php?id=' . $submitterUserId) : '#';
  $profileDataAttr = ($submitterUserId > 0) ? ('data-user-id="' . $submitterUserId . '"') : '';
}
// Token → USD estimate (aspirational; configurable via env TOKEN_USD, default 5)
$TOKEN_USD = (int) (getenv('TOKEN_USD') ?: 5);
$usd_est = ((int) ($idea['tokens'] ?? 0)) * $TOKEN_USD;
?>
<!doctype html>
<html lang="en" class="h-full">

  <?php // after fetching $idea with fields: title, summary, tokens, likes, url, video_url, file_path, ... (optional) slug
  $pageType = 'idea';
  $pageTitle = $idea['title'] . ' — Tindlekit';
  $metaDesc  = $idea['description'] ?? ($idea['summary'] ?? 'Idea on Tindlekit');
  // prefer DB slug if it only contains lowercase letters, numbers, and dashes; otherwise generate from title
  $slugFromDb = isset($idea['slug']) && preg_match('~^[a-z0-9-]+$~', (string)$idea['slug']) ? (string)$idea['slug'] : '';
  $slug = $slugFromDb !== '' ? $slugFromDb : slugify($idea['title'] ?? '');
  $canonicalURL = 'https://tindlekit.com/idea/' . $slug;
  // expose back for later use if needed
  $idea['slug'] = $slug;
  include 'includes/head.php'; ?>

  <body class="h-full">
    <!-- Three.js background container -->
    <div id="app-bg"></div>
    <?php include 'includes/header.php'; ?>

    <main class="mx-auto max-w-6xl px-4 py-8">

      <!-- 1. HERO BAR: Title | Category | Tokens | Likes | Share/Follow Actions -->
      <div class="tk-card p-6 mb-6">
        <div class="flex flex-wrap items-start justify-between gap-4 mb-4">
          <div class="flex-1 min-w-0">
            <h1 class="text-3xl font-bold text-tk-fg mb-2 leading-tight">
              <?= h($idea['title']) ?>
            </h1>
            <div class="flex items-center gap-3 text-sm text-tk-muted">
              <span>by <a href="<?= h($profileHref) ?>" <?= $profileDataAttr ?>
                  class="text-tk-accent hover:text-tk-fg transition-colors font-medium"
                  title="View all ideas by <?= h($idea['submitter_name']) ?>">
                  <?= h($idea['submitter_name']) ?>
                </a></span>
              <span>•</span>
              <span><?= date('M j, Y', strtotime($idea['created_at'])) ?></span>
              <span>•</span>
              <span class="tk-badge tk-badge-muted"><?= h($idea['license_type']) ?></span>
            </div>
          </div>

          <div class="flex items-center gap-4">
            <div class="text-center">
              <div class="flex items-center justify-center gap-1 text-tk-accent font-semibold">
                <i class="iconoir-coins"></i>
                <span id="tokenTotalDisplay"><?= number_format($totalTokens) ?></span>
              </div>
              <div class="text-xs text-tk-muted">AI Tokens</div>
            </div>

            <div class="text-center">
              <div class="flex items-center justify-center gap-1 text-tk-muted font-semibold">
                <i class="iconoir-heart"></i>
                <span><?= number_format((int) ($idea['likes'] ?? 0)) ?></span>
              </div>
              <div class="text-xs text-tk-muted">Likes</div>
            </div>

            <div class="flex items-center gap-2">
              <span class="tk-badge tk-badge-success"><?= h($category) ?></span>
              <button class="tk-btn tk-btn-secondary" title="Share idea">
                <i class="iconoir-share-android"></i>
              </button>
            </div>
          </div>
        </div>

        <!-- Tags Row -->
        <div class="flex flex-wrap gap-2">
          <?php foreach ($tagsArr as $tag): ?>
            <span class="tk-badge tk-badge-muted"><?= h($tag) ?></span>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- 2. FEATURED MEDIA: [Image/Video Embed] -->
      <?php if ($embedUrl || ($hasFile && strpos($fileMime, 'image/') === 0)): ?>
        <div class="tk-card p-0 mb-6 overflow-hidden">
          <?php if ($embedUrl): ?>
            <div class="aspect-video">
              <iframe class="w-full h-full" src="<?= h($embedUrl) ?>" title="Video" frameborder="0"
                allowfullscreen></iframe>
            </div>
          <?php elseif ($hasFile && strpos($fileMime, 'image/') === 0): ?>
            <img src="<?= h($filePublic) ?>" alt="<?= h($idea['title']) ?>" class="w-full h-auto" />
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <!-- 3. SUMMARY & DESCRIPTION (short, 3-4 lines) with Read More -->
      <div class="tk-card p-6 mb-6">
        <div class="mb-4">
          <div id="summary-short" class="text-lg leading-relaxed text-tk-fg">
            <?php
            $summary = h($idea['summary']);
            $words = explode(' ', strip_tags($summary));
            $shortSummary = implode(' ', array_slice($words, 0, 50));
            $needsReadMore = count($words) > 50;
            echo nl2br($shortSummary);
            if ($needsReadMore)
              echo '...';
            ?>
          </div>

          <?php if ($needsReadMore): ?>
            <div id="summary-full" class="text-lg leading-relaxed text-tk-fg hidden">
              <?= nl2br($summary) ?>
            </div>
            <button id="read-more-btn"
              class="text-tk-accent hover:text-tk-accent-start font-medium mt-2 flex items-center gap-1">
              <span>Read More</span> <i class="iconoir-nav-arrow-down"></i>
            </button>
          <?php endif; ?>
        </div>

        <!-- Support Needed - Always Visible -->
        <?php if (!empty($idea['support_needs'])): ?>
          <div class="border-t border-tk-border pt-4">
            <div class="flex items-center gap-2 mb-3">
              <i class="iconoir-help-circle text-tk-accent"></i>
              <h3 class="font-semibold text-tk-fg">Support Needed</h3>
            </div>
            <div class="text-tk-muted leading-relaxed">
              <?= nl2br(h($idea['support_needs'])) ?>
            </div>
          </div>
        <?php endif; ?>

        <!-- 4. ATTACHMENTS: grouped, collapsible -->
        <?php if ($hasFile || !empty($idea['url'])): ?>
          <div class="border-t border-tk-border pt-4">
            <div class="flex flex-wrap gap-3">
              <?php if ($hasFile && $fileMime === 'application/pdf'): ?>
                <a href="<?= h($filePublic) ?>" target="_blank" class="flex items-center gap-2 tk-btn tk-btn-secondary">
                  <i class="iconoir-page"></i> PDF Document
                </a>
              <?php endif; ?>

              <?php if ($hasFile && strpos($fileMime, 'image/') !== 0 && $fileMime !== 'application/pdf'): ?>
                <a href="<?= h($filePublic) ?>" target="_blank" class="flex items-center gap-2 tk-btn tk-btn-secondary">
                  <i class="iconoir-attachment"></i> Attachment
                </a>
              <?php endif; ?>

              <?php if (!empty($idea['url'])): ?>
                <a href="<?= h($idea['url']) ?>" target="_blank" rel="noopener"
                  class="flex items-center gap-2 tk-btn tk-btn-secondary">
                  <i class="iconoir-link"></i> External Link
                </a>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
      <!-- 5. SPLIT CTA/SOCIAL PROOF: action + trust in one viewport -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">

        <!-- LEFT: CALL TO ACTION -->
        <div class="tk-card p-6">
          <div class="flex items-center gap-2 mb-4">
            <i class="iconoir-community text-tk-accent"></i>
            <h2 id="contribute-heading" class="text-xl font-semibold text-tk-fg">Want to contribute?</h2>
          </div>

          <div class="space-y-4 mb-6">
            <div class="flex flex-wrap gap-2">
              <button onclick="selectPledgeType('time')" class="pledge-type-btn tk-btn tk-btn-secondary"
                data-type="time">
                <i class="iconoir-clock"></i> Time
              </button>
              <button onclick="selectPledgeType('mentorship')" class="pledge-type-btn tk-btn tk-btn-secondary"
                data-type="mentorship">
                <i class="iconoir-learning"></i> Mentorship
              </button>
              <button onclick="selectPledgeType('token')" class="pledge-type-btn tk-btn tk-btn-secondary"
                data-type="token">
                <i class="iconoir-coins"></i> AI Tokens
              </button>
            </div>

            <div class="text-sm text-tk-muted">
              Join
              <?= $totalContributors > 0 ? number_format($totalContributors) . ' other contributors' : 'the community' ?>
              building this idea together.
            </div>
          </div>

          <form id="interestForm" class="space-y-4" aria-labelledby="contribute-heading">
            <div>
              <label for="supporter_name" class="tk-label">Your Name</label>
              <input id="supporter_name" name="supporter_name" type="text" class="tk-input" placeholder="Your name"
                required />
            </div>
            <div>
              <label for="supporter_email" class="tk-label">Your Email</label>
              <input id="supporter_email" name="supporter_email" type="email" class="tk-input" placeholder="Your email"
                required />
            </div>

            <fieldset>
              <legend class="tk-label">Contribution Type</legend>
              <input type="hidden" name="pledge_type" id="selected_pledge_type" required />
              <div class="space-y-4 mb-6">
                <div class="flex flex-wrap gap-2" role="radiogroup" aria-labelledby="contribute-heading">
                  <button type="button" onclick="selectPledgeType('time')" class="pledge-type-btn tk-btn tk-btn-secondary"
                    data-type="time" role="radio" aria-checked="false" tabindex="0">
                    <i class="iconoir-clock"></i> Time
                  </button>
                  <button type="button" onclick="selectPledgeType('mentorship')" class="pledge-type-btn tk-btn tk-btn-secondary"
                    data-type="mentorship" role="radio" aria-checked="false" tabindex="-1">
                    <i class="iconoir-learning"></i> Mentorship
                  </button>
                  <button type="button" onclick="selectPledgeType('token')" class="pledge-type-btn tk-btn tk-btn-secondary"
                    data-type="token" role="radio" aria-checked="false" tabindex="-1">
                    <i class="iconoir-coins"></i> AI Tokens
                  </button>
                </div>
              </div>
            </fieldset>

            <div id="tokenAmountRow" style="display:none;">
              <label for="tokens_amount" class="tk-label">Number of AI Tokens</label>
              <input id="tokens_amount" name="tokens" type="number" min="1" class="tk-input"
                placeholder="Number of AI Tokens to pledge" aria-describedby="token-help" />
              <p id="token-help" class="text-xs text-tk-subtle mt-1">1 token ≈ $<?= $TOKEN_USD ?> (aspirational value)</p>
            </div>

            <div>
              <label for="pledge_details" class="tk-label">Contribution Details</label>
              <textarea id="pledge_details" name="pledge_details" rows="3" class="tk-input"
                placeholder="Describe your offer (e.g. hours, skills, how you can help)"></textarea>
            </div>

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
              <button type="submit" class="tk-btn tk-btn-primary" id="interestSubmitBtn">
                <i class="iconoir-send"></i> Send Pledge
              </button>
              <div class="tk-badge tk-badge-muted">
                <i class="iconoir-shield-check"></i>
                <span class="text-xs">Safe & Private</span>
              </div>
            </div>
            <div id="interestStatus" class="text-sm text-tk-muted" aria-live="polite" aria-atomic="true" role="status"></div>
          </form>
        </div>

        <!-- RIGHT: SOCIAL PROOF -->
        <div class="tk-card p-6">
          <div class="flex items-center gap-2 mb-4">
            <i class="iconoir-community text-tk-success"></i>
            <h2 class="text-xl font-semibold text-tk-fg">Community Contributors</h2>
          </div>

          <?php if ($totalContributors > 0): ?>
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div class="text-center p-4 bg-tk-bg/50 rounded-lg">
                <div class="text-2xl font-bold text-tk-accent" id="tokenTotalDisplay2"><?= number_format($totalTokens) ?>
                </div>
                <div class="text-xs text-tk-muted">tokens pledged</div>
              </div>
              <div class="text-center p-4 bg-tk-bg/50 rounded-lg">
                <div class="text-2xl font-bold text-tk-success"><?= number_format($totalContributors) ?></div>
                <div class="text-xs text-tk-muted">supporters</div>
              </div>
            </div>

            <div class="space-y-3">
              <h3 class="font-semibold text-tk-fg">Recent Contributors:</h3>
              <div id="pledgesContainer" class="space-y-2 max-h-48 overflow-y-auto">
                <div id="pledgesStatus" class="text-tk-muted text-sm">Loading contributors...</div>
              </div>
            </div>
          <?php else: ?>
            <div class="text-center py-8">
              <i class="iconoir-community text-4xl text-tk-muted mb-3"></i>
              <p class="text-tk-muted">Be the first to support this idea!</p>
              <p class="text-sm text-tk-subtle mt-1">Your contribution helps bring innovative projects to life.</p>
            </div>
          <?php endif; ?>
        </div>

      </div>

      <script>window.__bypassTurnstile = <?= (getenv('BYPASS_TURNSTILE') === '1') ? 'true' : 'false' ?>;</script>
      <script type="module" src="ui/tindlekit.js"></script>

      <script type="module">
        // Read More functionality
        const readMoreBtn = document.getElementById('read-more-btn');
        const summaryShort = document.getElementById('summary-short');
        const summaryFull = document.getElementById('summary-full');

        if (readMoreBtn) {
          readMoreBtn.addEventListener('click', () => {
            summaryShort.classList.toggle('hidden');
            summaryFull.classList.toggle('hidden');
            const isExpanded = summaryFull.classList.contains('hidden');
            readMoreBtn.innerHTML = isExpanded
              ? '<span>Read More</span> <i class="iconoir-nav-arrow-down"></i>'
              : '<span>Read Less</span> <i class="iconoir-nav-arrow-up"></i>';
          });
        }

        // Pledge type selection
        window.selectPledgeType = function (type) {
          document.getElementById('selected_pledge_type').value = type;

          // Update button states and ARIA attributes
          document.querySelectorAll('.pledge-type-btn').forEach((btn, index) => {
            const isSelected = btn.dataset.type === type;
            
            // Visual state
            btn.classList.remove('tk-btn-primary');
            btn.classList.add('tk-btn-secondary');
            if (isSelected) {
              btn.classList.remove('tk-btn-secondary');
              btn.classList.add('tk-btn-primary');
            }
            
            // ARIA and focus management
            btn.setAttribute('aria-checked', isSelected ? 'true' : 'false');
            btn.setAttribute('tabindex', isSelected ? '0' : '-1');
            
            if (isSelected) {
              btn.focus();
            }
          });

          // Show/hide token amount with proper label association
          const tokenRow = document.getElementById('tokenAmountRow');
          const tokenInput = document.getElementById('tokens_amount');
          if (type === 'token') {
            tokenRow.style.display = '';
            if (tokenInput) {
              tokenInput.setAttribute('required', 'true');
            }
          } else {
            tokenRow.style.display = 'none';
            if (tokenInput) {
              tokenInput.removeAttribute('required');
              tokenInput.value = '';
            }
          }
        };

        // Fetch and display pledges
        async function fetchPledges() {
          const pledgesStatus = document.getElementById('pledgesStatus');
          const pledgesContainer = document.getElementById('pledgesContainer');
          if (!pledgesStatus || !pledgesContainer) return;

          pledgesStatus.textContent = 'Loading contributors...';
          try {
            const res = await fetch('api.php?action=get_interests&idea_id=<?= (int) $idea['id'] ?>');
            const data = await res.json();

            if (!Array.isArray(data) || data.length === 0) {
              pledgesStatus.textContent = 'No contributors yet.';
              return;
            }

            pledgesContainer.innerHTML = '';

            // Create contributor cards
            for (const pledge of data) {
              const contributorCard = document.createElement('div');
              contributorCard.className = 'flex items-center gap-3 p-3 bg-tk-bg/30 rounded-lg';

              const typeIcon = {
                'time': 'iconoir-clock',
                'mentorship': 'iconoir-learning',
                'token': 'iconoir-coins'
              }[pledge.pledge_type] || 'iconoir-user';

              const typeColor = {
                'time': 'text-blue-400',
                'mentorship': 'text-purple-400',
                'token': 'text-tk-accent'
              }[pledge.pledge_type] || 'text-tk-muted';

              contributorCard.innerHTML = `
                <div class="w-10 h-10 rounded-full bg-tk-border flex items-center justify-center">
                  <i class="${typeIcon} ${typeColor}"></i>
                </div>
                <div class="flex-1 min-w-0">
                  <div class="font-medium text-tk-fg truncate">${escapeHtml(pledge.supporter_name)}</div>
                  <div class="text-sm text-tk-muted capitalize">${escapeHtml(pledge.pledge_type)} contribution</div>
                  ${pledge.pledge_details ? `<div class="text-xs text-tk-subtle truncate">${escapeHtml(pledge.pledge_details)}</div>` : ''}
                </div>
              `;

              pledgesContainer.appendChild(contributorCard);
            }

            pledgesStatus.textContent = '';
          } catch (e) {
            pledgesStatus.textContent = 'Failed to load contributors.';
          }
        }
        function escapeHtml(s) {
          return String(s).replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
          }[c]));
        }

        // Keyboard navigation for pledge type radio group
        (function() {
          const radioGroup = document.querySelector('[role="radiogroup"]');
          if (radioGroup) {
            radioGroup.addEventListener('keydown', function(e) {
              const radios = Array.from(document.querySelectorAll('.pledge-type-btn'));
              const currentIndex = radios.findIndex(radio => radio.getAttribute('tabindex') === '0');
              
              if (e.key === 'ArrowRight' || e.key === 'ArrowDown') {
                e.preventDefault();
                const newIndex = (currentIndex + 1) % radios.length;
                selectPledgeType(radios[newIndex].dataset.type);
              } else if (e.key === 'ArrowLeft' || e.key === 'ArrowUp') {
                e.preventDefault();
                const newIndex = (currentIndex - 1 + radios.length) % radios.length;
                selectPledgeType(radios[newIndex].dataset.type);
              }
            });
          }
        })();

        fetchPledges();
        // Toggle token amount input when pledge_type changes
        (function () {
          const form = document.getElementById('interestForm');
          const row = document.getElementById('tokenAmountRow');
          if (!form || !row) return;
          form.addEventListener('change', (e) => {
            if (e.target && e.target.name === 'pledge_type') {
              row.style.display = (e.target.value === 'token') ? '' : 'none';
            }
          });
        })();
        // Handle pledge submission
        document.getElementById('interestForm').addEventListener('submit', async (e) => {
          e.preventDefault();
          const status = document.getElementById('interestStatus');
          const btn = document.getElementById('interestSubmitBtn');
          const form = e.currentTarget;
          // Ensure Cloudflare Turnstile token is present (skip when bypass flag is set)
          const turnstileInput = form.querySelector('input[name="cf-turnstile-response"]');
          const turnstileToken = (turnstileInput && turnstileInput.value) ? turnstileInput.value.trim() : '';
          const bypassTurnstile = (window.__bypassTurnstile === true);
          const supporter_name = form.supporter_name.value.trim();
          const supporter_email = form.supporter_email.value.trim();
          const pledge_type = form.pledge_type.value;
          const pledge_details = form.pledge_details.value.trim();
          const tokensAmountEl = document.getElementById('tokens_amount');
          const isToken = pledge_type === 'token';
          const tokensAmount = isToken ? parseInt(tokensAmountEl?.value || '0', 10) : 0;
          if (!supporter_name || !supporter_email || !pledge_type) {
            status.textContent = 'Please fill all required fields.';
            return;
          }
          if (isToken && (!Number.isFinite(tokensAmount) || tokensAmount < 1)) {
            status.textContent = 'Please enter a valid token amount (≥ 1).';
            return;
          }
          if (!turnstileToken && !bypassTurnstile) {
            status.textContent = 'Please complete the security verification.';
            return;
          }
          btn.disabled = true;
          status.textContent = 'Submitting…';
          try {
            const fd = new FormData();
            fd.append('idea_id', <?= (int) $idea['id'] ?>);
            fd.append('supporter_name', supporter_name);
            fd.append('supporter_email', supporter_email);
            fd.append('pledge_type', pledge_type);
            fd.append('pledge_details', pledge_details);
            if (isToken && tokensAmount > 0) {
              fd.append('tokens', String(tokensAmount));
            }
            fd.append('cf-turnstile-response', turnstileToken || (bypassTurnstile ? 'bypass' : ''));
            const res = await fetch('api.php?action=express_interest', { method: 'POST', body: fd });
            if (!res.ok) throw new Error('HTTP ' + res.status);
            status.textContent = '✅ Thank you for your pledge!';
            if (isToken && tokensAmount > 0) {
              const totals1 = document.getElementById('tokenTotalDisplay');
              const totals2 = document.getElementById('tokenTotalDisplay2');
              const cur = parseInt((totals1?.textContent || '0').replace(/[,\\s]/g, ''), 10) || 0;
              const next = cur + tokensAmount;
              if (totals1) totals1.textContent = next.toLocaleString();
              if (totals2) totals2.textContent = next.toLocaleString();
            }
            form.reset();
            // Reset Turnstile widget after successful submission
            try { if (window.turnstile && typeof window.turnstile.reset === 'function') { window.turnstile.reset(); } } catch (_) { }
            // Reset pledge type selection
            document.querySelectorAll('.pledge-type-btn').forEach(btn => {
              btn.classList.remove('tk-btn-primary');
              btn.classList.add('tk-btn-secondary');
            });
            document.getElementById('selected_pledge_type').value = '';
            document.getElementById('tokenAmountRow').style.display = 'none';

            fetchPledges();
          } catch (err) {
            status.textContent = '❌ Could not submit pledge.';
          } finally {
            btn.disabled = false;
          }
        });
      </script>
      <script type="module">
        // Dispatch a custom event when a user profile link is clicked (for future signals/analytics)
        (function () {
          const profileLink = document.querySelector('[data-user-username], [data-user-id]');
          if (!profileLink) return;
          profileLink.addEventListener('click', () => {
            const uname = profileLink.getAttribute('data-user-username') || '';
            const uid = profileLink.getAttribute('data-user-id') || '';
            window.dispatchEvent(new CustomEvent('user:profile-click', {
              detail: { username: uname, userId: uid ? parseInt(uid, 10) : null, ideaId: <?= (int) $idea['id'] ?> }
            }));
          });
        })();

        // Share button functionality
        document.querySelector('button[title="Share idea"]')?.addEventListener('click', async function () {
          const shareData = {
            title: <?= json_encode(h($idea['title'])) ?>,
            text: <?= json_encode('Check out this idea on Tindlekit: ' . h($idea['title'])) ?>,
            url: window.location.href
          };

          try {
            // Try Web Share API first (mobile/supported browsers)
            if (navigator.share && navigator.canShare && navigator.canShare(shareData)) {
              await navigator.share(shareData);
              return;
            }
          } catch (err) {
            console.log('Web Share API failed:', err);
          }

          // Fallback: Copy URL to clipboard
          try {
            await navigator.clipboard.writeText(window.location.href);

            // Show success feedback
            const button = this;
            const originalContent = button.innerHTML;
            button.innerHTML = '<i class="iconoir-check"></i>';
            button.style.background = 'var(--tk-success)';
            button.style.color = 'var(--tk-bg)';

            setTimeout(() => {
              button.innerHTML = originalContent;
              button.style.background = '';
              button.style.color = '';
            }, 2000);

            console.log('URL copied to clipboard');
          } catch (err) {
            console.log('Clipboard API failed:', err);

            // Ultimate fallback: Show copy dialog (older browsers)
            const url = window.location.href;
            if (window.prompt) {
              window.prompt('Copy this URL to share:', url);
            }
          }
        });
      </script>

      <?php include 'includes/footer.php'; ?>
  </body>

</html>
