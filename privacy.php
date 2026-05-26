<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';
?>

<div style="max-width: 800px; margin: 120px auto 60px; padding: 0 24px;">
  <div class="card legal-document">
    <h1>Πολιτική Απορρήτου</h1>
    <p class="legal-meta">Τελευταία ενημέρωση: <?php echo date('d/m/Y'); ?></p>

    <div class="legal-intro">
      <p>Η παρούσα Πολιτική Απορρήτου περιγράφει πώς η <?php echo e(APP_NAME); ?> 
        συλλέγει, χρησιμοποιεί και προστατεύει τα προσωπικά σας δεδομένα σύμφωνα με τον Γενικό Κανονισμό Προστασίας
        Δεδομένων (GDPR - Κανονισμός ΕΕ 2016/679).</p>
    </div>

    <nav class="legal-toc">
      <h3>Περιεχόμενα</h3>
      <ol>
        <li><a href="#controller">Υπεύθυνος Επεξεργασίας</a></li>
        <li><a href="#data-collected">Δεδομένα που Συλλέγουμε</a></li>
        <li><a href="#legal-basis">Νομική Βάση Επεξεργασίας</a></li>
        <li><a href="#purposes">Σκοποί Επεξεργασίας</a></li>
        <li><a href="#retention">Χρόνος Διατήρησης</a></li>
        <li><a href="#sharing">Κοινοποίηση Δεδομένων</a></li>
        <li><a href="#cookies">Cookies</a></li>
        <li><a href="#rights">Τα Δικαιώματά σας</a></li>
        <li><a href="#security">Ασφάλεια Δεδομένων</a></li>
        <li><a href="#children">Προστασία Ανηλίκων</a></li>
        <li><a href="#changes">Αλλαγές στην Πολιτική</a></li>
        <li><a href="#contact">Επικοινωνία</a></li>
      </ol>
    </nav>

    <section id="controller">
      <h2>1. Υπεύθυνος Επεξεργασίας</h2>
      <p>Υπεύθυνος επεξεργασίας των προσωπικών σας δεδομένων είναι η <?php echo e(APP_NAME); ?>.</p>
      <div class="contact-box">
        <p><strong>Επικοινωνία για θέματα προσωπικών δεδομένων:</strong></p>
        <p>Email: privacy@givehope.gr</p>
      </div>
    </section>

    <section id="data-collected">
      <h2>2. Δεδομένα που Συλλέγουμε</h2>

      <h3>2.1 Δεδομένα που παρέχετε άμεσα</h3>
      <table class="data-table">
        <thead>
          <tr>
            <th>Κατηγορία</th>
            <th>Δεδομένα</th>
            <th>Σκοπός</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><strong>Εγγραφή Χρήστη</strong></td>
            <td>Ονοματεπώνυμο, email, τηλέφωνο (προαιρετικό), κωδικός πρόσβασης (κρυπτογραφημένος)</td>
            <td>Δημιουργία και διαχείριση λογαριασμού</td>
          </tr>
          <tr>
            <td><strong>Εγγραφή Οργανισμού</strong></td>
            <td>Επωνυμία, email, τηλέφωνο, ιστοσελίδα, περιγραφή, κωδικός πρόσβασης (κρυπτογραφημένος)</td>
            <td>Δημιουργία και διαχείριση λογαριασμού οργανισμού</td>
          </tr>
          <tr>
            <td><strong>Δωρεά</strong></td>
            <td>Όνομα δωρητή (προαιρετικό), email (προαιρετικό), ποσό, μήνυμα</td>
            <td>Καταγραφή δωρεάς, επικοινωνία</td>
          </tr>
          <tr>
            <td><strong>Επικοινωνία</strong></td>
            <td>Ονοματεπώνυμο, email, θέμα, μήνυμα</td>
            <td>Απάντηση σε ερωτήματα</td>
          </tr>
          <tr>
            <td><strong>Έρανοι</strong></td>
            <td>Τίτλος, περιγραφή, ιστορία, εικόνες, έγγραφα επαλήθευσης</td>
            <td>Δημοσίευση και διαχείριση εράνων</td>
          </tr>
        </tbody>
      </table>

      <h3>2.2 Δεδομένα που συλλέγονται αυτόματα</h3>
      <ul>
        <li><strong>Διεύθυνση IP:</strong> Για λόγους ασφαλείας και πρόληψης κατάχρησης</li>
        <li><strong>Cookies:</strong> Για τη λειτουργία της ιστοσελίδας (βλ. <a href="#cookies">Πολιτική Cookies</a>)
        </li>
        <li><strong>Δεδομένα περιήγησης:</strong> Τύπος προγράμματος περιήγησης, συσκευή, γλώσσα</li>
      </ul>
    </section>

    <section id="legal-basis">
      <h2>3. Νομική Βάση Επεξεργασίας</h2>
      <p>Επεξεργαζόμαστε τα δεδομένα σας βάσει των ακόλουθων νομικών βάσεων του GDPR:</p>
      <ul>
        <li><strong>Συγκατάθεση (Άρθρο 6(1)(a)):</strong> Όταν έχετε δώσει ρητή συγκατάθεση για συγκεκριμένους σκοπούς
        </li>
        <li><strong>Εκτέλεση Σύμβασης (Άρθρο 6(1)(b)):</strong> Για την παροχή των υπηρεσιών μας</li>
        <li><strong>Έννομο Συμφέρον (Άρθρο 6(1)(f)):</strong> Για την ασφάλεια της πλατφόρμας και την πρόληψη απάτης
        </li>
        <li><strong>Νομική Υποχρέωση (Άρθρο 6(1)(c)):</strong> Για συμμόρφωση με νομικές απαιτήσεις</li>
      </ul>
    </section>

    <section id="purposes">
      <h2>4. Σκοποί Επεξεργασίας</h2>
      <p>Χρησιμοποιούμε τα δεδομένα σας για:</p>
      <ul>
        <li>Δημιουργία και διαχείριση του λογαριασμού σας</li>
        <li>Επεξεργασία και καταγραφή δωρεών</li>
        <li>Επικοινωνία σχετικά με τους εράνους σας</li>
        <li>Επαλήθευση εράνων και πρόληψη απάτης</li>
        <li>Βελτίωση των υπηρεσιών μας</li>
        <li>Συμμόρφωση με νομικές υποχρεώσεις</li>
        <li>Αποστολή ενημερώσεων (μόνο με τη συγκατάθεσή σας)</li>
      </ul>
    </section>

    <section id="retention">
      <h2>5. Χρόνος Διατήρησης Δεδομένων</h2>
      <table class="data-table">
        <thead>
          <tr>
            <th>Κατηγορία Δεδομένων</th>
            <th>Χρόνος Διατήρησης</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>Δεδομένα λογαριασμού</td>
            <td>Μέχρι τη διαγραφή του λογαριασμού + 30 ημέρες</td>
          </tr>
          <tr>
            <td>Δεδομένα δωρεών</td>
            <td>10 έτη (φορολογικοί λόγοι)</td>
          </tr>
          <tr>
            <td>Μηνύματα επικοινωνίας</td>
            <td>2 έτη</td>
          </tr>
          <tr>
            <td>Αρχεία καταγραφής (logs)</td>
            <td>1 έτος</td>
          </tr>
          <tr>
            <td>Cookies συγκατάθεσης</td>
            <td>1 έτος</td>
          </tr>
        </tbody>
      </table>
    </section>

    <section id="sharing">
      <h2>6. Κοινοποίηση Δεδομένων</h2>
      <p>Δεν πουλάμε ούτε νοικιάζουμε τα προσωπικά σας δεδομένα. Μπορεί να κοινοποιήσουμε δεδομένα:</p>
      <ul>
        <li><strong>Με οργανισμούς που λαμβάνουν δωρεές:</strong> Τα στοιχεία που επιλέξατε να μοιραστείτε</li>
        <li><strong>Με παρόχους υπηρεσιών:</strong> Που μας βοηθούν να λειτουργούμε την πλατφόρμα (hosting, email)</li>
        <li><strong>Με αρχές:</strong> Όταν απαιτείται από το νόμο</li>
      </ul>
      <p><strong>Διεθνείς μεταφορές:</strong> Τα δεδομένα σας αποθηκεύονται σε διακομιστές εντός της Ευρωπαϊκής Ένωσης.
      </p>
    </section>

    <section id="cookies">
      <h2>7. Cookies</h2>
      <p>Χρησιμοποιούμε cookies για τη λειτουργία της ιστοσελίδας. Για λεπτομέρειες, δείτε την <a
          href="<?php echo BASE_URL; ?>/cookies.php">Πολιτική Cookies</a>.</p>
      <h3>Κατηγορίες Cookies:</h3>
      <ul>
        <li><strong>Απαραίτητα:</strong> Για τη βασική λειτουργία (σύνδεση, ασφάλεια)</li>
        <li><strong>Αναλυτικά:</strong> Για στατιστικά χρήσης (μόνο με συγκατάθεση)</li>
        <li><strong>Μάρκετινγκ:</strong> Για εξατομικευμένες ενημερώσεις (μόνο με συγκατάθεση)</li>
      </ul>
    </section>

    <section id="rights">
      <h2>8. Τα Δικαιώματά σας (GDPR)</h2>
      <p>Έχετε τα ακόλουθα δικαιώματα σύμφωνα με τον GDPR:</p>

      <div class="rights-grid">
        <div class="right-item">
          <h4><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--moss)" stroke-width="2"
              style="vertical-align: -3px; margin-right: 4px;">
              <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
              <polyline points="14 2 14 8 20 8" />
              <line x1="16" y1="13" x2="8" y2="13" />
              <line x1="16" y1="17" x2="8" y2="17" />
            </svg> Δικαίωμα Πρόσβασης</h4>
          <p>Να λάβετε αντίγραφο των δεδομένων σας (Άρθρο 15)</p>
        </div>
        <div class="right-item">
          <h4><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--moss)" stroke-width="2"
              style="vertical-align: -3px; margin-right: 4px;">
              <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
              <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
            </svg> Δικαίωμα Διόρθωσης</h4>
          <p>Να διορθώσετε ανακριβή δεδομένα (Άρθρο 16)</p>
        </div>
        <div class="right-item">
          <h4><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--moss)" stroke-width="2"
              style="vertical-align: -3px; margin-right: 4px;">
              <polyline points="3 6 5 6 21 6" />
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
            </svg> Δικαίωμα Διαγραφής</h4>
          <p>Να ζητήσετε διαγραφή των δεδομένων σας (Άρθρο 17)</p>
        </div>
        <div class="right-item">
          <h4><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--moss)" stroke-width="2"
              style="vertical-align: -3px; margin-right: 4px;">
              <circle cx="12" cy="12" r="10" />
              <line x1="10" y1="15" x2="10" y2="9" />
              <line x1="14" y1="15" x2="14" y2="9" />
            </svg> Δικαίωμα Περιορισμού</h4>
          <p>Να περιορίσετε την επεξεργασία (Άρθρο 18)</p>
        </div>
        <div class="right-item">
          <h4><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--moss)" stroke-width="2"
              style="vertical-align: -3px; margin-right: 4px;">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4" />
              <polyline points="7 10 12 15 17 10" />
              <line x1="12" y1="15" x2="12" y2="3" />
            </svg> Δικαίωμα Φορητότητας</h4>
          <p>Να λάβετε τα δεδομένα σας σε αναγνώσιμη μορφή (Άρθρο 20)</p>
        </div>
        <div class="right-item">
          <h4><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="var(--moss)" stroke-width="2"
              style="vertical-align: -3px; margin-right: 4px;">
              <circle cx="12" cy="12" r="10" />
              <line x1="4.93" y1="4.93" x2="19.07" y2="19.07" />
            </svg> Δικαίωμα Εναντίωσης</h4>
          <p>Να αντιταχθείτε στην επεξεργασία (Άρθρο 21)</p>
        </div>
      </div>

      <div class="action-box">
        <h4>Άσκηση Δικαιωμάτων</h4>
        <p>Για να ασκήσετε τα δικαιώματά σας:</p>
        <ul>
          <li>Συνδεθείτε στο λογαριασμό σας και επισκεφθείτε τις <strong>Ρυθμίσεις → Τα Δεδομένα μου</strong></li>
          <li>Στείλτε email στο <strong>privacy@givehope.gr</strong></li>
          <li>Υποβάλετε αίτημα μέσω της <a href="<?php echo BASE_URL; ?>/gdpr-request.php">φόρμας αιτημάτων GDPR</a>
          </li>
        </ul>
        <p>Θα απαντήσουμε εντός <strong>30 ημερών</strong> από τη λήψη του αιτήματος.</p>
      </div>

      <h3>Δικαίωμα Καταγγελίας</h3>
      <p>Έχετε το δικαίωμα να υποβάλετε καταγγελία στην <strong>Αρχή Προστασίας Δεδομένων Προσωπικού Χαρακτήρα
          (ΑΠΔΠΧ)</strong>:</p>
      <ul>
        <li>Ιστοσελίδα: <a href="https://www.dpa.gr" target="_blank" rel="noopener">www.dpa.gr</a></li>
        <li>Τηλέφωνο: +30 210 6475600</li>
        <li>Email: contact@dpa.gr</li>
      </ul>
    </section>

    <section id="security">
      <h2>9. Ασφάλεια Δεδομένων</h2>
      <p>Εφαρμόζουμε τεχνικά και οργανωτικά μέτρα για την προστασία των δεδομένων σας:</p>
      <ul>
        <li>Κρυπτογράφηση κωδικών πρόσβασης (bcrypt)</li>
        <li>Ασφαλής σύνδεση (HTTPS)</li>
        <li>Προστασία από CSRF επιθέσεις</li>
        <li>Τακτική ενημέρωση λογισμικού</li>
        <li>Περιορισμένη πρόσβαση στα δεδομένα</li>
      </ul>
    </section>

    <section id="children">
      <h2>10. Προστασία Ανηλίκων</h2>
      <p>Η πλατφόρμα μας δεν απευθύνεται σε άτομα κάτω των 16 ετών. Δεν συλλέγουμε εν γνώσει μας δεδομένα από ανηλίκους.
        Αν ανακαλύψουμε τέτοια δεδομένα, θα τα διαγράψουμε αμέσως.</p>
    </section>

    <section id="changes">
      <h2>11. Αλλαγές στην Πολιτική</h2>
      <p>Μπορεί να ενημερώνουμε περιοδικά αυτήν την πολιτική. Σε περίπτωση ουσιαστικών αλλαγών, θα σας ειδοποιήσουμε
        μέσω email ή/και με εμφανή ανακοίνωση στην ιστοσελίδα.</p>
    </section>

    <section id="contact">
      <h2>12. Επικοινωνία</h2>
      <div class="contact-box">
        <p>Για ερωτήσεις σχετικά με την προστασία των δεδομένων σας:</p>
        <p><strong>Email:</strong> privacy@givehope.gr</p>
        <p><strong>Φόρμα επικοινωνίας:</strong> <a href="<?php echo BASE_URL; ?>/contact.php">Επικοινωνία</a></p>
      </div>
    </section>

    <div class="legal-footer">
      <p><strong>Κανονισμός (ΕΕ) 2016/679</strong> - Γενικός Κανονισμός Προστασίας Δεδομένων</p>
      <p><a href="https://eur-lex.europa.eu/eli/reg/2016/679/oj" target="_blank" rel="noopener">Πλήρες κείμενο GDPR</a>
      </p>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>