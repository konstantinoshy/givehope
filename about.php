<?php
// Σχετικά με εμάς
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Σχετικά με εμάς';
$pageDescription = 'Μάθετε περισσότερα για το GiveHope — την πλατφόρμα δωρεών που φέρνει κοντά ανθρώπους που χρειάζονται βοήθεια με αυτούς που θέλουν να προσφέρουν.';

require_once __DIR__ . '/includes/header.php';
?>

<style>
    .about-hero {
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

    .about-hero::before {
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

    .about-hero-content {
        position: relative;
        z-index: 2;
        max-width: 800px;
        margin: 0 auto;
    }

    .about-hero-icon {
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
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
    }

    .about-hero-icon img {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        object-fit: cover;
    }

    .about-hero h1 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: clamp(2.5rem, 5vw, 3.5rem);
        font-weight: 800;
        margin: 0 0 16px;
        letter-spacing: -0.03em;
    }

    .about-hero p {
        font-family: 'Outfit', sans-serif;
        font-size: 1.25rem;
        color: rgba(242, 240, 233, 0.8);
        margin: 0;
        line-height: 1.6;
    }

    .about-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 64px 24px;
    }

    .about-intro {
        text-align: center;
        margin-bottom: 64px;
    }

    .about-intro h2 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 32px;
        font-weight: 800;
        color: var(--text);
        margin: 0 0 16px;
        letter-spacing: -0.02em;
    }

    .about-intro p {
        font-size: 18px;
        color: var(--text-secondary);
        max-width: 650px;
        margin: 0 auto;
        line-height: 1.7;
    }

    /* Αξίες */
    .values-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 64px;
    }

    .value-card {
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

    .value-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 30px rgba(46, 64, 54, 0.08);
        border-color: rgba(46, 64, 54, 0.2);
    }

    .value-icon {
        width: 64px;
        height: 64px;
        background: rgba(46, 64, 54, 0.08);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
        color: var(--moss);
    }

    .value-card h3 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 20px;
        font-weight: 700;
        margin: 0 0 12px;
        color: var(--text);
    }

    .value-card p {
        font-size: 15px;
        color: var(--text-secondary);
        margin: 0;
        line-height: 1.6;
    }

    /* Στατιστικά */
    .stats-section {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        margin-bottom: 64px;
    }

    .stat-card {
        background: var(--bg);
        border-radius: var(--radius-xl);
        padding: 32px 24px;
        text-align: center;
        border: 1px solid var(--border-light);
        box-shadow: var(--shadow-sm);
    }

    .stat-number {
        font-family: 'Outfit', sans-serif;
        font-size: 42px;
        font-weight: 800;
        color: var(--moss);
        letter-spacing: -0.03em;
        line-height: 1;
        margin-bottom: 8px;
    }

    .stat-label {
        font-size: 15px;
        color: var(--text-secondary);
        font-weight: 500;
    }

    /* Story section */
    .story-section {
        background: var(--bg);
        border-radius: var(--radius-2xl);
        padding: 56px;
        border: 1px solid var(--border);
        margin-bottom: 64px;
        box-shadow: var(--shadow-sm);
    }

    .story-section h2 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 28px;
        font-weight: 800;
        margin: 0 0 24px;
        display: flex;
        align-items: center;
        gap: 16px;
        color: var(--text);
    }

    .story-section p {
        font-size: 16px;
        color: var(--text-secondary);
        line-height: 1.8;
        margin: 0 0 16px;
    }

    .story-section p:last-child {
        margin-bottom: 0;
    }

    /* Features */
    .about-features {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 24px;
        margin-bottom: 64px;
    }

    .about-feature-card {
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

    .about-feature-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-4px);
    }

    .about-feature-icon {
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

    .about-feature-content h3 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 18px;
        font-weight: 700;
        margin: 0 0 8px;
        color: var(--text);
    }

    .about-feature-content p {
        font-size: 14px;
        color: var(--text-secondary);
        margin: 0;
        line-height: 1.6;
    }

    /* CTA */
    .about-cta {
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

    .about-cta::before {
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

    .about-cta h2 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 32px;
        font-weight: 800;
        margin: 0 0 16px;
        position: relative;
        z-index: 2;
    }

    .about-cta p {
        font-size: 18px;
        color: rgba(242, 240, 233, 0.8);
        margin: 0 0 32px;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
        position: relative;
        z-index: 2;
    }

    .about-cta-buttons {
        display: flex;
        gap: 16px;
        justify-content: center;
        flex-wrap: wrap;
        position: relative;
        z-index: 2;
    }

    .about-cta-buttons .btn {
        padding: 16px 32px;
        font-size: 16px;
        border-radius: var(--radius-pill);
    }

    .about-cta-buttons .btn.primary {
        background: var(--clay);
        color: var(--cream);
        border: none;
        box-shadow: 0 4px 15px rgba(204, 88, 51, 0.3);
    }

    .about-cta-buttons .btn.primary:hover {
        background: #B34D2D;
        transform: translateY(-2px);
    }

    .about-cta-buttons .btn:not(.primary) {
        background: rgba(255, 255, 255, 0.1);
        color: var(--cream);
        border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
    }

    .about-cta-buttons .btn:not(.primary):hover {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.3);
    }

    @media (max-width: 768px) {
        .about-hero {
            padding: 100px 24px 60px;
        }

        .about-hero h1 {
            font-size: 2rem;
        }

        .about-container {
            padding: 40px 16px;
        }

        .stats-section {
            grid-template-columns: 1fr;
        }

        .story-section {
            padding: 32px 24px;
            border-radius: var(--radius-xl);
        }

        .about-cta {
            padding: 48px 24px;
        }

        .about-cta-buttons {
            flex-direction: column;
        }

        .about-cta-buttons .btn {
            width: 100%;
        }
    }
</style>

<?php
$pdo = db();
$campaignCount = (int) $pdo->query("SELECT COUNT(*) FROM campaigns WHERE status = 'approved'")->fetchColumn();
$donTotal = (int) $pdo->query("SELECT COALESCE(SUM(amount), 0) FROM donations")->fetchColumn();
$donorCount = (int) $pdo->query("SELECT COUNT(*) FROM donations")->fetchColumn();
?>

<div class="about-hero">
    <div class="about-hero-content">
        <div class="about-hero-icon">
            <img src="<?php echo BASE_URL; ?>/public/images/logo.svg" alt="GiveHope">
        </div>
        <h1>Σχετικά με το
            <?php echo e(APP_NAME); ?>
        </h1>
        <p>Φέρνουμε κοντά αυτούς που χρειάζονται βοήθεια με αυτούς που θέλουν να προσφέρουν</p>
    </div>
</div>

<div class="about-container">

    <!-- Εισαγωγή -->
    <div class="about-intro">
        <h2>Η αποστολή μας</h2>
        <p>Πιστεύουμε ότι η τεχνολογία μπορεί να ενισχύσει την αλληλεγγύη. Το GiveHope δημιουργήθηκε με σκοπό να παρέχει
            μια αξιόπιστη, διαφανή και φιλική πλατφόρμα δωρεών για οργανισμούς και ιδιώτες στην Ελλάδα.</p>
    </div>

    <!-- Στατιστικά -->
    <div class="stats-section">
        <div class="stat-card">
            <div class="stat-number">
                <?php echo number_format($campaignCount, 0, ',', '.'); ?>
            </div>
            <div class="stat-label">Ενεργοί Έρανοι</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">
                <?php echo money_eur($donTotal); ?>
            </div>
            <div class="stat-label">Συνολικές Δωρεές</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">
                <?php echo number_format($donorCount, 0, ',', '.'); ?>
            </div>
            <div class="stat-label">Δωρητές</div>
        </div>
    </div>

    <!-- Οι αξίες μας -->
    <h2 style="text-align: center; margin-bottom: 24px;">Οι αξίες μας</h2>
    <p
        style="text-align: center; color: var(--text-secondary); margin-bottom: 32px; max-width: 600px; margin-left: auto; margin-right: auto;">
        Κάθε απόφασή μας καθοδηγείται από τρεις θεμελιώδεις αξίες.
    </p>

    <div class="values-grid">
        <div class="value-card">
            <div class="value-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
            </div>
            <h3>Διαφάνεια</h3>
            <p>Κάθε δωρεά, κάθε έρανος και κάθε συναλλαγή είναι πλήρως ορατά. Πιστεύουμε ότι η εμπιστοσύνη χτίζεται μέσω
                της διαφάνειας.</p>
        </div>
        <div class="value-card">
            <div class="value-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                </svg>
            </div>
            <h3>Ασφάλεια</h3>
            <p>Η προστασία των δωρητών και των δικαιούχων είναι προτεραιότητά μας. Κάθε έρανος ελέγχεται πριν
                δημοσιευτεί.</p>
        </div>
        <div class="value-card">
            <div class="value-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="9" cy="7" r="4" />
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87" />
                    <path d="M16 3.13a4 4 0 0 1 0 7.75" />
                </svg>
            </div>
            <h3>Κοινότητα</h3>
            <p>Φέρνουμε τους ανθρώπους κοντά. Το GiveHope δεν είναι απλώς πλατφόρμα — είναι μια κοινότητα αλληλεγγύης.
            </p>
        </div>
    </div>

    <!-- Η ιστορία μας -->
    <div class="story-section">
        <h2>
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
            </svg>
            Η ιστορία μας
        </h2>
        <p>
            Το GiveHope γεννήθηκε από μια απλή πεποίθηση: ότι η τεχνολογία μπορεί να γίνει γέφυρα αλληλεγγύης. Σε μια
            εποχή που οι ανάγκες αυξάνονται, θέλαμε να δημιουργήσουμε μια πλατφόρμα που κάνει τη δωρεά εύκολη, ασφαλή
            και διαφανή.
        </p>
        <p>
            Η πλατφόρμα μας σχεδιάστηκε ώστε να εξυπηρετεί τόσο μεμονωμένους πολίτες που αντιμετωπίζουν δυσκολίες, όσο
            και οργανισμούς που εργάζονται καθημερινά για το κοινό καλό. Από ιατρικά έξοδα μέχρι σπουδές, από φιλόδοξα
            κοινοτικά έργα μέχρι φυσικές καταστροφές — κάθε έρανος στο GiveHope έχει τη δυνατότητα να αλλάξει ζωές.
        </p>
        <p>
            Με σεβασμό στην ιδιωτικότητα (GDPR), πλήρη συμμόρφωση με την ευρωπαϊκή νομοθεσία και ένα σύστημα εγγύησης
            δωρεών, δεσμευόμαστε να προσφέρουμε μια εμπειρία που εμπνέει εμπιστοσύνη σε κάθε βήμα.
        </p>
    </div>

    <!-- Τι μας κάνει διαφορετικούς -->
    <h2 style="text-align: center; margin-bottom: 24px;">Τι μας κάνει διαφορετικούς</h2>
    <div class="about-features">
        <div class="about-feature-card">
            <div class="about-feature-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12" />
                </svg>
            </div>
            <div class="about-feature-content">
                <h3>Δεδομένα σε Πραγματικό Χρόνο</h3>
                <p>Παρακολουθήστε κάθε δωρεά, κάθε έρανο και κάθε εξέλιξη ζωντανά στην πλατφόρμα μας.</p>
            </div>
        </div>
        <div class="about-feature-card">
            <div class="about-feature-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                </svg>
            </div>
            <div class="about-feature-content">
                <h3>Ασφαλείς Συναλλαγές</h3>
                <p>Κάθε δωρεά προστατεύεται με σύγχρονα πρωτόκολλα ασφαλείας και κρυπτογράφηση.</p>
            </div>
        </div>
        <div class="about-feature-card">
            <div class="about-feature-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                    <polyline points="22 4 12 14.01 9 11.01" />
                </svg>
            </div>
            <div class="about-feature-content">
                <h3>Εγγύηση Δωρεών</h3>
                <p>Η GiveHope Giving Guarantee προστατεύει κάθε δωρεά σας με εγγύηση επιστροφής χρημάτων.</p>
            </div>
        </div>
        <div class="about-feature-card">
            <div class="about-feature-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                    <circle cx="8.5" cy="7" r="4" />
                    <polyline points="17 11 19 13 23 9" />
                </svg>
            </div>
            <div class="about-feature-content">
                <h3>Επαληθευμένοι Οργανισμοί</h3>
                <p>Κάθε οργανισμός στην πλατφόρμα ελέγχεται και επαληθεύεται από την ομάδα μας.</p>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="about-cta">
        <h2>Γίνετε μέρος της αλλαγής</h2>
        <p>Ξεκινήστε τον δικό σας έρανο ή στηρίξτε κάποιον που χρειάζεται τη βοήθειά σας σήμερα.</p>
        <div class="about-cta-buttons">
            <a href="<?php echo BASE_URL; ?>/campaign-create.php" class="btn primary">Ξεκινήστε Έρανο</a>
            <a href="<?php echo BASE_URL; ?>/explore.php" class="btn">Εξερευνήστε Εράνους</a>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>