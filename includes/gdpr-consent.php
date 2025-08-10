<?php
// Simple GDPR consent banner
// Only show if user hasn't consented yet
?>
<div id="gdprConsent" class="fixed bottom-0 left-0 right-0 bg-tk-card border-t border-tk-border p-4 shadow-lg z-50 transform translate-y-full transition-transform duration-300" style="display: none;">
  <div class="mx-auto max-w-6xl flex flex-col sm:flex-row items-center justify-between gap-4">
    <div class="flex-1 text-sm text-tk-muted">
      <p>
        We use essential cookies to make this platform work. By continuing to use Tindlekit, you accept our 
        <a href="/privacy.php" class="text-tk-accent hover:text-tk-fg transition-colors underline">Privacy Policy</a>.
      </p>
    </div>
    <div class="flex gap-2 flex-shrink-0">
      <button id="gdprAccept" class="tk-btn tk-btn-primary px-4 py-2 text-sm">
        Accept
      </button>
      <button id="gdprDecline" class="tk-btn tk-btn-secondary px-4 py-2 text-sm">
        Decline
      </button>
    </div>
  </div>
</div>

<script>
(function() {
  const banner = document.getElementById('gdprConsent');
  const acceptBtn = document.getElementById('gdprAccept');
  const declineBtn = document.getElementById('gdprDecline');
  
  // Check if user has already made a choice
  const consent = localStorage.getItem('tk-gdpr-consent');
  
  if (!consent) {
    // Show banner after a brief delay
    setTimeout(() => {
      banner.style.display = 'block';
      setTimeout(() => {
        banner.classList.remove('translate-y-full');
      }, 100);
    }, 1000);
  }
  
  acceptBtn?.addEventListener('click', () => {
    localStorage.setItem('tk-gdpr-consent', 'accepted');
    localStorage.setItem('tk-gdpr-date', new Date().toISOString());
    hideBanner();
  });
  
  declineBtn?.addEventListener('click', () => {
    localStorage.setItem('tk-gdpr-consent', 'declined');
    localStorage.setItem('tk-gdpr-date', new Date().toISOString());
    hideBanner();
    
    // Optionally disable non-essential features
    console.log('GDPR: Non-essential features disabled');
  });
  
  function hideBanner() {
    banner.classList.add('translate-y-full');
    setTimeout(() => {
      banner.style.display = 'none';
    }, 300);
  }
})();
</script>