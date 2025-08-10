<!doctype html>
<html lang="en" class="h-full">

<?php include 'includes/head.php'; ?>

<body class="h-full">
  <!-- Three.js background container -->
  <div id="app-bg"></div>
  <?php include 'includes/header.php'; ?>

  <main class="mx-auto max-w-4xl px-4 py-8">
    <div class="tk-card p-8">
      <h1 class="text-3xl font-bold text-tk-fg mb-6">Privacy Policy</h1>
      
      <div class="prose prose-invert max-w-none">
        <p class="text-tk-muted mb-6"><em>Last updated: <?= date('F j, Y') ?></em></p>

        <section class="mb-8">
          <h2 class="text-xl font-semibold text-tk-fg mb-4">What We Collect</h2>
          <div class="text-tk-muted space-y-3">
            <p><strong>Personal Information:</strong> Name and email address when you submit ideas or pledges</p>
            <p><strong>Technical Data:</strong> IP address, browser type, and usage analytics for spam prevention</p>
            <p><strong>Optional Data:</strong> Project URLs, files, and supporting materials you choose to share</p>
          </div>
        </section>

        <section class="mb-8">
          <h2 class="text-xl font-semibold text-tk-fg mb-4">How We Use Your Data</h2>
          <div class="text-tk-muted space-y-3">
            <p>• Display your name and ideas publicly on the platform</p>
            <p>• Contact you about your submissions or platform updates</p>
            <p>• Prevent spam and maintain platform security</p>
            <p>• Improve the platform experience and features</p>
          </div>
        </section>

        <section class="mb-8">
          <h2 class="text-xl font-semibold text-tk-fg mb-4">Your Rights (GDPR)</h2>
          <div class="text-tk-muted space-y-3">
            <p><strong>Access:</strong> Request a copy of your personal data</p>
            <p><strong>Rectification:</strong> Correct inaccurate personal information</p>
            <p><strong>Erasure:</strong> Request deletion of your personal data</p>
            <p><strong>Portability:</strong> Receive your data in a structured format</p>
            <p><strong>Objection:</strong> Object to processing of your personal data</p>
          </div>
        </section>

        <section class="mb-8">
          <h2 class="text-xl font-semibold text-tk-fg mb-4">Data Sharing</h2>
          <div class="text-tk-muted space-y-3">
            <p>We <strong>never</strong> sell your personal data to third parties.</p>
            <p>Your ideas and name are publicly visible by design to foster open collaboration.</p>
            <p>We may share anonymized usage data to improve the platform.</p>
          </div>
        </section>

        <section class="mb-8">
          <h2 class="text-xl font-semibold text-tk-fg mb-4">Contact & Requests</h2>
          <div class="text-tk-muted">
            <p>For privacy requests or questions, contact us at:</p>
            <p class="mt-2">
              <a href="mailto:hello@carmelyne.com" class="text-tk-accent hover:text-tk-fg transition-colors">
                hello@carmelyne.com
              </a>
            </p>
            <p class="mt-4 text-sm">We'll respond to privacy requests within 30 days.</p>
          </div>
        </section>

        <section class="mb-8">
          <h2 class="text-xl font-semibold text-tk-fg mb-4">Data Security</h2>
          <div class="text-tk-muted space-y-3">
            <p>We implement appropriate technical and organizational measures to protect your data.</p>
            <p>All data transmission is encrypted using industry-standard SSL/TLS.</p>
            <p>We regularly review and update our security practices.</p>
          </div>
        </section>

        <section>
          <h2 class="text-xl font-semibold text-tk-fg mb-4">Changes to This Policy</h2>
          <div class="text-tk-muted">
            <p>We may update this privacy policy from time to time. Changes will be posted on this page with an updated revision date.</p>
          </div>
        </section>
      </div>
    </div>
  </main>

  <script type="module" src="/main.js"></script>
  <?php include 'includes/footer.php'; ?>
</body>

</html>