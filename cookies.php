<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 800px; margin: 120px auto 60px; padding: 0 24px;">
  <div class="card legal-document">
    <h1>Πολιτική Cookies</h1>
    <p class="legal-meta">Τελευταία ενημέρωση: <?php echo date('d/m/Y'); ?></p>

    <div class="legal-intro">
      <p>Η παρούσα Πολιτική Cookies εξηγεί πώς η <?php echo e(APP_NAME); ?> χρησιμοποιεί cookies και παρόμοιες
        τεχνολογίες σύμφωνα με τον GDPR και την Οδηγία ePrivacy.</p>
    </div>

    <section id="what-are-cookies">
      <h2>Τι είναι τα Cookies;</h2>
      <p>Τα cookies είναι μικρά αρχεία κειμένου που αποθηκεύονται στη συσκευή σας όταν επισκέπτεστε έναν ιστότοπο.
        Χρησιμοποιούνται για:</p>
      <ul>
        <li>Τη σωστή λειτουργία του ιστότοπου</li>
        <li>Την αποθήκευση προτιμήσεων</li>
        <li>Τη συλλογή στατιστικών στοιχείων</li>
        <li>Την παροχή εξατομικευμένου περιεχομένου</li>
      </ul>
    </section>

    <section id="cookie-types">
      <h2>Κατηγορίες Cookies</h2>

      <div class="cookie-category">
        <h3>🔒 Απαραίτητα Cookies (Πάντα ενεργά)</h3>
        <p>Αυτά τα cookies είναι απαραίτητα για τη βασική λειτουργία του ιστότοπου και δεν απαιτούν συγκατάθεση.</p>
        <table class="cookie-table">
          <thead>
            <tr>
              <th>Όνομα</th>
              <th>Σκοπός</th>
              <th>Διάρκεια</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><code>PHPSESSID</code></td>
              <td>Διατήρηση συνεδρίας χρήστη</td>
              <td>Μέχρι κλείσιμο browser</td>
            </tr>
            <tr>
              <td><code>csrf_token</code></td>
              <td>Προστασία από επιθέσεις CSRF</td>
              <td>Μέχρι κλείσιμο browser</td>
            </tr>
            <tr>
              <td><code>cookie_consent</code></td>
              <td>Αποθήκευση επιλογών cookies</td>
              <td>1 έτος</td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="cookie-category">
        <h3>📊 Αναλυτικά Cookies (Απαιτούν συγκατάθεση)</h3>
        <p>Μας βοηθούν να κατανοήσουμε πώς χρησιμοποιείται ο ιστότοπος.</p>
        <table class="cookie-table">
          <thead>
            <tr>
              <th>Όνομα</th>
              <th>Πάροχος</th>
              <th>Σκοπός</th>
              <th>Διάρκεια</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td><code>_ga</code></td>
              <td>Google Analytics</td>
              <td>Διάκριση επισκεπτών</td>
              <td>2 έτη</td>
            </tr>
            <tr>
              <td><code>_gid</code></td>
              <td>Google Analytics</td>
              <td>Διάκριση επισκεπτών</td>
              <td>24 ώρες</td>
            </tr>
          </tbody>
        </table>
        <p class="small muted">Σημείωση: Τα αναλυτικά cookies ενεργοποιούνται μόνο αν δώσετε συγκατάθεση.</p>
      </div>

      <div class="cookie-category">
        <h3>📧 Cookies Μάρκετινγκ (Απαιτούν συγκατάθεση)</h3>
        <p>Χρησιμοποιούνται για εξατομικευμένες ενημερώσεις και διαφημίσεις.</p>
        <p class="small muted">Αυτή τη στιγμή η πλατφόρμα δεν χρησιμοποιεί cookies μάρκετινγκ.</p>
      </div>
    </section>

    <section id="manage-cookies">
      <h2>Διαχείριση Cookies</h2>

      <h3>Μέσω του Banner Συγκατάθεσης</h3>
      <p>Κατά την πρώτη επίσκεψη, θα δείτε ένα banner που σας επιτρέπει να:</p>
      <ul>
        <li>Αποδεχτείτε όλα τα cookies</li>
        <li>Αποδεχτείτε μόνο τα απαραίτητα</li>
        <li>Προσαρμόσετε τις επιλογές σας</li>
      </ul>

      <div class="action-box">
        <button onclick="window.resetCookieConsent && window.resetCookieConsent()" class="btn">
          🍪 Αλλαγή Ρυθμίσεων Cookies
        </button>
      </div>

      <h3>Μέσω του Browser</h3>
      <p>Μπορείτε επίσης να διαχειριστείτε τα cookies από τις ρυθμίσεις του προγράμματος περιήγησης:</p>
      <ul>
        <li><a href="https://support.google.com/chrome/answer/95647" target="_blank" rel="noopener">Google Chrome</a>
        </li>
        <li><a href="https://support.mozilla.org/el/kb/enable-and-disable-cookies-website-preferences" target="_blank"
            rel="noopener">Mozilla Firefox</a></li>
        <li><a href="https://support.apple.com/el-gr/guide/safari/sfri11471/mac" target="_blank"
            rel="noopener">Safari</a></li>
        <li><a href="https://support.microsoft.com/el-gr/help/17442/windows-internet-explorer-delete-manage-cookies"
            target="_blank" rel="noopener">Microsoft Edge</a></li>
      </ul>

      <div class="notice warn">
        <strong>Προσοχή:</strong> Αν απενεργοποιήσετε τα απαραίτητα cookies, ορισμένες λειτουργίες του ιστότοπου (π.χ.
        σύνδεση) δεν θα λειτουργούν σωστά.
      </div>
    </section>

    <section id="third-party">
      <h2>Cookies Τρίτων</h2>
      <p>Ορισμένα cookies τοποθετούνται από τρίτους παρόχους υπηρεσιών:</p>
      <ul>
        <li><strong>Google Analytics:</strong> Για στατιστικά χρήσης (<a href="https://policies.google.com/privacy"
            target="_blank" rel="noopener">Πολιτική Απορρήτου</a>)</li>
      </ul>
      <p>Δεν έχουμε έλεγχο επί των cookies τρίτων. Παρακαλούμε ανατρέξτε στις πολιτικές τους για περισσότερες
        πληροφορίες.</p>
    </section>

    <section id="updates">
      <h2>Ενημερώσεις</h2>
      <p>Μπορεί να ενημερώνουμε αυτήν την πολιτική. Οι αλλαγές θα δημοσιεύονται σε αυτήν τη σελίδα με νέα ημερομηνία.
      </p>
    </section>

    <section id="contact">
      <h2>Επικοινωνία</h2>
      <div class="contact-box">
        <p>Για ερωτήσεις σχετικά με τα cookies:</p>
        <p><strong>Email:</strong> privacy@givehope.gr</p>
      </div>
    </section>

    <div class="legal-footer">
      <p>Δείτε επίσης: <a href="<?php echo BASE_URL; ?>/privacy.php">Πολιτική Απορρήτου</a></p>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>