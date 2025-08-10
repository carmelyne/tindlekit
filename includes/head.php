<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Tindlekit — Open Source Ideas Leaderboard</title>
  <meta name="description"
    content="Discover and support the best open source ideas, ranked by AI Tokens from our community. The Andrej Effect connects innovators with contributors and showcases early-stage projects." />
  <link rel="icon" href="/favicon.ico" />
  <link rel="stylesheet" href="/styles.css?v=<?= time() ?>" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/lucaburgio/iconoir@main/css/iconoir.css">
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
  <?php $__OVERLOAD__ = getenv('LOAD_SHED') === '1'; ?>
  <script>window.__OVERLOAD__ = <?= $__OVERLOAD__ ? 'true' : 'false' ?>;</script>
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
