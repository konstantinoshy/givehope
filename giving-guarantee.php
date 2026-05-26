<?php
// GiveHope Giving Guarantee — εγγύηση προστασίας δωρητών
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'GiveHope Giving Guarantee';
$pageDescription = 'Η εγγύηση GiveHope προσφέρει στους δωρητές ασφάλεια. Κάντε δωρεά με σιγουριά, γνωρίζοντας ότι προστατεύεστε από την εγγύηση επιστροφής χρημάτων.';

require_once __DIR__ . '/includes/header.php';
?>

<style>
    .guarantee-hero {
        position: relative;
        background-color: var(--charcoal);
        background-image: radial-gradient(circle at 15% 50%, rgba(46, 64, 54, 0.4) 0%, transparent 50%),
            radial-gradient(circle at 85% 30%, rgba(204, 88, 51, 0.15) 0%, transparent 50%);
        color: var(--cream);
        padding: 160px 24px 80px;
        text-align: center;
        margin: -90px -24px 0;
        overflow: hidden;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .guarantee-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
        opacity: 0.03;
        pointer-events: none;
        z-index: 1;
    }

    .guarantee-hero-content {
        position: relative;
        z-index: 2;
        max-width: 800px;
        margin: 0 auto;
    }

    .guarantee-hero-icon {
        width: 100px;
        height: 100px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--clay);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    }

    .guarantee-hero h1 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: clamp(2.5rem, 5vw, 3.5rem);
        font-weight: 800;
        margin: 0 0 16px;
        letter-spacing: -0.03em;
    }

    .guarantee-hero p {
        font-family: 'Outfit', sans-serif;
        font-size: 1.25rem;
        color: rgba(242, 240, 233, 0.8);
        margin: 0;
        line-height: 1.6;
    }

    .guarantee-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 64px 24px;
    }

    .guarantee-intro {
        text-align: center;
        margin-bottom: 64px;
    }

    .guarantee-intro h2 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 32px;
        font-weight: 800;
        color: var(--text);
        margin: 0 0 16px;
        letter-spacing: -0.02em;
    }

    .guarantee-intro p {
        font-size: 18px;
        color: var(--text-secondary);
        max-width: 600px;
        margin: 0 auto;
        line-height: 1.7;
    }

    .how-it-works {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 64px;
    }

    .how-it-works-card {
        background: var(--cream);
        border-radius: var(--radius-xl);
        padding: 32px 24px;
        text-align: center;
        box-shadow: var(--shadow-sm);
        transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        border: 1px solid var(--border);
        position: relative;
        overflow: hidden;
    }

    .how-it-works-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(46, 64, 54, 0.08);
        border-color: rgba(46, 64, 54, 0.2);
    }

    .step-number {
        width: 48px;
        height: 48px;
        background: var(--moss);
        color: var(--cream);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Outfit', sans-serif;
        font-size: 20px;
        font-weight: 800;
        margin: 0 auto 24px;
        box-shadow: 0 4px 12px rgba(46, 64, 54, 0.2);
    }

    .how-it-works-card h3 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 20px;
        font-weight: 700;
        margin: 0 0 12px;
        color: var(--text);
    }

    .how-it-works-card p {
        font-size: 15px;
        color: var(--text-secondary);
        margin: 0;
        line-height: 1.6;
    }

    .guarantee-features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 24px;
        margin-bottom: 64px;
    }

    .feature-card {
        background: var(--bg);
        border-radius: var(--radius-xl);
        padding: 28px;
        border: 1px solid var(--border-light);
        display: flex;
        gap: 20px;
        align-items: flex-start;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: var(--shadow-sm);
    }

    .feature-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-4px);
    }

    .feature-icon {
        width: 56px;
        height: 56px;
        background: rgba(204, 88, 51, 0.1);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        color: var(--clay);
    }

    .feature-icon svg {
        width: 28px;
        height: 28px;
        stroke: currentColor;
    }

    .feature-content h3 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 18px;
        font-weight: 700;
        margin: 0 0 8px;
        color: var(--text);
    }

    .feature-content p {
        font-size: 14px;
        color: var(--text-secondary);
        margin: 0;
        line-height: 1.6;
    }

    .policy-section {
        background: var(--bg);
        border-radius: var(--radius-2xl);
        padding: 56px;
        border: 1px solid var(--border);
        margin-bottom: 64px;
        box-shadow: var(--shadow-sm);
    }

    .policy-section h2 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 28px;
        font-weight: 800;
        margin: 0 0 32px;
        display: flex;
        align-items: center;
        gap: 16px;
        color: var(--text);
    }

    .policy-section h2 svg {
        stroke: var(--moss);
    }

    .policy-section h3 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 20px;
        font-weight: 700;
        margin: 40px 0 16px;
        color: var(--text);
    }

    .policy-section h3:first-of-type {
        margin-top: 0;
    }

    .policy-section p,
    .policy-section li {
        font-size: 16px;
        color: var(--text-secondary);
        line-height: 1.8;
    }

    .policy-section ul {
        padding-left: 24px;
        margin: 16px 0;
    }

    .policy-section li {
        margin-bottom: 12px;
    }

    .exclusions-list {
        background: rgba(204, 88, 51, 0.05);
        /* very light clay */
        border-radius: var(--radius-lg);
        padding: 24px 32px;
        border-left: 4px solid var(--clay);
        margin: 24px 0;
    }

    .exclusions-list li {
        color: var(--text);
    }

    .cta-section {
        background-color: var(--charcoal);
        background-image: linear-gradient(135deg, rgba(46, 64, 54, 0.4) 0%, rgba(204, 88, 51, 0.15) 100%);
        border-radius: var(--radius-2xl);
        padding: 64px 32px;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--cream);
        position: relative;
        overflow: hidden;
    }

    .cta-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg viewBox='0 0 200 200' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noiseFilter'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noiseFilter)'/%3E%3C/svg%3E");
        opacity: 0.03;
        pointer-events: none;
    }

    .cta-section h2 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 32px;
        font-weight: 800;
        margin: 0 0 16px;
        position: relative;
        z-index: 2;
    }

    .cta-section p {
        font-size: 18px;
        color: rgba(242, 240, 233, 0.8);
        margin: 0 0 32px;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
        position: relative;
        z-index: 2;
    }

    .cta-buttons {
        display: flex;
        gap: 16px;
        justify-content: center;
        flex-wrap: wrap;
        position: relative;
        z-index: 2;
    }

    .cta-buttons .btn {
        padding: 16px 32px;
        font-size: 16px;
        border-radius: var(--radius-pill);
    }

    .cta-buttons .btn.primary {
        background: var(--clay);
        color: var(--cream);
        border: none;
        box-shadow: 0 4px 15px rgba(204, 88, 51, 0.3);
    }

    .cta-buttons .btn.primary:hover {
        background: #B34D2D;
        transform: translateY(-2px);
    }

    .cta-buttons .btn:not(.primary) {
        background: rgba(255, 255, 255, 0.1);
        color: var(--cream);
        border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
    }

    .cta-buttons .btn:not(.primary):hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.3);
    }

    .effective-date {
        text-align: center;
        padding: 24px;
        color: var(--text-muted);
        font-size: 14px;
        font-family: 'Outfit', sans-serif;
    }

    @media (max-width: 768px) {
        .guarantee-hero {
            padding: 100px 24px 60px;
        }

        .guarantee-hero h1 {
            font-size: 2rem;
        }

        .guarantee-container {
            padding: 40px 16px;
        }

        .policy-section {
            padding: 32px 24px;
            border-radius: var(--radius-xl);
        }

        .cta-section {
            padding: 48px 24px;
        }

        .cta-buttons {
            flex-direction: column;
        }

        .cta-buttons .btn {
            width: 100%;
        }
    }
</style>

<div class="guarantee-hero">
    <div class="guarantee-hero-content">
        <div class="guarantee-hero-icon">
            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                <polyline points="9 12 12 15 16 10" stroke-width="2.5" />
            </svg>
        </div>
        <h1>GiveHope Giving Guarantee</h1>
        <p>Προστατεύουμε τη δωρεά σας με την εγγύησή μας επιστροφής χρημάτων</p>
    </div>
</div>

<div class="guarantee-container">
    <div class="guarantee-intro">
        <h2>Κάνετε κάθε φορά τον κόσμο καλύτερο</h2>
        <p>Πιστεύουμε ότι είναι ευθύνη μας να προστατεύσουμε την καλοσύνη σας — προστατεύοντας τη δωρεά σας.</p>
    </div>

    <!-- Πώς Λειτουργεί -->
    <h2 style="text-align: center; margin-bottom: 24px;">Είμαστε εδώ για να βοηθήσουμε</h2>
    <p style="text-align: center; color: var(--text-secondary); margin-bottom: 32px;">
        Ευτυχώς, η απάτη στο GiveHope είναι σπάνια. Η ομάδα Εμπιστοσύνης & Ασφάλειας παρακολουθεί προληπτικά
        την πλατφόρμα μας και ερευνά όλα τα αναφερόμενα ζητήματα.
    </p>

    <div class="how-it-works">
        <div class="how-it-works-card">
            <div class="step-number">1</div>
            <h3>Υποβολή Αναφοράς</h3>
            <p>Ενημερώστε μας γιατί πιστεύετε ότι η δωρεά σας χρησιμοποιείται λανθασμένα.</p>
        </div>
        <div class="how-it-works-card">
            <div class="step-number">2</div>
            <h3>Έλεγχος από Ειδικούς</h3>
            <p>Η ομάδα Εμπιστοσύνης & Ασφάλειας θα ερευνήσει την αναφορά σας.</p>
        </div>
        <div class="how-it-works-card">
            <div class="step-number">3</div>
            <h3>Λήψη Επιστροφής</h3>
            <p>Οι επιστροφές χρημάτων θα επεξεργαστούν σε 3-10 εργάσιμες ημέρες.</p>
        </div>
    </div>

    <!-- Χαρακτηριστικά Εγγύησης -->
    <div class="guarantee-features">
        <div class="feature-card">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                </svg>
            </div>
            <div class="feature-content">
                <h3>Πλήρης Επιστροφή Χρημάτων</h3>
                <p>Εγγυόμαστε πλήρη επιστροφή για έως και ένα έτος σε περίπτωση απάτης.</p>
            </div>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10" />
                    <polyline points="12 6 12 12 16 14" />
                </svg>
            </div>
            <div class="feature-content">
                <h3>24/7 Παρακολούθηση</h3>
                <p>Η ομάδα μας παρακολουθεί συνεχώς την πλατφόρμα για ύποπτη δραστηριότητα.</p>
            </div>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                    <circle cx="12" cy="7" r="4" />
                </svg>
            </div>
            <div class="feature-content">
                <h3>Επαλήθευση Δικαιούχων</h3>
                <p>Διασφαλίζουμε ότι τα χρήματα φτάνουν στους πραγματικούς δικαιούχους.</p>
            </div>
        </div>
        <div class="feature-card">
            <div class="feature-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                    <polyline points="14 2 14 8 20 8" />
                    <line x1="16" y1="13" x2="8" y2="13" />
                    <line x1="16" y1="17" x2="8" y2="17" />
                </svg>
            </div>
            <div class="feature-content">
                <h3>Διαφανείς Διαδικασίες</h3>
                <p>Σαφείς πολιτικές και διαδικασίες για την προστασία όλων.</p>
            </div>
        </div>
    </div>

    <!-- Πλήρης Πολιτική -->
    <div class="policy-section">
        <h2>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                <polyline points="14 2 14 8 20 8" />
            </svg>
            Πολιτική GiveHope Giving Guarantee
        </h2>

        <h3>1. Εισαγωγή & Σκοπός</h3>
        <p>
            Το GiveHope δεσμεύεται να διασφαλίζει την εμπιστοσύνη όλων όσων χρησιμοποιούν την πλατφόρμα μας.
            Σύμφωνα με τους παρακάτω όρους, αυτή η Πολιτική Εγγύησης προστατεύει:
        </p>
        <ul>
            <li><strong>Δωρητές</strong>, διασφαλίζοντας ότι οι δωρεές τους χρησιμοποιούνται για τον δηλωμένο σκοπό ή
                φτάνουν
                στον προβλεπόμενο οργανισμό.</li>
            <li><strong>Δικαιούχους</strong>, διασφαλίζοντας ότι λαμβάνουν τα χρήματα που συγκεντρώθηκαν για αυτούς.
            </li>
        </ul>

        <h3>2. Εγγύηση για Δωρητές</h3>
        <p><strong>Δωρεές σε Ιδιώτες:</strong> Αν κάνετε δωρεά σε έρανο για ιδιώτη στο GiveHope, προστατεύεστε από κακή
            χρήση των χρημάτων σας βάσει του δηλωμένου σκοπού του εράνου ή αποτυχία παράδοσης των χρημάτων στον
            δικαιούχο.</p>
        <p><strong>Δωρεές σε Οργανισμούς:</strong> Αν κάνετε δωρεά σε οργανισμό, εγγυόμαστε ότι η δωρεά σας θα φτάσει
            στον
            προβλεπόμενο οργανισμό.</p>

        <h3>3. Εγγύηση για Δικαιούχους</h3>
        <p>
            Είτε είστε ιδιώτης, οργανισμός ή άλλη οντότητα, αν είστε ο μόνος δηλωμένος, προβλεπόμενος παραλήπτης ενός
            εράνου,
            το GiveHope θα διασφαλίσει ότι θα λάβετε τα χρήματα που κατευθύνονται σε εσάς.
        </p>

        <h3>4. Εξαιρέσεις</h3>
        <p>Οι ακόλουθες περιπτώσεις εξαιρούνται από αυτήν την Πολιτική:</p>
        <div class="exclusions-list">
            <ul>
                <li>Αίτημα που υποβάλλεται περισσότερο από ένα (1) έτος μετά την πραγματοποίηση της δωρεάς</li>
                <li>Δωρεά που πραγματοποιήθηκε εκτός της Πλατφόρμας</li>
                <li>Χρήματα που παραδόθηκαν κατάλληλα σε κάποιον άλλο με τη γραπτή έγκρισή σας</li>
                <li>Δωρεά στον δικό σας έρανο</li>
                <li>Αίτημα επιστροφής για το οποίο έχει ξεκινήσει chargeback</li>
                <li>Οποιαδήποτε επιπόλαια, διπλότυπα ή υπερβολικά αιτήματα</li>
                <li>Το GiveHope κρίνει ότι έχετε παραβιάσει τους Όρους Χρήσης μας</li>
            </ul>
        </div>

        <h3>5. Υποβολή Αναφοράς</h3>
        <p>
            Μπορείτε να υποβάλετε αναφορά στο πλαίσιο της GiveHope Giving Guarantee μέσω της
            <a href="<?php echo BASE_URL; ?>/contact.php">φόρμας επικοινωνίας</a> ή του κουμπιού "Αναφορά εράνου"
            σε κάθε σελίδα εράνου.
        </p>

        <h3>6. Έρευνα Αναφοράς</h3>
        <p>
            Μόλις λάβουμε την αναφορά σας, η ομάδα Εμπιστοσύνης & Ασφάλειας θα την εξετάσει. Μπορεί να επικοινωνήσουμε
            μαζί
            σας
            για πρόσθετες πληροφορίες. Αν κρίνουμε ότι υπήρξε κακή χρήση, μπορείτε να λάβετε επιστροφή του ποσού που
            δωρήσατε.
        </p>

        <h3>7. Καμία Ασφάλεια ή Εγγύηση</h3>
        <p>
            Αυτή η Πολιτική δεν αποτελεί ασφαλιστήριο συμβόλαιο. Τα δικαιώματα που παρέχονται βάσει αυτής της Πολιτικής
            δεν μπορούν να μεταβιβαστούν ή να εκχωρηθούν.
        </p>

        <h3>8. Τροποποιήσεις & Τερματισμός</h3>
        <p>
            Διατηρούμε το δικαίωμα να τροποποιήσουμε ή να τερματίσουμε αυτήν την Πολιτική ανά πάσα στιγμή μετά από
            ειδοποίηση στην πλατφόρμα μας.
        </p>

        <h3>9. Σχέση με τους Όρους Χρήσης</h3>
        <p>
            Αυτή η Πολιτική αποτελεί μέρος των <a href="<?php echo BASE_URL; ?>/terms.php">Όρων Χρήσης</a> του GiveHope.
            Σε περίπτωση σύγκρουσης, ισχύουν οι διατάξεις αυτής της Πολιτικής για θέματα που αφορούν την Εγγύηση.
        </p>
    </div>

    <!-- Call to Action -->
    <div class="cta-section">
        <h2>Έχετε ερωτήσεις;</h2>
        <p>Η ομάδα υποστήριξης είναι διαθέσιμη για να σας βοηθήσει με οποιαδήποτε απορία.</p>
        <div class="cta-buttons">
            <a href="<?php echo BASE_URL; ?>/contact.php" class="btn primary">Επικοινωνήστε μαζί μας</a>
            <a href="<?php echo BASE_URL; ?>/explore.php" class="btn">Εξερευνήστε Εράνους</a>
        </div>
    </div>

    <p class="effective-date">
        Ημερομηνία Ισχύος:
        <?php echo date('d/m/Y'); ?>
    </p>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>