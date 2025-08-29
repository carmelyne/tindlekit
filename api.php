<?php
// api.php — minimal endpoints for Andrej Tokens
header('Content-Type: application/json; charset=utf-8');
header('Referrer-Policy: no-referrer');

$action = $_GET['action'] ?? '';

// Load DB config
if (!file_exists(__DIR__ . '/config.php')) {
  http_response_code(500);
  echo json_encode(['error' => 'Missing config.php']);
  exit;
}
require __DIR__ . '/config.php';

// Load-shed switch: keep reads available, shed writes during spikes
if (!function_exists('shed_enabled')) {
function shed_enabled(): bool {
  $v = getenv('LOAD_SHED');
  return $v === '1' || strtolower((string)$v) === 'true';
}
}

// --- Helpers: category whitelist + tag normalization ---
if (!function_exists('allowed_categories')) {
function allowed_categories(): array
{
  // Load categories from categories.json if available, else fallback to hardcoded list
  $json_path = __DIR__ . '/categories.json';
  if (file_exists($json_path)) {
    $categories = json_decode(file_get_contents($json_path), true);
    if (is_array($categories)) {
      return $categories;
    }
  }
  // fallback
  return [
    'Research',
    'Open Source',
    'Product',
    'Tooling',
    'Education',
    'Community',
    'Infrastructure',
    'Design',
    'Governance',
    'Other'
  ];
}
}
if (!function_exists('sanitize_category')) {
function sanitize_category(?string $cat): string
{
  $c = trim((string) $cat);
  if ($c === '')
    return 'Other';
  // Case-insensitive whitelist
  $allow = allowed_categories();
  foreach ($allow as $a) {
    if (strcasecmp($a, $c) === 0)
      return $a;
  }
  return 'Other';
}
}
if (!function_exists('normalize_tags_input')) {
function normalize_tags_input($raw): string
{
  // Accept CSV or JSON array; output canonical CSV (lowercase, trimmed, de-duped)
  if (is_string($raw)) {
    $rawStr = trim($raw);
    $arr = json_decode($rawStr, true);
    if (!is_array($arr)) {
      $arr = array_map('trim', explode(',', $rawStr));
    }
  } elseif (is_array($raw)) {
    $arr = $raw;
  } else {
    $arr = [];
  }
  $norm = [];
  foreach ($arr as $t) {
    $t2 = strtolower(trim((string) $t));
    if ($t2 !== '' && !in_array($t2, $norm, true))
      $norm[] = $t2;
  }
  return implode(',', $norm);
}
}

try {
  $options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  ];

  // MySQL-specific options only for MySQL connections
  if (strpos($DB_DSN, 'mysql:') === 0) {
    $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4";
  }

  $pdo = new PDO($DB_DSN, $DB_USER, $DB_PASS, $options);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['error' => 'DB connection failed']);
  exit;
}

if (!function_exists('client_ip_bin')) {
function client_ip_bin(): ?string
{
  $ip = $_SERVER['REMOTE_ADDR'] ?? null;
  if (!$ip)
    return null;
  $packed = @inet_pton($ip);
  return $packed === false ? null : $packed;
}
}

if (!function_exists('env_int')) {
function env_int(string $key, int $default): int {
  $v = getenv($key);
  if ($v === false || $v === null || $v === '') return $default;
  $n = (int)$v;
  return $n > 0 ? $n : $default;
}
}

if (!function_exists('normalize_email')) {
function normalize_email(string $s): string {
  return strtolower(trim($s));
}
}

// --- Helper: slugify, unique_token, make_username ---
if (!function_exists('slugify')) {
function slugify(string $s): string {
  // ASCII fold (safe fallback if iconv not available)
  if (function_exists('iconv')) {
    $i = @iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$s);
    if ($i !== false) $s = $i;
  }
  $s = strtolower($s);
  $s = preg_replace('~[^a-z0-9]+~','-',$s);
  $s = preg_replace('~-+~','-',$s);
  return trim((string)$s, '-') ?: 'x';
}
}
if (!function_exists('unique_token')) {
function unique_token(PDO $pdo, string $table, string $col, string $base): string {
  $tok = $base; $n = 2;
  $check = $pdo->prepare("SELECT 1 FROM `$table` WHERE `$col` = ? LIMIT 1");
  while (true) {
    $check->execute([$tok]);
    if (!$check->fetchColumn()) return $tok;
    $tok = $base . '-' . ($n++);
  }
}
}
if (!function_exists('make_username')) {
function make_username(?string $name, ?string $email): string {
  $local = '';
  if ($name && trim($name) !== '') {
    $local = $name;
  } elseif ($email && strpos($email,'@') !== false) {
    $local = explode('@',$email)[0];
  } else {
    $local = (string)$email;
  }
  return slugify($local);
}
}

if (!function_exists('verify_turnstile')) {
function verify_turnstile(?string $token, ?string $ip): bool {
  // Test bypass: allow unit/e2e tests to skip external verification
  if (getenv('BYPASS_TURNSTILE') === '1') {
    return true;
  }

  $secret = getenv('CF_TURNSTILE_SECRET');
  // Treat as failure if secret is configured but token missing
  if (!$secret || !$token) return false;

  $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
      'secret'   => $secret,
      'response' => $token,
      'remoteip' => $ip ?? ''
    ]),
    CURLOPT_TIMEOUT => 5,
  ]);
  $res = curl_exec($ch);
  if ($res === false) return false;
  $data = json_decode($res, true);
  return is_array($data) && !empty($data['success']);
}
}

// Global guard for overload/read-only mode
if (shed_enabled() && (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST')) {
  http_response_code(503);
  header('Retry-After: 60');
  header('Cache-Control: no-store');
  echo json_encode([
    'error' => 'busy',
    'message' => 'Server is under temporary heavy load. Please try again in a minute.'
  ]);
  exit;
}

switch ($action) {
  case 'status':
    echo json_encode([
      'overload' => shed_enabled(),
      'host' => ($_SERVER['HTTP_HOST'] ?? null)
    ]);
    break;
  case 'list_ideas':
    try {
      // Optional filtering + pagination
      $category = isset($_GET['category']) ? trim((string)$_GET['category']) : '';
      $limit = isset($_GET['limit']) ? max(1, min(100, (int)$_GET['limit'])) : 24; // sane default
      $offset = isset($_GET['offset']) ? max(0, (int)$_GET['offset']) : 0;

      // Load categories for validation (single source of truth)
      $catPath = __DIR__ . '/categories.json';
      $allowed = [];
      if (is_readable($catPath)) {
        $tmp = json_decode(file_get_contents($catPath), true);
        if (is_array($tmp)) $allowed = $tmp;
      }

      if ($category !== '' && $allowed && !in_array($category, $allowed, true)) {
        echo json_encode([]);
        break;
      }

      // Inline integers for LIMIT/OFFSET to avoid PDO driver issues
      $limitSql = (string)$limit;
      $offsetSql = (string)$offset;

      if ($category !== '') {
        $sql = "SELECT id, title, summary, tokens, likes, url, video_url, file_path, category, tags
                  FROM ideas WHERE category = ?
                  ORDER BY tokens DESC, id ASC LIMIT $limitSql OFFSET $offsetSql";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$category]);
      } else {
        $sql = "SELECT id, title, summary, tokens, likes, url, video_url, file_path, category, tags
                  FROM ideas ORDER BY tokens DESC, id ASC LIMIT $limitSql OFFSET $offsetSql";
        $stmt = $pdo->query($sql);
      }
      echo json_encode($stmt->fetchAll());
    } catch (Throwable $e) {
      http_response_code(500);
      echo json_encode(['error' => 'list_ideas failed', 'detail' => $e->getMessage()]);
    }
    break;

  case 'create_idea':
    try {
      $submitter_name = trim($_POST['submitter_name'] ?? '');
      $submitter_email = trim($_POST['submitter_email'] ?? '');
      $title = trim($_POST['title'] ?? '');
      $summary = trim($_POST['summary'] ?? '');
      $license_type = trim($_POST['license_type'] ?? '');
      $support_needs = trim($_POST['support_needs'] ?? '');
      $url = trim($_POST['url'] ?? '');
      $video_url = trim($_POST['video_url'] ?? '');
      $category = sanitize_category($_POST['category'] ?? null);
      $tags_csv = normalize_tags_input($_POST['tags'] ?? '');

      // --- SpamGuard server checks -------------------------------------------------
      // Honeypot: silently drop obvious bots
      if (!empty($_POST['fax_number'] ?? '')) {
        http_response_code(400);
        echo json_encode(['error' => 'spam_detected']);
        break;
      }

      // Timing check: reject forms submitted too quickly (< 1500ms) or absurdly old (> 30min)
      $nowMs = (int)floor(microtime(true) * 1000);
      $renderedAt = isset($_POST['form_rendered_at']) ? (int)$_POST['form_rendered_at'] : 0;
      if ($renderedAt > 0) {
        $delta = $nowMs - $renderedAt;
        if ($delta < 1500 || $delta > (30 * 60 * 1000)) {
          http_response_code(400);
          echo json_encode(['error' => 'suspicious_timing']);
          break;
        }
      }

      // Normalize and validate email
      $submitter_email = normalize_email($submitter_email);
      if (!filter_var($submitter_email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid email']);
        break;
      }

      // Cloudflare Turnstile (if configured)
      $turnstileSecret = getenv('CF_TURNSTILE_SECRET');
      if ($turnstileSecret) {
        $tsToken = $_POST['cf-turnstile-response'] ?? '';
        $ipForTs = $_SERVER['REMOTE_ADDR'] ?? null;
        if (!verify_turnstile($tsToken, $ipForTs)) {
          http_response_code(400);
          echo json_encode(['error' => 'turnstile_failed']);
          break;
        }
      }

      // Per-IP/email daily rate limit (no extra tables needed)
      $createLimit = env_int('CREATE_IDEA_DAILY_LIMIT', 5);
      if ($createLimit > 0) {
        $start = new DateTime('today', new DateTimeZone('UTC'));
        $end = new DateTime('tomorrow', new DateTimeZone('UTC'));
        $startStr = $start->format('Y-m-d H:i:s');
        $endStr = $end->format('Y-m-d H:i:s');
        $ipBin = client_ip_bin();

        $q = 'SELECT COUNT(*) AS c FROM ideas WHERE created_at >= ? AND created_at < ? AND (submitter_email = ?';
        $params = [$startStr, $endStr, $submitter_email];
        if ($ipBin !== null) { $q .= ' OR submit_ip = ?'; $params[] = $ipBin; }
        $q .= ')';
        $chk = $pdo->prepare($q);
        $chk->execute($params);
        $row = $chk->fetch();
        if ($row && (int)$row['c'] >= $createLimit) {
          http_response_code(429);
          echo json_encode(['error' => 'rate_limited', 'message' => 'Daily idea limit reached.', 'limit' => $createLimit]);
          break;
        }
      }
      // -----------------------------------------------------------------------------

      if ($submitter_name === '' || $submitter_email === '' || $title === '' || $summary === '' || $license_type === '') {
        http_response_code(400);
        echo json_encode(['error' => 'Missing fields']);
        break;
      }

      // Validate URLs (optional, basic)
      if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid URL']);
        break;
      }
      if ($video_url !== '' && !filter_var($video_url, FILTER_VALIDATE_URL)) {
        http_response_code(400);
        echo json_encode(['error' => 'Invalid video URL']);
        break;
      }

      // --- Derive username & user_id (no auth) + idea slug ---------------------
      $pdo->beginTransaction();
      try {
        // Upsert user by email
        $uSel = $pdo->prepare('SELECT user_id, username, display_name FROM users WHERE email = ? LIMIT 1');
        $uSel->execute([$submitter_email]);
        $u = $uSel->fetch(PDO::FETCH_ASSOC);

        if ($u) {
          $submitter_user_id = (int)$u['user_id'];
          $username = (string)($u['username'] ?? '');
          if ($username === '') {
            $base = make_username($submitter_name, $submitter_email);
            $username = unique_token($pdo, 'users', 'username', $base);
            $uUpd = $pdo->prepare('UPDATE users SET display_name = ?, username = ?, updated_at = NOW() WHERE user_id = ?');
            $uUpd->execute([$submitter_name, $username, $submitter_user_id]);
          }
        } else {
          $base = make_username($submitter_name, $submitter_email);
          $username = unique_token($pdo, 'users', 'username', $base);
          $now = date('Y-m-d H:i:s');
          $uIns = $pdo->prepare('INSERT INTO users (email, display_name, username, created_at, updated_at) VALUES (?,?,?,?,?)');
          $uIns->execute([$submitter_email, $submitter_name, $username, $now, $now]);
          $submitter_user_id = (int)$pdo->lastInsertId();
        }

        // Prepare canonical idea slug (unique)
        $baseSlug = slugify($title);
        $idea_slug = unique_token($pdo, 'ideas', 'slug', $baseSlug);

        // Stash for later phases in this request
        $_TK_USERNAME = $username;
        $_TK_USER_ID  = $submitter_user_id;
        $_TK_IDEA_SLUG = $idea_slug;

        // Defer commit until after idea insert
      } catch (Throwable $e) {
        $pdo->rollBack();
        http_response_code(500);
        echo json_encode(['error' => 'user_upsert_failed']);
        break;
      }
      // -------------------------------------------------------------------------

      $file_path = null;
      $file_mime = null;
      $file_size = null;

      if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $_FILES['attachment'];
        if ($file['error'] !== UPLOAD_ERR_OK) {
          http_response_code(400);
          echo json_encode(['error' => 'File upload error']);
          break;
        }
        if ($file['size'] > 10 * 1024 * 1024) {
          http_response_code(400);
          echo json_encode(['error' => 'File too large']);
          break;
        }
        $allowed_mimes = [
          'application/pdf',
          'image/png',
          'image/jpeg',
          'image/webp',
          'image/gif',
          'text/markdown',
          'text/plain',
        ];
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $allowed_mimes, true)) {
          http_response_code(400);
          echo json_encode(['error' => 'Invalid file type']);
          break;
        }
        $ext_map = [
          'application/pdf' => 'pdf',
          'image/png' => 'png',
          'image/jpeg' => 'jpg',
          'image/webp' => 'webp',
          'image/gif' => 'gif',
          'text/markdown' => 'md',
          'text/plain' => 'md',
        ];
        $ext = $ext_map[$mime] ?? 'bin';

        $upload_dir = __DIR__ . '/uploads';
        if (!is_dir($upload_dir)) {
          mkdir($upload_dir, 0755, true);
        }
        $basename = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest = $upload_dir . '/' . $basename;
        if (!move_uploaded_file($file['tmp_name'], $dest)) {
          http_response_code(500);
          echo json_encode(['error' => 'Failed to save file']);
          break;
        }
        $file_path = 'uploads/' . $basename;
        $file_mime = $mime;
        $file_size = $file['size'];
      }

      $ip = client_ip_bin();
      $stmt = $pdo->prepare('INSERT INTO ideas (
        title, summary, submit_ip, url, video_url, file_path, file_mime, file_size,
        submitter_name, submitter_email, submitter_user_id, slug,
        license_type, support_needs, category, tags
      ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
      $stmt->execute([
        $title,
        $summary,
        $ip,
        $url !== '' ? $url : null,
        $video_url !== '' ? $video_url : null,
        $file_path,
        $file_mime,
        $file_size,
        $submitter_name,
        $submitter_email,
        (int)($_TK_USER_ID ?? 0),
        (string)($_TK_IDEA_SLUG ?? ''),
        $license_type,
        $support_needs,
        $category,
        $tags_csv
      ]);
      // Commit the user/slug transaction now that idea is inserted
      $pdo->commit();
      $newId = (int)$pdo->lastInsertId();
      echo json_encode([
        'ok' => true,
        'id' => $newId,
        'idea_url' => '/idea/' . (string)($_TK_IDEA_SLUG ?? ''),
        'user_url' => '/user/' . (string)($_TK_USERNAME ?? '')
      ]);
    } catch (Throwable $e) {
      http_response_code(500);
      echo json_encode([
        'error' => 'create_idea_failed',
        'message' => $e->getMessage()
      ]);
    }
    break;
  case 'express_interest':
    // POST: idea_id, supporter_name, supporter_email, pledge_type, pledge_details
    $idea_id = (int) ($_POST['idea_id'] ?? 0);
    $supporter_name = trim($_POST['supporter_name'] ?? '');
    $supporter_email = trim($_POST['supporter_email'] ?? '');
    $pledge_type = trim($_POST['pledge_type'] ?? '');
    $pledge_details = trim($_POST['pledge_details'] ?? '');
    $tokens_amount = (int)($_POST['tokens'] ?? 0);
    $allowed_types = ['time','mentorship','token'];

    // Normalize and validate email
    $supporter_email = normalize_email($supporter_email);
    if (!filter_var($supporter_email, FILTER_VALIDATE_EMAIL)) {
      http_response_code(400);
      echo json_encode(['error' => 'Invalid email']);
      break;
    }

    // Cloudflare Turnstile (if configured)
    $turnstileSecret = getenv('CF_TURNSTILE_SECRET');
    if ($turnstileSecret) {
      $tsToken = $_POST['cf-turnstile-response'] ?? '';
      $ipForTs = $_SERVER['REMOTE_ADDR'] ?? null;
      if (!verify_turnstile($tsToken, $ipForTs)) {
        http_response_code(400);
        echo json_encode(['error' => 'turnstile_failed']);
        break;
      }
    }

    if ($idea_id <= 0 || $supporter_name === '' || $supporter_email === '' || !in_array($pledge_type, $allowed_types, true)) {
      http_response_code(400);
      echo json_encode(['error' => 'Missing fields']);
      break;
    }
    $pdo->beginTransaction();
    try {
      // Record the pledge itself
      $stmt = $pdo->prepare('INSERT INTO idea_interest (idea_id, supporter_name, supporter_email, pledge_type, pledge_details) VALUES (?, ?, ?, ?, ?)');
      $details = $pledge_details;
      if ($pledge_type === 'token' && $tokens_amount > 0) {
        $details = trim($pledge_details . ' ' . "(tokens: {$tokens_amount})");
      }
      $stmt->execute([$idea_id, $supporter_name, $supporter_email, $pledge_type, $details]);

      // If it's a token pledge, increment idea tokens and log event
      if ($pledge_type === 'token' && $tokens_amount > 0) {
        $ev = $pdo->prepare('INSERT INTO token_events (idea_id, delta, reason, actor_ip, user_agent) VALUES (?, ?, ?, ?, ?)');
        $ev->execute([$idea_id, $tokens_amount, 'pledge', client_ip_bin(), $_SERVER['HTTP_USER_AGENT'] ?? null]);
        $up = $pdo->prepare('UPDATE ideas SET tokens = tokens + ? WHERE id=?');
        $up->execute([$tokens_amount, $idea_id]);
      }

      $pdo->commit();
      echo json_encode(['ok' => true]);
    } catch (Throwable $e) {
      $pdo->rollBack();
      http_response_code(500);
      echo json_encode(['error' => 'express_interest_failed', 'message' => $e->getMessage()]);
    }
    break;
  case 'add_like':
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) {
      http_response_code(400);
      echo json_encode(['error' => 'Invalid id']);
      break;
    }
    $ip = client_ip_bin();
    $today = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d');
    $LIMIT = env_int('LIKE_DAILY_LIMIT', 5);

    $pdo->beginTransaction();
    try {
      if ($ip) {
        // Ensure like_events exists via migration
        $sel = $pdo->prepare('SELECT COUNT(*) AS c FROM like_events WHERE idea_id=? AND ip=? AND day=? FOR UPDATE');
        $sel->execute([$id, $ip, $today]);
        $row = $sel->fetch();
        $count = $row ? (int)$row['c'] : 0;
        if ($count >= $LIMIT) {
          $pdo->rollBack();
          http_response_code(429);
          echo json_encode(['error' => 'rate_limited', 'message' => 'Daily like limit reached for this idea.', 'limit' => $LIMIT]);
          break;
        }
        $ins = $pdo->prepare('INSERT INTO like_events (idea_id, ip, day, user_agent) VALUES (?, ?, ?, ?)');
        $ins->execute([$id, $ip, $today, $_SERVER['HTTP_USER_AGENT'] ?? null]);
      }
      // Increment denorm likes counter
      $up = $pdo->prepare('UPDATE ideas SET likes = likes + 1 WHERE id=?');
      $up->execute([$id]);

      $pdo->commit();
      echo json_encode(['ok' => true]);
    } catch (Throwable $e) {
      $pdo->rollBack();
      http_response_code(500);
      echo json_encode(['error' => 'like_update_failed']);
    }
    break;

  case 'get_interests':
    // GET: idea_id
    $idea_id = (int) ($_GET['idea_id'] ?? 0);
    if ($idea_id <= 0) {
      http_response_code(400);
      echo json_encode(['error' => 'Invalid idea_id']);
      break;
    }
    $stmt = $pdo->prepare('SELECT supporter_name, supporter_email, pledge_type, pledge_details FROM idea_interest WHERE idea_id = ? ORDER BY id ASC');
    $stmt->execute([$idea_id]);
    $pledges = $stmt->fetchAll();
    echo json_encode($pledges);
    break;

  case 'add_token':
    $id = (int) ($_GET['id'] ?? 0);
    if ($id <= 0) {
      http_response_code(400);
      echo json_encode(['error' => 'Invalid id']);
      break;
    }
    // Simple daily per-IP rate limit (configurable)
    $ip = client_ip_bin();
    $today = (new DateTime('now', new DateTimeZone('UTC')))->format('Y-m-d');
    $LIMIT = env_int('TOKEN_DAILY_LIMIT', 3);

    $pdo->beginTransaction();
    try {
      if ($ip) {
        $sel = $pdo->prepare('SELECT count FROM token_ip_daily WHERE idea_id=? AND ip=? AND day=? FOR UPDATE');
        $sel->execute([$id, $ip, $today]);
        $row = $sel->fetch();
        $count = $row ? (int) $row['count'] : 0;
        if ($count >= $LIMIT) {
          $pdo->rollBack();
          http_response_code(429);
          echo json_encode(['error' => 'rate_limited', 'message' => 'Daily token tap limit reached for this idea.', 'limit' => $LIMIT]);
          break;
        }
        if ($row) {
          $upd = $pdo->prepare('UPDATE token_ip_daily SET count=count+1 WHERE idea_id=? AND ip=? AND day=?');
          $upd->execute([$id, $ip, $today]);
        } else {
          $ins = $pdo->prepare('INSERT INTO token_ip_daily (idea_id, ip, day, count) VALUES (?, ?, ?, 1)');
          $ins->execute([$id, $ip, $today]);
        }
      }

      $ev = $pdo->prepare('INSERT INTO token_events (idea_id, delta, reason, actor_ip, user_agent) VALUES (?, 1, ?, ?, ?)');
      $ev->execute([$id, 'tap', $ip, $_SERVER['HTTP_USER_AGENT'] ?? null]);
      $up = $pdo->prepare('UPDATE ideas SET tokens = tokens + 1 WHERE id=?');
      $up->execute([$id]);

      $pdo->commit();
      echo json_encode(['ok' => true]);
    } catch (Throwable $e) {
      $pdo->rollBack();
      http_response_code(500);
      echo json_encode(['error' => 'Token update failed']);
    }
    break;

  case 'get_idea':
    $id = (int) ($_GET['id'] ?? 0);
    if ($id <= 0) {
      http_response_code(400);
      echo json_encode(['error' => 'Invalid id']);
      break;
    }
    $stmt = $pdo->prepare('SELECT id, title, summary, tokens, likes, url, video_url, file_path, file_mime, file_size, created_at, category, tags FROM ideas WHERE id = ?');
    $stmt->execute([$id]);
    $idea = $stmt->fetch();
    if (!$idea) {
      http_response_code(404);
      echo json_encode(['error' => 'Not found']);
      break;
    }
    echo json_encode($idea);
    break;

  case 'categories':
    $json_path = __DIR__ . '/categories.json';
    if (is_readable($json_path)) {
      $cats = json_decode(file_get_contents($json_path), true);
      if (is_array($cats)) { echo json_encode($cats); break; }
    }
    echo json_encode(allowed_categories());
    break;

  case 'turnstile_status':
    $site = getenv('CF_TURNSTILE_SITE_KEY') ?: (isset($CF_TURNSTILE_SITE_KEY) ? $CF_TURNSTILE_SITE_KEY : '');
    $secretPresent = (bool)(getenv('CF_TURNSTILE_SECRET') ?: ($CF_TURNSTILE_SECRET ?? ''));
    echo json_encode([
      'site_key_present' => (bool)$site,
      'secret_present' => $secretPresent,
      'host' => ($_SERVER['HTTP_HOST'] ?? null),
    ]);
    break;

  case 'gdpr_data_request':
    // Simple GDPR data export for a user
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      http_response_code(400);
      echo json_encode(['error' => 'Invalid email address']);
      break;
    }

    try {
      // Get user's submitted ideas
      $stmt = $pdo->prepare('SELECT id, title, summary, category, tags, tokens, likes, created_at FROM ideas WHERE submitter_email = ?');
      $stmt->execute([$email]);
      $ideas = $stmt->fetchAll();

      // Get user's pledges/interests
      $stmt = $pdo->prepare('SELECT idea_id, pledge_type, pledge_details, id as pledge_id FROM idea_interest WHERE supporter_email = ?');
      $stmt->execute([$email]);
      $pledges = $stmt->fetchAll();

      $data = [
        'email' => $email,
        'data_exported_at' => date('Y-m-d H:i:s'),
        'ideas_submitted' => $ideas,
        'pledges_made' => $pledges,
        'total_ideas' => count($ideas),
        'total_pledges' => count($pledges)
      ];

      echo json_encode($data, JSON_PRETTY_PRINT);
    } catch (Throwable $e) {
      http_response_code(500);
      echo json_encode(['error' => 'Failed to export data', 'message' => $e->getMessage()]);
    }
    break;
  default:
    http_response_code(404);
    echo json_encode(['error' => 'Unknown action']);
}
