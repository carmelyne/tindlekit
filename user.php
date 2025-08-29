<?php
// user.php — profile page for idea submitters
// Purpose: show aggregate creator signals + list of their ideas
// Contract: read-only; no API/DB schema changes; no inline CSS

// ---- helpers ---------------------------------------------------------------
function h($s)
{
  return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}
function env($k, $d = null)
{
  return isset($_ENV[$k]) ? $_ENV[$k] : (getenv($k) !== false ? getenv($k) : $d);
}

// ---- db bootstrap ----------------------------------------------------------
$pdo = null;
try {
  // Load environment + DSN from config.php (single source of truth)
  require_once __DIR__ . '/config.php';
  if (!isset($DB_DSN) || $DB_DSN === '') {
    throw new RuntimeException('Database DSN is not configured. Check config.php/.env');
  }
  $user = isset($DB_USER) ? $DB_USER : (getenv('DB_USER') ?: 'root');
  $pass = isset($DB_PASS) ? $DB_PASS : (getenv('DB_PASS') ?: '');
  $pdo = new PDO($DB_DSN, $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo '<!doctype html><meta charset="utf-8"><title>DB Error</title><pre style="white-space:pre-wrap">' . h($e->getMessage()) . "\n" . h($e->getFile() . ':' . $e->getLine()) . '</pre>';
  exit;
}

// ---- input ----------------------------------------------------------------
$username = isset($_GET['username']) ? (string) $_GET['username'] : null;
// allow only username-safe characters
if ($username !== null) {
  $username = preg_replace('~[^A-Za-z0-9._-]~', '', $username);
  if ($username === '') $username = null;
}
$email = filter_input(INPUT_GET, 'email', FILTER_VALIDATE_EMAIL);
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

try {
  if ($username !== null) {
    // Resolve by username first (preferred pretty URL)
    $stmt = $pdo->prepare('SELECT user_id AS id, email, display_name, username FROM users WHERE username = ? LIMIT 1');
    $stmt->execute([$username]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($u) {
      $id = (int) ($u['id'] ?? 0);
      $email = $u['email'] ?? $email;
      // Keep $username as-is for canonical later
    } else {
      http_response_code(404);
      echo '<!doctype html><meta charset="utf-8"><title>User</title><div class="mx-auto max-w-3xl p-6">'
        . '<h1 class="text-2xl font-semibold">User profile</h1>'
        . '<p class="mt-4 text-sm opacity-80">Profile not found.</p>'
        . '<p class="mt-2"><a class="underline" href="/">Back to Leaderboard</a></p>'
        . '</div>';
      exit;
    }
  } elseif (!$email && $id) {
    // If no email but id is present, resolve email by id (auth_users first, then users)
    $stmt = $pdo->prepare('SELECT email FROM auth_users WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && !empty($row['email'])) {
      $email = $row['email'];
    } else {
      $stmt = $pdo->prepare('SELECT email, username FROM users WHERE user_id = ?');
      $stmt->execute([$id]);
      $row2 = $stmt->fetch(PDO::FETCH_ASSOC);
      if ($row2 && !empty($row2['email'])) {
        $email = $row2['email'];
        if (empty($username) && !empty($row2['username'])) $username = $row2['username'];
      }
    }
  }
} catch (Throwable $e) { /* ignore; handled below */ }

if (!$email && !$id && !$username) {
  http_response_code(400);
  echo '<!doctype html><meta charset="utf-8"><title>User</title><div class="mx-auto max-w-3xl p-6">'
    . '<h1 class="text-2xl font-semibold">User profile</h1>'
    . '<p class="mt-4 text-sm opacity-80">Missing or invalid <code>username</code>, <code>id</code>, or <code>email</code> parameter.</p>'
    . '<p class="mt-2"><a class="underline" href="/">Back to Leaderboard</a></p>'
    . '</div>';
  exit;
}

// Try to resolve email via ideas if still unknown but we have a user id
if (!$email && $id) {
  try {
    $stmt = $pdo->prepare('SELECT submitter_email FROM ideas WHERE submitter_user_id = ? AND submitter_email IS NOT NULL AND submitter_email <> "" ORDER BY created_at DESC LIMIT 1');
    $stmt->execute([$id]);
    $emailGuess = $stmt->fetchColumn();
    if ($emailGuess) { $email = $emailGuess; }
  } catch (Throwable $e) { /* ignore */ }
}

try {
  if ($username) {
    // Already on pretty URL; nothing to do
  } elseif ($id) {
    // If we have an id but not pretty URL, try to fetch username for canonical redirect
    $stmt = $pdo->prepare('SELECT username FROM users WHERE user_id = ?');
    $stmt->execute([$id]);
    $uname = $stmt->fetchColumn();
    if ($uname) {
      header('Location: /user/' . rawurlencode($uname), true, 302);
      exit;
    }
    // otherwise stay on /user.php?id=...
  } else {
    // Resolve id by email to canonicalize
    $stmt = $pdo->prepare('SELECT user_id AS id, username FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($u && !empty($u['username'])) {
      header('Location: /user/' . rawurlencode($u['username']), true, 302);
      exit;
    } elseif ($u && !empty($u['id'])) {
      header('Location: /user.php?id=' . (int)$u['id'], true, 302);
      exit;
    }
  }
} catch (Throwable $e) { /* non-fatal */ }

// ---- queries ---------------------------------------------------------------
$summary = null;
$ideas = [];
try {
  // Aggregate signals
  if ($email) {
    $stmt = $pdo->prepare('SELECT submitter_name, submitter_email, COUNT(*) AS ideas_count, COALESCE(SUM(tokens),0) AS total_tokens, COALESCE(SUM(likes),0) AS total_likes FROM ideas WHERE submitter_email = ?');
    $stmt->execute([$email]);
  } else {
    $stmt = $pdo->prepare('SELECT submitter_name, MAX(submitter_email) AS submitter_email, COUNT(*) AS ideas_count, COALESCE(SUM(tokens),0) AS total_tokens, COALESCE(SUM(likes),0) AS total_likes FROM ideas WHERE submitter_user_id = ?');
    $stmt->execute([$id]);
  }
  $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: null;

  // Idea list
  if ($email) {
    $stmt = $pdo->prepare('SELECT id, title, summary, category, tokens, likes, created_at, slug FROM ideas WHERE submitter_email = ? ORDER BY created_at DESC LIMIT 100');
    $stmt->execute([$email]);
  } else {
    $stmt = $pdo->prepare('SELECT id, title, summary, category, tokens, likes, created_at, slug FROM ideas WHERE submitter_user_id = ? ORDER BY created_at DESC LIMIT 100');
    $stmt->execute([$id]);
  }
  $ideas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  http_response_code(500);
  echo '<!doctype html><meta charset="utf-8"><title>User</title><div class="mx-auto max-w-3xl p-6">'
    . '<h1 class="text-2xl font-semibold">User profile</h1>'
    . '<p class="mt-4 text-sm text-red-600">Query error.</p>'
    . (isset($_GET['dev']) ? '<pre class="mt-4 p-3 rounded bg-black/5">' . h($e->getMessage()) . '</pre>' : '')
    . '</div>';
  exit;
}

$name = $summary && $summary['submitter_name'] ? $summary['submitter_name'] : 'Unknown';
$ideasCount = (int) ($summary['ideas_count'] ?? 0);
$totalTokens = (int) ($summary['total_tokens'] ?? 0);
$totalLikes = (int) ($summary['total_likes'] ?? 0);

// SEO
$pageType = 'user';
$pageTitle = ($name !== 'Unknown' ? $name : ($username ?: 'User')) . ' — Tindlekit Profile';
$canonicalURL = $username ? ('https://tindlekit.com/user/' . rawurlencode($username)) : ($id ? ('https://tindlekit.com/user.php?id=' . (int)$id) : 'https://tindlekit.com/user');

?>
<!doctype html>
<html lang="en" class="h-full">
  <?php include 'includes/head.php'; ?>

  <body class="h-full">
    <!-- Three.js background container -->
    <div id="app-bg"></div>
    <?php include 'includes/header.php'; ?>
    <main class="mx-auto max-w-5xl sm:px-6 py-8">
      <header class="tk-card p-8 mb-8">
        <div class="flex items-start justify-between gap-4">
          <div>
            <h1 class="text-3xl font-bold text-tk-fg mb-2"><?= h($name) ?></h1>
            <div class="text-tk-muted">
              <span class="align-middle"><?= h($email ?: ($username ? ('@'.$username) : '')) ?></span>
              <button id="contactBtn"
                class="ml-3 inline-flex items-center gap-1 px-2 py-1 text-xs bg-tk-card border border-tk-border rounded hover:bg-tk-border transition-colors"
                title="Copy email to clipboard">
                <i class="iconoir-mail text-tk-accent"></i>
                <span>Contact</span>
              </button>
            </div>
          </div>
          <nav>
            <a class="tk-btn tk-btn-secondary" href="/">← Back to Leaderboard</a>
          </nav>
        </div>
      </header>

      <section class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
        <div class="tk-card p-6 text-center">
          <div class="text-tk-muted text-sm font-medium uppercase tracking-wide">Ideas</div>
          <div class="text-3xl font-bold text-tk-fg mt-2"><?= $ideasCount ?></div>
        </div>
        <div class="tk-card p-6 text-center">
          <div class="text-tk-muted text-sm font-medium uppercase tracking-wide">🍀 AI Tokens</div>
          <div class="text-3xl font-bold text-tk-accent mt-2"><?= $totalTokens ?></div>
        </div>
        <div class="tk-card p-6 text-center">
          <div class="text-tk-muted text-sm font-medium uppercase tracking-wide">Total Likes</div>
          <div class="text-3xl font-bold text-tk-success mt-2"><?= $totalLikes ?></div>
        </div>
      </section>

      <section>
        <h2 class="text-2xl font-semibold text-tk-fg mb-6">Ideas by <?= h($name) ?></h2>
        <?php if (!$ideas): ?>
          <div class="tk-card p-8 text-center">
            <div class="text-tk-muted text-lg">No ideas yet</div>
            <p class="text-tk-muted mt-2">This creator hasn't submitted any ideas to the platform.</p>
          </div>
        <?php else: ?>
          <div class="space-y-4">
            <?php foreach ($ideas as $row): ?>
              <article class="tk-card p-6 hover:transform hover:-translate-y-1 transition-all duration-200">
                <div class="flex items-start justify-between gap-4 mb-4">
                  <h3 class="text-lg font-semibold text-tk-fg">
<?php
  $ideaUrl = '/idea.php?id='.(int)$row['id'];
  if (isset($row['slug']) && $row['slug']) {
    $ideaUrl = '/idea/'.h($row['slug']);
  }
?>
                    <a class="stretched-link hover:text-tk-accent transition-colors"
                      href="<?= $ideaUrl ?>">
                      <?= h($row['title']) ?>
                    </a>
                  </h3>
                  <div class="flex items-center gap-3 text-sm">
                    <span class="tk-badge tk-badge-success"><?= h($row['category']) ?></span>
                    <div class="flex items-center gap-2 text-tk-muted">
                      <span>🍀 <?= (int) $row['tokens'] ?></span>
                      <span>❤️ <?= (int) $row['likes'] ?></span>
                    </div>
                  </div>
                </div>

                <?php if (!empty($row['summary'])): ?>
                  <p class="text-tk-muted leading-relaxed mb-4 line-clamp-3"><?= h($row['summary']) ?></p>
                <?php endif; ?>

                <div class="text-sm text-tk-muted">
                  <time datetime="<?= h($row['created_at']) ?>">
                    <?= date('M j, Y', strtotime((string) $row['created_at'])) ?>
                  </time>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </main>

    <script type="module" src="/main.js"></script>
    <script type="module">
      // Dispatch a profile view event for downstream analytics/signals
      window.dispatchEvent(new CustomEvent('user:profile-view', {
        detail: { email: <?= json_encode($email) ?>, ideasCount: <?= (int) $ideasCount ?>, totalTokens: <?= (int) $totalTokens ?>, totalLikes: <?= (int) $totalLikes ?> }
      }));

      // Contact button functionality
      document.getElementById('contactBtn')?.addEventListener('click', async function () {
        const email = <?= json_encode($email) ?>;
        if (!email) return;

        try {
          await navigator.clipboard.writeText(email);

          // Visual feedback
          const button = this;
          const icon = button.querySelector('i');
          const text = button.querySelector('span');
          const originalIcon = icon.className;
          const originalText = text.textContent;

          // Change to success state
          icon.className = 'iconoir-check text-tk-success';
          text.textContent = 'Copied!';
          button.style.borderColor = 'var(--tk-success)';
          button.style.background = 'rgba(34, 197, 94, 0.1)';

          // Reset after 2 seconds
          setTimeout(() => {
            icon.className = originalIcon;
            text.textContent = originalText;
            button.style.borderColor = '';
            button.style.background = '';
          }, 2000);

        } catch (err) {
          console.log('Clipboard failed:', err);

          // Fallback: show prompt dialog
          if (window.prompt) {
            window.prompt('Copy this email address:', email);
          }
        }
      });
    </script>
    <?php include 'includes/footer.php'; ?>
  </body>

</html>
