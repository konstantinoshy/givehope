<?php
// Πώς λειτουργεί — οδηγός χρήσης
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Πώς Λειτουργεί';
$pageDescription = 'Μάθετε πώς λειτουργεί το GiveHope — από τη δημιουργία εράνου μέχρι τη δωρεά, βήμα-βήμα.';

require_once __DIR__ . '/includes/header.php';
?>

<style>
    .hiw-hero {
        position: relative;
        background-color: var(--charcoal);
        background-image: radial-gradient(circle at 20% 60%, rgba(46, 64, 54, 0.5) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(204, 88, 51, 0.2) 0%, transparent 50%);
        color: var(--cream);
        padding: 160px 24px 80px;
        text-align: center;
        margin: -90px -24px 0;
        overflow: hidden;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .hiw-hero::before {
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

    .hiw-hero-content {
        position: relative;
        z-index: 2;
        max-width: 700px;
        margin: 0 auto;
    }

    .hiw-hero h1 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: clamp(2.2rem, 5vw, 3.2rem);
        font-weight: 800;
        margin: 0 0 16px;
        letter-spacing: -0.03em;
    }

    .hiw-hero p {
        font-family: 'Outfit', sans-serif;
        font-size: 1.2rem;
        color: rgba(242, 240, 233, 0.8);
        margin: 0;
        line-height: 1.6;
    }

    .hiw-container {
        max-width: 900px;
        margin: 0 auto;
        padding: 64px 24px;
    }

    /* Tab switcher */
    .hiw-tabs {
        display: flex;
        justify-content: center;
        gap: 8px;
        margin-bottom: 48px;
        background: var(--bg);
        border-radius: var(--radius-pill);
        padding: 6px;
        border: 1px solid var(--border);
        max-width: 500px;
        margin-left: auto;
        margin-right: auto;
    }

    .hiw-tab {
        flex: 1;
        padding: 12px 24px;
        border: none;
        background: transparent;
        border-radius: var(--radius-pill);
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 15px;
        font-weight: 600;
        color: var(--text-secondary);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .hiw-tab.active {
        background: var(--moss);
        color: var(--cream);
        box-shadow: 0 2px 8px rgba(46, 64, 54, 0.2);
    }

    .hiw-tab:hover:not(.active) {
        color: var(--text);
        background: rgba(46, 64, 54, 0.05);
    }

    .hiw-panel {
        display: none;
    }

    .hiw-panel.active {
        display: block;
    }

    /* Steps */
    .hiw-steps {
        position: relative;
    }

    .hiw-steps::before {
        content: '';
        position: absolute;
        left: 32px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: linear-gradient(to bottom, var(--moss), var(--clay));
        border-radius: 2px;
    }

    .hiw-step {
        position: relative;
        padding-left: 80px;
        padding-bottom: 48px;
    }

    .hiw-step:last-child {
        padding-bottom: 0;
    }

    .hiw-step-number {
        position: absolute;
        left: 14px;
        top: 0;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Outfit', sans-serif;
        font-size: 16px;
        font-weight: 800;
        z-index: 2;
    }

    .hiw-step-number.moss {
        background: var(--moss);
        color: var(--cream);
    }

    .hiw-step-number.clay {
        background: var(--clay);
        color: var(--cream);
    }

    .hiw-step-card {
        background: var(--bg);
        border-radius: var(--radius-xl);
        padding: 28px;
        border: 1px solid var(--border);
        box-shadow: var(--shadow-sm);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .hiw-step-card:hover {
        box-shadow: var(--shadow-md);
        transform: translateY(-4px);
    }

    .hiw-step-card h3 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 20px;
        font-weight: 700;
        margin: 0 0 8px;
        color: var(--text);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .hiw-step-card h3 svg {
        flex-shrink: 0;
        color: var(--moss);
    }

    .hiw-step-card p {
        font-size: 15px;
        color: var(--text-secondary);
        margin: 0 0 16px;
        line-height: 1.7;
    }

    .hiw-step-card p:last-child {
        margin-bottom: 0;
    }

    .hiw-tip {
        background: rgba(46, 64, 54, 0.06);
        border-radius: var(--radius-lg);
        padding: 14px 16px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        font-size: 14px;
        color: var(--moss);
        margin-top: 16px;
    }

    .hiw-tip svg {
        flex-shrink: 0;
        margin-top: 2px;
    }

    /* FAQ */
    .hiw-faq {
        margin-top: 64px;
    }

    .hiw-faq h2 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 28px;
        font-weight: 800;
        color: var(--text);
        text-align: center;
        margin: 0 0 32px;
    }

    .faq-item {
        background: var(--bg);
        border-radius: var(--radius-xl);
        border: 1px solid var(--border-light);
        margin-bottom: 12px;
        overflow: hidden;
        box-shadow: var(--shadow-sm);
    }

    .faq-question {
        width: 100%;
        padding: 20px 24px;
        background: none;
        border: none;
        cursor: pointer;
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 16px;
        font-weight: 600;
        color: var(--text);
        text-align: left;
        transition: background 0.2s;
    }

    .faq-question:hover {
        background: rgba(46, 64, 54, 0.03);
    }

    .faq-question svg {
        flex-shrink: 0;
        transition: transform 0.3s ease;
        color: var(--text-muted);
    }

    .faq-item.open .faq-question svg {
        transform: rotate(180deg);
    }

    .faq-answer {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.4s ease, padding 0.3s ease;
    }

    .faq-item.open .faq-answer {
        max-height: 300px;
        padding: 0 24px 20px;
    }

    .faq-answer p {
        font-size: 15px;
        color: var(--text-secondary);
        margin: 0;
        line-height: 1.7;
    }

    /* CTA */
    .hiw-cta {
        background-color: var(--charcoal);
        background-image: linear-gradient(135deg, rgba(46, 64, 54, 0.4) 0%, rgba(204, 88, 51, 0.15) 100%);
        border-radius: var(--radius-2xl);
        padding: 64px 32px;
        text-align: center;
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: var(--cream);
        margin-top: 64px;
        position: relative;
        overflow: hidden;
    }

    .hiw-cta::before {
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

    .hiw-cta h2 {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 28px;
        font-weight: 800;
        margin: 0 0 12px;
        position: relative;
        z-index: 2;
    }

    .hiw-cta p {
        font-size: 17px;
        color: rgba(242, 240, 233, 0.8);
        margin: 0 0 28px;
        position: relative;
        z-index: 2;
    }

    .hiw-cta-buttons {
        display: flex;
        gap: 16px;
        justify-content: center;
        flex-wrap: wrap;
        position: relative;
        z-index: 2;
    }

    .hiw-cta-buttons .btn {
        padding: 16px 32px;
        font-size: 16px;
        border-radius: var(--radius-pill);
    }

    .hiw-cta-buttons .btn.primary {
        background: var(--clay);
        color: var(--cream);
        border: none;
        box-shadow: 0 4px 15px rgba(204, 88, 51, 0.3);
    }

    .hiw-cta-buttons .btn.primary:hover {
        background: #B34D2D;
        transform: translateY(-2px);
    }

    .hiw-cta-buttons .btn:not(.primary) {
        background: rgba(255, 255, 255, 0.1);
        color: var(--cream);
        border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
    }

    .hiw-cta-buttons .btn:not(.primary):hover {
        background: rgba(255, 255, 255, 0.15);
    }

    @media (max-width: 768px) {
        .hiw-hero {
            padding: 100px 24px 60px;
        }

        .hiw-hero h1 {
            font-size: 2rem;
        }

        .hiw-container {
            padding: 40px 16px;
        }

        .hiw-steps::before {
            left: 22px;
        }

        .hiw-step {
            padding-left: 64px;
        }

        .hiw-step-number {
            left: 4px;
        }

        .hiw-cta {
            padding: 48px 24px;
        }

        .hiw-cta-buttons {
            flex-direction: column;
        }

        .hiw-cta-buttons .btn {
            width: 100%;
        }
    }
</style>

<div class="hiw-hero">
    <div class="hiw-hero-content">
        <h1>Πώς λειτουργεί το
            <?php echo e(APP_NAME); ?>
        </h1>
        <p>Ένας απλός, βήμα-βήμα οδηγός για δωρητές και δημιουργούς εράνων</p>
    </div>
</div>

<div class="hiw-container">

    <!-- Tabs -->
    <div class="hiw-tabs">
        <button class="hiw-tab active" onclick="switchTab('donor')">Θέλω να δωρίσω</button>
        <button class="hiw-tab" onclick="switchTab('creator')">Θέλω να ξεκινήσω έρανο</button>
    </div>

    <!-- ============ DONOR PANEL ============ -->
    <div id="panel-donor" class="hiw-panel active">
        <div class="hiw-steps">

            <div class="hiw-step">
                <div class="hiw-step-number moss">1</div>
                <div class="hiw-step-card">
                    <h3>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="11" cy="11" r="8" />
                            <line x1="21" y1="21" x2="16.65" y2="16.65" />
                        </svg>
                        Βρείτε έναν έρανο
                    </h3>
                    <p>Περιηγηθείτε στη σελίδα <strong>Έρανοι</strong> ή χρησιμοποιήστε τη μπάρα αναζήτησης για να
                        βρείτε έναν σκοπό που σας αγγίζει. Μπορείτε να φιλτράρετε ανά κατηγορία (Υγεία, Εκπαίδευση,
                        Περιβάλλον κ.ά.).</p>
                    <div class="hiw-tip">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" />
                            <line x1="12" y1="16" x2="12" y2="12" />
                            <line x1="12" y1="8" x2="12.01" y2="8" />
                        </svg>
                        <span>Κάθε έρανος στο GiveHope ελέγχεται και εγκρίνεται από την ομάδα μας πριν
                            δημοσιευτεί.</span>
                    </div>
                </div>
            </div>

            <div class="hiw-step">
                <div class="hiw-step-number moss">2</div>
                <div class="hiw-step-card">
                    <h3>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                            <circle cx="12" cy="12" r="3" />
                        </svg>
                        Δείτε τις λεπτομέρειες
                    </h3>
                    <p>Κάντε κλικ σε έναν έρανο για να δείτε την πλήρη ιστορία του, τα έγγραφα επαλήθευσης, τον στόχο
                        και τη μέχρι τώρα πρόοδο. Δείτε επίσης τη λίστα δωρητών και τα μηνύματα υποστήριξης.</p>
                </div>
            </div>

            <div class="hiw-step">
                <div class="hiw-step-number moss">3</div>
                <div class="hiw-step-card">
                    <h3>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path
                                d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z" />
                        </svg>
                        Κάντε τη δωρεά σας
                    </h3>
                    <p>Πατήστε <strong>"Κάνε Δωρεά"</strong> και επιλέξτε ένα ποσό ή πληκτρολογήστε το δικό σας.
                        Μπορείτε να προσθέσετε ένα μήνυμα υποστήριξης. Η δωρεά μπορεί να γίνει <strong>ανώνυμα</strong>
                        αν το επιθυμείτε.</p>
                    <div class="hiw-tip">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        </svg>
                        <span>Δεν χρειάζεται λογαριασμός για να κάνετε δωρεά — μόνο ένα email (προαιρετικό).</span>
                    </div>
                </div>
            </div>

            <div class="hiw-step">
                <div class="hiw-step-number moss">4</div>
                <div class="hiw-step-card">
                    <h3>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
                        Παρακολουθήστε τον αντίκτυπο
                    </h3>
                    <p>Μετά τη δωρεά, μπορείτε να παρακολουθείτε την πρόοδο του εράνου σε πραγματικό χρόνο. Η μπάρα
                        προόδου ενημερώνεται αυτόματα, ώστε να βλέπετε πόσο κοντά είναι ο στόχος.</p>
                </div>
            </div>

        </div>
    </div>

    <!-- ============ CREATOR PANEL ============ -->
    <div id="panel-creator" class="hiw-panel">
        <div class="hiw-steps">

            <div class="hiw-step">
                <div class="hiw-step-number clay">1</div>
                <div class="hiw-step-card">
                    <h3>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" />
                            <circle cx="8.5" cy="7" r="4" />
                            <line x1="20" y1="8" x2="20" y2="14" />
                            <line x1="23" y1="11" x2="17" y2="11" />
                        </svg>
                        Δημιουργήστε λογαριασμό
                    </h3>
                    <p>Εγγραφείτε δωρεάν ως <strong>ιδιώτης</strong> ή ως <strong>οργανισμός</strong>. Χρειάζεται μόνο
                        ένα email, ένα όνομα και έναν κωδικό πρόσβασης. Η εγγραφή παίρνει λιγότερο από 1 λεπτό.</p>
                </div>
            </div>

            <div class="hiw-step">
                <div class="hiw-step-number clay">2</div>
                <div class="hiw-step-card">
                    <h3>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                        Δημιουργήστε τον έρανο
                    </h3>
                    <p>Πατήστε <strong>"Ξεκίνα Έρανο"</strong> και συμπληρώστε τα στοιχεία: τίτλο, ιστορία, κατηγορία,
                        στόχο δωρεάς και μια εικόνα. Όσο πιο αναλυτική η ιστορία, τόσο περισσότερες δωρεές θα λάβετε.
                    </p>
                    <div class="hiw-tip">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z" />
                            <line x1="12" y1="16" x2="12" y2="12" />
                            <line x1="12" y1="8" x2="12.01" y2="8" />
                        </svg>
                        <span>Προσθέστε μια προσωπική φωτογραφία και ένα συγκινητικό κείμενο. Οι έρανοι με εικόνα
                            λαμβάνουν κατά μέσο όρο 3x περισσότερες δωρεές.</span>
                    </div>
                </div>
            </div>

            <div class="hiw-step">
                <div class="hiw-step-number clay">3</div>
                <div class="hiw-step-card">
                    <h3>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <circle cx="18" cy="5" r="3" />
                            <circle cx="6" cy="12" r="3" />
                            <circle cx="18" cy="19" r="3" />
                            <line x1="8.59" y1="13.51" x2="15.42" y2="17.49" />
                            <line x1="15.41" y1="6.51" x2="8.59" y2="10.49" />
                        </svg>
                        Μοιραστείτε τον
                    </h3>
                    <p>Μόλις εγκριθεί ο έρανός σας, μοιραστείτε τον σύνδεσμο στα <strong>κοινωνικά δίκτυά</strong> σας
                        (Facebook, Instagram, WhatsApp) και στείλτε τον σε φίλους και οικογένεια. Κάθε κοινοποίηση
                        μετράει!</p>
                </div>
            </div>

            <div class="hiw-step">
                <div class="hiw-step-number clay">4</div>
                <div class="hiw-step-card">
                    <h3>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23" />
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                        Λάβετε τις δωρεές
                    </h3>
                    <p>Παρακολουθήστε τις δωρεές σε πραγματικό χρόνο μέσα από τον πίνακα ελέγχου σας. Κάθε δωρεά
                        καταγράφεται αυτόματα και μπορείτε να δείτε αναλυτικά στατιστικά για τον έρανό σας.</p>
                    <div class="hiw-tip">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        </svg>
                        <span>Η πλατφόρμα δεν χρεώνει κρυφές προμήθειες. Το ποσό που συγκεντρώνεται πηγαίνει εξ'
                            ολοκλήρου στον δικαιούχο.</span>
                    </div>
                </div>
            </div>

            <div class="hiw-step">
                <div class="hiw-step-number clay">5</div>
                <div class="hiw-step-card">
                    <h3>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                            <polyline points="22 4 12 14.01 9 11.01" />
                        </svg>
                        Ευχαριστήστε τους δωρητές
                    </h3>
                    <p>Ενημερώστε τους δωρητές σας για την πρόοδο. Ένα σύντομο μήνυμα ευχαριστίας ή μια ενημέρωση
                        κατάστασης ενθαρρύνει τους ανθρώπους να μοιραστούν τον έρανο σε ακόμα περισσότερους.</p>
                </div>
            </div>

        </div>
    </div>

    <!-- FAQ Section -->
    <div class="hiw-faq">
        <h2>Συχνές ερωτήσεις</h2>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                Χρειάζεται να δημιουργήσω λογαριασμό για να κάνω δωρεά;
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9" />
                </svg>
            </button>
            <div class="faq-answer">
                <p>Όχι! Μπορείτε να κάνετε δωρεά χωρίς λογαριασμό. Αν θέλετε, μπορείτε να δώσετε το email σας για
                    επιβεβαίωση, αλλά δεν είναι υποχρεωτικό. Οι ανώνυμες δωρεές υποστηρίζονται πλήρως.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                Πώς ξέρω ότι τα χρήματά μου φτάνουν στον σωστό σκοπό;
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9" />
                </svg>
            </button>
            <div class="faq-answer">
                <p>Κάθε έρανος ελέγχεται και εγκρίνεται από την ομάδα μας πριν δημοσιευτεί. Η πλατφόρμα προσφέρει επίσης
                    την <a href="<?php echo BASE_URL; ?>/giving-guarantee.php"><strong>GiveHope Giving
                            Guarantee</strong></a>, η οποία εγγυάται την επιστροφή χρημάτων σε περίπτωση κατάχρησης.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                Υπάρχουν χρεώσεις ή προμήθειες;
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9" />
                </svg>
            </button>
            <div class="faq-answer">
                <p>Η δημιουργία εράνου είναι εντελώς δωρεάν. Δεν υπάρχουν κρυφές χρεώσεις ή προμήθειες πλατφόρμας. Το
                    100% του ποσού πηγαίνει στον δικαιούχο.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                Πόσο χρόνο παίρνει η έγκριση ενός εράνου;
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9" />
                </svg>
            </button>
            <div class="faq-answer">
                <p>Η ομάδα μας ελέγχει κάθε νέο έρανο εντός 24–48 ωρών. Αν ο οργανισμός σας είναι ήδη επαληθευμένος, ο
                    έρανος εγκρίνεται αυτόματα.</p>
            </div>
        </div>

        <div class="faq-item">
            <button class="faq-question" onclick="toggleFaq(this)">
                Τα δεδομένα μου είναι ασφαλή;
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9" />
                </svg>
            </button>
            <div class="faq-answer">
                <p>Απολύτως. Η πλατφόρμα συμμορφώνεται πλήρως με τον Ευρωπαϊκό Κανονισμό GDPR. Οι κωδικοί
                    κρυπτογραφούνται, η σύνδεση είναι ασφαλής (HTTPS) και μπορείτε ανά πάσα στιγμή να ασκήσετε τα <a
                        href="<?php echo BASE_URL; ?>/gdpr-request.php">δικαιώματά σας</a>.</p>
            </div>
        </div>
    </div>

    <!-- CTA -->
    <div class="hiw-cta">
        <h2>Έτοιμοι να κάνετε τη διαφορά;</h2>
        <p>Ξεκινήστε σήμερα — είτε ως δωρητής, είτε δημιουργώντας τον δικό σας έρανο.</p>
        <div class="hiw-cta-buttons">
            <a href="<?php echo BASE_URL; ?>/explore.php" class="btn primary">Εξερευνήστε Εράνους</a>
            <a href="<?php echo BASE_URL; ?>/campaign-create.php" class="btn">Ξεκινήστε Έρανο</a>
        </div>
    </div>

</div>

<script>
    function switchTab(tab) {
        document.querySelectorAll('.hiw-tab').forEach(function (t) { t.classList.remove('active'); });
        document.querySelectorAll('.hiw-panel').forEach(function (p) { p.classList.remove('active'); });

        document.getElementById('panel-' + tab).classList.add('active');
        event.currentTarget.classList.add('active');
    }

    function toggleFaq(btn) {
        var item = btn.closest('.faq-item');
        var wasOpen = item.classList.contains('open');

        // Κλείσιμο όλων
        document.querySelectorAll('.faq-item').forEach(function (i) { i.classList.remove('open'); });

        // Άνοιγμα επιλεγμένου
        if (!wasOpen) {
            item.classList.add('open');
        }
    }
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>