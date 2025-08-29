<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <?php
    // —— SEO Defaults ——
    $siteName      = 'Tindlekit';
    $defaultTitle  = 'Tindlekit — Open Source Ideas Leaderboard';
    $defaultDesc   = 'Discover and support the best open source ideas, ranked by AI Tokens from our community. The Andrej Effect connects innovators with contributors and showcases early-stage projects.';

    // Accept page-level overrides set before including head.php
    $pageTitle    = isset($pageTitle) && $pageTitle !== '' ? $pageTitle : $defaultTitle;
    $metaDesc     = isset($metaDesc) && $metaDesc !== '' ? $metaDesc : $defaultDesc;
    $metaKeywords = isset($metaKeywords) && is_array($metaKeywords) ? implode(', ', $metaKeywords) : (isset($metaKeywords) ? (string)$metaKeywords : '');

    // Canonical URL (prefer https)
    $canonicalURL = isset($canonicalURL) && $canonicalURL !== '' ? $canonicalURL : '';
    if ($canonicalURL && strpos($canonicalURL, 'http://') === 0) {
      $canonicalURL = 'https://' . substr($canonicalURL, 7);
    }

    // Basic context flags (optional): $pageType can be 'home','submit','community','privacy','idea','user'
    $pageType = isset($pageType) ? $pageType : null;

    // Escape helpers
    $e = function($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); };
  ?>

  <title><?= $e($pageTitle) ?></title>
  <meta name="description" content="<?= $e($metaDesc) ?>" />
  <?php if ($metaKeywords !== ''): ?>
  <meta name="keywords" content="<?= $e($metaKeywords) ?>" />
  <?php endif; ?>
  <?php if ($canonicalURL !== ''): ?>
  <link rel="canonical" href="<?= $e($canonicalURL) ?>" />
  <?php endif; ?>

  <!-- Open Graph / Twitter -->
  <meta property="og:site_name" content="<?= $e($siteName) ?>" />
  <meta property="og:title" content="<?= $e($pageTitle) ?>" />
  <meta property="og:description" content="<?= $e($metaDesc) ?>" />
  <?php if ($canonicalURL !== ''): ?><meta property="og:url" content="<?= $e($canonicalURL) ?>" /><?php endif; ?>
  <meta property="og:type" content="<?= $pageType === 'idea' ? 'article' : 'website' ?>" />
  <meta name="twitter:card" content="summary_large_image" />
  <meta name="twitter:title" content="<?= $e($pageTitle) ?>" />
  <meta name="twitter:description" content="<?= $e($metaDesc) ?>" />

  <?php
    // —— JSON-LD (override with $jsonLd string if provided) ——
    if (!isset($jsonLd) || $jsonLd === '') {
      $json = null;
      if ($pageType === 'idea' && isset($idea) && is_array($idea)) {
        // Expect $idea['title'], $idea['description'], $idea['slug']
        $json = [
          '@context' => 'https://schema.org',
          '@type' => 'CreativeWork',
          'name' => $idea['title'] ?? $pageTitle,
          'description' => $idea['description'] ?? $metaDesc,
          'url' => $canonicalURL ?: ('https://tindlekit.com/idea/' . ($idea['slug'] ?? '')),
        ];
      } elseif ($pageType === 'user' && isset($user) && is_array($user)) {
        // Expect $user['name'] or $user['username']
        $json = [
          '@context' => 'https://schema.org',
          '@type' => 'Person',
          'name' => $user['name'] ?? ($user['username'] ?? 'Profile'),
          'url' => $canonicalURL ?: 'https://tindlekit.com/user',
        ];
      } else {
        $json = [
          '@context' => 'https://schema.org',
          '@type' => 'WebSite',
          'name' => $siteName,
          'url' => $canonicalURL ?: 'https://tindlekit.com/',
          'description' => $metaDesc,
        ];
      }
      $jsonLd = json_encode($json, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
  ?>
  <script type="application/ld+json">
    <?= $jsonLd ?>
  </script>
  <link rel="icon" href="/favicon.ico" />
  <link rel="stylesheet" href="/styles.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lucaburgio/iconoir@main/css/iconoir.css">
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
  <?php $__OVERLOAD__ = getenv('LOAD_SHED') === '1'; ?>
  <script>window.__OVERLOAD__ = <?= $__OVERLOAD__ ? 'true' : 'false' ?>;</script>

  <?php $GA_KEY = getenv('GOOGLE_ANALYTICS_KEY'); ?>
  <?php if ($GA_KEY && !empty($GA_KEY)): ?>
  <!-- Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($GA_KEY, ENT_QUOTES, 'UTF-8') ?>"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', '<?= htmlspecialchars($GA_KEY, ENT_QUOTES, 'UTF-8') ?>');
  </script>
  <?php endif; ?>
  <style>
    .tk-overload-banner {
      position: sticky;
      top: 0;
      z-index: 9999;
      display: none;
      background: #FEF3C7;
      color: #92400E;
      border: 1px solid #F59E0B;
      border-left: 0;
      border-right: 0;
      padding: 10px 14px;
      font: 600 14px/1.4 system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
      text-align: center
    }

    .tk-overload-banner strong {
      font-weight: 700
    }
  </style>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      if (!window.__OVERLOAD__) return;
      var b = document.createElement('div');
      b.className = 'tk-overload-banner';
      b.role = 'status';
      b.setAttribute('aria-live', 'polite');
      b.innerHTML = '🐋 <strong>Sorry guys!</strong> We’re trending and my tiny server is crying. Read-only for a bit — likes/pledges may be paused. Thanks for the love. 💚';
      b.style.display = 'block';
      document.body.prepend(b);
    });
  </script>
</head>
