</main>

<footer class="site-footer">
  <div class="footer-content">
    <div class="footer-row">
      <div class="footer-left">
        <div class="small muted">© <?php echo date('Y'); ?> <?php echo e(APP_NAME); ?></div>
        <div class="small muted">Πλατφόρμα δωρεών για ΜΚΟ</div>
      </div>

      <div class="footer-links" style="font-size: 14px;">
        <a href="<?php echo BASE_URL; ?>/about.php">Σχετικά</a>
        <a href="<?php echo BASE_URL; ?>/privacy.php">Πολιτική Απορρήτου</a>
        <a href="<?php echo BASE_URL; ?>/terms.php">Όροι Χρήσης</a>
        <a href="<?php echo BASE_URL; ?>/cookies.php">Cookies</a>
        <a href="<?php echo BASE_URL; ?>/gdpr-request.php">Τα Δικαιώματά σας (GDPR)</a>
        <a href="<?php echo BASE_URL; ?>/contact.php">Επικοινωνία</a>
      </div>
    </div>
    <div class="footer-bottom">
      <p class="muted" style="font-size: 14px;">
        Συμμορφωνόμαστε με τον <a href="https://eur-lex.europa.eu/eli/reg/2016/679/oj" target="_blank"
          rel="noopener">Κανονισμό (ΕΕ) 2016/679 (GDPR)</a>
      </p>
    </div>
  </div>
</footer>

<!-- Μπάνερ Συγκατάθεσης Cookies -->
<div id="cookie-consent" class="cookie-consent" style="display: none;">
  <div class="cookie-consent-content">
    <div class="cookie-consent-text">
      <h4>🍪 Χρήση Cookies</h4>
      <p>Χρησιμοποιούμε cookies για τη λειτουργία της ιστοσελίδας.
        <a href="<?php echo BASE_URL; ?>/cookies.php">Μάθετε περισσότερα</a>
      </p>
    </div>
    <div class="cookie-consent-buttons">
      <button onclick="setCookieConsent('essential')" class="btn">Μόνο Απαραίτητα</button>
      <button onclick="setCookieConsent('all')" class="btn primary">Αποδοχή Όλων</button>
      <button onclick="showCookieSettings()" class="btn-text">Ρυθμίσεις</button>
    </div>
  </div>
</div>

<!-- Παράθυρο Ρυθμίσεων Cookies -->
<div id="cookie-settings" class="cookie-settings-modal" style="display: none;">
  <div class="cookie-settings-content">
    <div class="cookie-settings-header">
      <h3>Ρυθμίσεις Cookies</h3>
      <button onclick="hideCookieSettings()" class="close-btn">&times;</button>
    </div>
    <div class="cookie-settings-body">
      <div class="cookie-option">
        <div class="cookie-option-header">
          <label class="switch">
            <input type="checkbox" id="cookie-essential" checked disabled>
            <span class="slider"></span>
          </label>
          <strong>Απαραίτητα Cookies</strong>
        </div>
        <p class="small muted">Απαραίτητα για τη βασική λειτουργία (σύνδεση, ασφάλεια). Δεν μπορούν να απενεργοποιηθούν.
        </p>
      </div>
      <div class="cookie-option">
        <div class="cookie-option-header">
          <label class="switch">
            <input type="checkbox" id="cookie-analytics">
            <span class="slider"></span>
          </label>
          <strong>Αναλυτικά Cookies</strong>
        </div>
        <p class="small muted">Μας βοηθούν να κατανοήσουμε πώς χρησιμοποιείτε την ιστοσελίδα.</p>
      </div>
      <div class="cookie-option">
        <div class="cookie-option-header">
          <label class="switch">
            <input type="checkbox" id="cookie-marketing">
            <span class="slider"></span>
          </label>
          <strong>Cookies Μάρκετινγκ</strong>
        </div>
        <p class="small muted">Για εξατομικευμένες ενημερώσεις (αυτή τη στιγμή δεν χρησιμοποιούνται).</p>
      </div>
    </div>
    <div class="cookie-settings-footer">
      <button onclick="saveCookieSettings()" class="btn primary">Αποθήκευση Ρυθμίσεων</button>
    </div>
  </div>
</div>

<script>
  // Cookies
  (function () {
    const COOKIE_NAME = 'cookie_consent';
    const COOKIE_DAYS = 365; // Διάρκεια αποθήκευσης προτιμήσεων: 1 έτος

    function getCookie(name) {
      const value = `; ${document.cookie}`;
      const parts = value.split(`; ${name}=`);
      if (parts.length === 2) return parts.pop().split(';').shift();
      return null;
    }

    function setCookie(name, value, days) {
      const date = new Date();
      date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
      document.cookie = `${name}=${value};expires=${date.toUTCString()};path=/;SameSite=Lax`;
    }

    function showBanner() {
      document.getElementById('cookie-consent').style.display = 'block';
    }

    function hideBanner() {
      document.getElementById('cookie-consent').style.display = 'none';
    }

    // Αποθήκευση επιλογής συγκατάθεσης
    window.setCookieConsent = function (type) {
      const consent = {
        essential: true, // Πάντα ενεργά (απαραίτητα για λειτουργία)
        analytics: type === 'all',
        marketing: type === 'all',
        timestamp: new Date().toISOString()
      };
      setCookie(COOKIE_NAME, JSON.stringify(consent), COOKIE_DAYS);
      hideBanner();

      // Εφαρμογή συγκατάθεσης - φόρτωση analytics αν δόθηκε άδεια
      if (consent.analytics) {
        // Εδώ θα φορτωνόταν το Google Analytics ή άλλο εργαλείο
      }
    };

    // Εμφάνιση παραθύρου ρυθμίσεων
    window.showCookieSettings = function () {
      document.getElementById('cookie-settings').style.display = 'flex';

      // Φόρτωση τρεχουσών ρυθμίσεων
      const consent = getCookie(COOKIE_NAME);
      if (consent) {
        try {
          const settings = JSON.parse(consent);
          document.getElementById('cookie-analytics').checked = settings.analytics || false;
          document.getElementById('cookie-marketing').checked = settings.marketing || false;
        } catch (e) { }
      }
    };

    window.hideCookieSettings = function () {
      document.getElementById('cookie-settings').style.display = 'none';
    };

    window.saveCookieSettings = function () {
      const consent = {
        essential: true,
        analytics: document.getElementById('cookie-analytics').checked,
        marketing: document.getElementById('cookie-marketing').checked,
        timestamp: new Date().toISOString()
      };
      setCookie(COOKIE_NAME, JSON.stringify(consent), COOKIE_DAYS);
      hideCookieSettings();
      hideBanner();
    };

    window.resetCookieConsent = function () {
      document.cookie = `${COOKIE_NAME}=;expires=Thu, 01 Jan 1970 00:00:00 GMT;path=/`;
      showBanner();
    };

    // Έλεγχος αν έχει ήδη δοθεί συγκατάθεση
    if (!getCookie(COOKIE_NAME)) {
      // Μικρή καθυστέρηση για να μην εμποδίζει τη φόρτωση
      setTimeout(showBanner, 500);
    }
  })();
</script>

<!-- Custom Confirm Modal -->
<div id="confirmModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center;">
  <div style="background: white; border-radius: 16px; padding: 32px; max-width: 420px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.3); animation: modalSlideIn 0.25s ease;">
    <div style="text-align: center; margin-bottom: 20px;">
      <div style="width: 56px; height: 56px; border-radius: 50%; background: #fef2f2; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><path d="M12 9v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
      </div>
      <h3 id="confirmTitle" style="margin: 0 0 8px; font-size: 18px; color: #1f2937;">Επιβεβαίωση</h3>
      <p id="confirmMessage" style="margin: 0; color: #6b7280; font-size: 14px; line-height: 1.5;"></p>
    </div>
    <div style="display: flex; gap: 12px;">
      <button id="confirmCancel" style="flex: 1; padding: 12px; border-radius: 10px; border: 1px solid #e5e7eb; background: white; font-size: 14px; font-weight: 600; cursor: pointer; font-family: inherit; transition: all 0.15s;">Ακύρωση</button>
      <button id="confirmOk" style="flex: 1; padding: 12px; border-radius: 10px; border: none; background: #ef4444; color: white; font-size: 14px; font-weight: 600; cursor: pointer; font-family: inherit; transition: all 0.15s;">Διαγραφή</button>
    </div>
  </div>
</div>

<style>
@keyframes modalSlideIn {
  from { opacity: 0; transform: scale(0.95) translateY(-10px); }
  to { opacity: 1; transform: scale(1) translateY(0); }
}
#confirmCancel:hover { background: #f3f4f6; }
#confirmOk:hover { background: #dc2626; }
</style>

<script>
window.siteConfirm = function(message, title, btnText) {
  return new Promise(function(resolve) {
    var modal = document.getElementById('confirmModal');
    var msgEl = document.getElementById('confirmMessage');
    var titleEl = document.getElementById('confirmTitle');
    var okBtn = document.getElementById('confirmOk');
    var cancelBtn = document.getElementById('confirmCancel');

    titleEl.textContent = title || 'Επιβεβαίωση';
    msgEl.textContent = message;
    okBtn.textContent = btnText || 'Διαγραφή';
    modal.style.display = 'flex';

    function cleanup() {
      modal.style.display = 'none';
      okBtn.removeEventListener('click', onOk);
      cancelBtn.removeEventListener('click', onCancel);
      modal.removeEventListener('click', onOverlay);
    }
    function onOk() { cleanup(); resolve(true); }
    function onCancel() { cleanup(); resolve(false); }
    function onOverlay(e) { if (e.target === modal) { cleanup(); resolve(false); } }

    okBtn.addEventListener('click', onOk);
    cancelBtn.addEventListener('click', onCancel);
    modal.addEventListener('click', onOverlay);
  });
};

// Auto-bind: φόρμες με data-confirm attribute
function initConfirmForms() {
  document.querySelectorAll('form[data-confirm]').forEach(function(form) {
    form.addEventListener('submit', function(e) {
      e.preventDefault();
      var msg = this.getAttribute('data-confirm');
      var title = this.getAttribute('data-confirm-title') || 'Επιβεβαίωση';
      var btn = this.getAttribute('data-confirm-btn') || 'Διαγραφή';
      var f = this;
      siteConfirm(msg, title, btn).then(function(ok) {
        if (ok) {
          // Remove the listener temporarily and submit
          var clone = f.cloneNode(true);
          f.parentNode.replaceChild(clone, f);
          clone.submit();
        }
      });
    });
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initConfirmForms);
} else {
  initConfirmForms();
}
</script>

<?php if (!empty($loadGsap)): ?>
  <!-- Primary: CDN -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"
          integrity="sha384-d+vyQ0dYcymoP8ndq2hW7FGC50nqGdXUEgoOUGxbbkAJwZqL7h+jKN0GGgn9hFDS"
          crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <!-- Fallback: load local copy if CDN failed (window.gsap will be undefined) -->
  <script>
    if (typeof window.gsap === 'undefined') {
      document.write('<script src="<?php echo BASE_URL; ?>/public/vendor/gsap/gsap.min.js"><\/script>');
    }
  </script>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/ScrollTrigger.min.js"
          integrity="sha384-poC0r6usQOX2Ayt/VGA+t81H6V3iN9L+Irz9iO8o+s0X20tLpzc9DOOtnKxhaQSE"
          crossorigin="anonymous" referrerpolicy="no-referrer"></script>
  <script>
    if (typeof window.ScrollTrigger === 'undefined') {
      document.write('<script src="<?php echo BASE_URL; ?>/public/vendor/gsap/ScrollTrigger.min.js"><\/script>');
    }
  </script>

  <script src="<?php echo BASE_URL; ?>/public/js/gsap-animations.js?v=<?php echo filemtime(__DIR__ . '/../public/js/gsap-animations.js'); ?>"></script>
<?php endif; ?>

</body>

</html>