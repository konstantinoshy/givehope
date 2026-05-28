<?php
// Σελίδα δωρεάς

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$pdo = db();

// Υποβολή δωρεάς (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $campaignId = (int) ($_POST['campaign_id'] ?? 0);
    $donorName = trim($_POST['donor_name'] ?? '');
    $donorEmail = trim($_POST['donor_email'] ?? '');
    $amount = (int) ($_POST['amount'] ?? 0);
    $message = trim($_POST['message'] ?? '');
    $isAnonymous = isset($_POST['is_anonymous']) ? 1 : 0;
    $privacyConsent = isset($_POST['privacy_consent']) ? 1 : 0;
    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;

    if (!$privacyConsent) {
        redirect(BASE_URL . "/donate.php?id=" . $campaignId . "&error=consent");
    }

    if ($campaignId <= 0) {
        redirect(BASE_URL . "/explore.php");
    }

    if (!$isAnonymous) {
        if ($donorName === '') {
            redirect(BASE_URL . "/donate.php?id=" . $campaignId . "&error=donor");
        }
        if (!filter_var($donorEmail, FILTER_VALIDATE_EMAIL)) {
            redirect(BASE_URL . "/donate.php?id=" . $campaignId . "&error=email");
        }
    } elseif ($donorEmail !== '' && !filter_var($donorEmail, FILTER_VALIDATE_EMAIL)) {
        redirect(BASE_URL . "/donate.php?id=" . $campaignId . "&error=email");
    }

    if ($amount < 1)
        $amount = 1;
    if ($amount > 10000) {
        redirect(BASE_URL . "/donate.php?id=" . $campaignId . "&error=amount");
    }
    if (mb_strlen($message, 'UTF-8') > 280)
        $message = mb_substr($message, 0, 280, 'UTF-8');

    $pdo->beginTransaction();
    try {
        // Κλείδωμα εράνου μέσα στο transaction για αποφυγή race condition
        $stmt = $pdo->prepare("SELECT * FROM campaigns WHERE id = :id AND status = 'approved' FOR UPDATE");
        $stmt->execute([':id' => $campaignId]);
        $campaign = $stmt->fetch();

        if (!$campaign) {
            $pdo->rollBack();
            redirect(BASE_URL . "/explore.php");
        }

        $ins = $pdo->prepare("
            INSERT INTO donations (campaign_id, donor_name, donor_email, amount, message, is_anonymous, privacy_consent, ip_address)
            VALUES (:cid, :dn, :de, :amt, :msg, :anon, :consent, :ip)
        ");
        $ins->execute([
            ':cid' => $campaignId,
            ':dn' => ($donorName === '' ? null : $donorName),
            ':de' => ($donorEmail === '' ? null : $donorEmail),
            ':amt' => $amount,
            ':msg' => ($message === '' ? null : $message),
            ':anon' => $isAnonymous,
            ':consent' => $privacyConsent,
            ':ip' => $ipAddress,
        ]);

        if ($campaign['type'] === 'money' && $amount > 0) {
            $upd = $pdo->prepare("UPDATE campaigns SET current_amount = current_amount + :amt WHERE id = :id");
            $upd->execute([':amt' => $amount, ':id' => $campaignId]);
        }

        $donationId = (int) $pdo->lastInsertId();
        $pdo->commit();

        // Καταγραφή δωρεάς στο αρχείο επεξεργασίας (Άρθρο 30 GDPR)
        logDataProcessing($pdo, 'donation', $donationId, 'create', 'user', 0, 'Νέα δωρεά €' . $amount . ' στον έρανο #' . $campaignId);

    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    redirect(BASE_URL . "/campaign.php?id=" . $campaignId . "&donated=1");
    exit;
}

// GET αίτημα - Εμφάνιση φόρμας
$id = (int) ($_GET['id'] ?? 0);
$error = $_GET['error'] ?? '';

if ($id <= 0) {
    redirect(BASE_URL . "/explore.php");
}

$stmt = $pdo->prepare("
    SELECT c.*, cat.name AS category_name,
           u.name AS user_name, o.name AS org_name
    FROM campaigns c
    JOIN categories cat ON cat.id = c.category_id
    LEFT JOIN users u ON u.id = c.user_id
    LEFT JOIN organizations o ON o.id = c.org_id
    WHERE c.id = :id AND c.status = 'approved'
");
$stmt->execute([':id' => $id]);
$campaign = $stmt->fetch();

if (!$campaign) {
    redirect(BASE_URL . "/explore.php");
}

$isOrg = $campaign['org_id'] !== null;
$creatorName = $isOrg ? $campaign['org_name'] : $campaign['user_name'];
$pct = min(100, (int) round(($campaign['current_amount'] / max(1, $campaign['target_amount'])) * 100));

$defaultImage = 'https://images.unsplash.com/photo-1532629345422-7515f3d16bb6?w=800&q=80';
$image = $campaign['image_url'] ?: $defaultImage;

$pageTitle = 'Δωρεά - ' . $campaign['title'];
require_once __DIR__ . '/includes/header.php';

// Prefill: logged-in profile → empty (donate.php uses GET redirects on error, so no POST fallback needed)
$prefillDonorName  = $currentUser['name']  ?? $currentOrg['name']  ?? '';
$prefillDonorEmail = $currentUser['email'] ?? $currentOrg['email'] ?? '';
?>

<div class="donate-page">
    <a href="<?php echo BASE_URL; ?>/campaign.php?id=<?php echo $id; ?>" class="back-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M19 12H5M12 19l-7-7 7-7" />
        </svg>
        Πίσω στον έρανο
    </a>

    <?php if ($error === 'consent'): ?>
        <div class="notice error" style="margin-bottom: 20px;">
            Πρέπει να αποδεχτείτε την Πολιτική Απορρήτου για να συνεχίσετε.
        </div>
    <?php elseif ($error === 'donor'): ?>
        <div class="notice error" style="margin-bottom: 20px;">
            Συμπληρώστε το όνομά σας (ή επιλέξτε ανώνυμη δωρεά).
        </div>
    <?php elseif ($error === 'email'): ?>
        <div class="notice error" style="margin-bottom: 20px;">
            Εισάγετε έγκυρο email.
        </div>
    <?php endif; ?>

    <div class="donate-grid">
        <!-- Campaign Summary (Left) -->
        <div class="campaign-summary">
            <div class="card">
                <img src="<?php echo e($image); ?>" alt="<?php echo e($campaign['title']); ?>"
                    class="campaign-summary-image">
                <h2><?php echo e($campaign['title']); ?></h2>
                <p class="creator">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                        style="vertical-align: -2px;">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" />
                        <circle cx="12" cy="7" r="4" />
                    </svg>
                    <?php echo e($creatorName); ?>
                </p>

                <div style="font-size: 24px; font-weight: 800; color: var(--primary); margin-bottom: 4px;">
                    <?php echo money_eur((int) $campaign['current_amount']); ?>
                </div>
                <p class="muted" style="margin: 0 0 12px; font-size: 14px;">
                    συγκεντρώθηκαν από <?php echo money_eur((int) $campaign['target_amount']); ?>
                </p>
                <div class="progress-bar" style="height: 8px; margin-bottom: 8px;">
                    <div class="fill" style="width: <?php echo $pct; ?>%;"></div>
                </div>
                <p class="small muted" style="margin: 0;"><?php echo $pct; ?>% του στόχου</p>
            </div>
        </div>

        <!-- Donation Form (Right) -->
        <div class="donate-form-card">
            <h1>Κάντε τη δωρεά σας</h1>

            <form method="post" action="<?php echo BASE_URL; ?>/donate.php" class="js-validate">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="campaign_id" value="<?php echo $id; ?>">

                <label style="font-weight: 600; font-size: 15px; margin-bottom: 12px; display: block;">Επιλέξτε
                    ποσό</label>

                <!-- Amount Buttons -->
                <div class="donation-amounts-row">
                    <?php
                    $amounts = [10, 25, 50, 100, 150, 200];
                    $suggestedAmount = 50;
                    foreach ($amounts as $amt):
                        ?>
                        <div class="amount-btn-wrapper <?php echo $amt === $suggestedAmount ? 'has-badge' : ''; ?>">
                            <button type="button"
                                class="amount-btn <?php echo $amt === $suggestedAmount ? 'suggested' : ''; ?>"
                                onclick="selectAmount(<?php echo $amt; ?>, this)">
                                <?php echo $amt; ?>€
                            </button>
                            <?php if ($amt === $suggestedAmount): ?>
                                <span class="suggested-badge">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
                                        <path
                                            d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z" />
                                    </svg>
                                    ΠΡΟΤΕΙΝΟΜΕΝΟ
                                </span>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Currency Input -->
                <div class="currency-input-wrapper">
                    <div class="currency-symbol">
                        <span class="symbol">€</span>
                        <span class="currency-label">EUR</span>
                    </div>
                    <input name="amount" type="number" min="1" value="50" required class="currency-input"
                        id="donationAmount" data-error="Το ποσό πρέπει να είναι τουλάχιστον 1€;">
                    <span class="decimal-part">.00</span>
                </div>
                <p class="field-hint" id="donationAmountHint">Θα δωρίσετε 50€</p>

                <div class="form-section">
                    <label>Όνομα <span id="donorNameRequired">*</span></label>
                    <input type="text" name="donor_name" id="donorNameInput" placeholder="Το όνομά σας" required
                        data-error="Συμπληρώστε το όνομά σας."
                        value="<?php echo e($prefillDonorName); ?>">
                </div>

                <div class="form-section">
                    <label>Email <span id="donorEmailRequired">*</span></label>
                    <input type="email" name="donor_email" id="donorEmailInput" placeholder="email@example.com" required
                        data-error="Συμπληρώστε έγκυρο email."
                        value="<?php echo e($prefillDonorEmail); ?>">
                </div>

                <div class="form-section">
                    <label>Μήνυμα (προαιρετικό)</label>
                    <input type="text" name="message" maxlength="280" placeholder="Καλή επιτυχία!">
                </div>

                <div class="form-section">
                    <label class="checkbox-row">
                        <input type="checkbox" name="is_anonymous" value="1" id="donateAnonymous">
                        Ανώνυμη δωρεά
                    </label>
                    <p class="field-hint">Το όνομά σας δεν θα εμφανιστεί δημόσια. Σε ανώνυμη δωρεά το όνομα και το email
                        είναι προαιρετικά (απαιτούνται μόνο αν δεν είναι επιλεγμένο).</p>
                </div>

                <div class="form-section">
                    <label class="checkbox-row small">
                        <input type="checkbox" name="privacy_consent" value="1" required
                            data-error="Πρέπει να αποδεχτείτε την Πολιτική Απορρήτου.">
                        <span>
                            Αποδέχομαι την <a href="<?php echo BASE_URL; ?>/privacy.php" target="_blank">Πολιτική
                                Απορρήτου</a>
                            και συναινώ στην επεξεργασία των δεδομένων μου. *
                        </span>
                    </label>
                </div>

                <button class="btn primary" type="submit"
                    style="width: 100%; font-size: 16px; padding: 16px; margin-top: 8px;">
                    Ολοκλήρωση Δωρεάς
                </button>

                <!-- GiveHope Guarantee -->
                <div class="guarantee-message">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2"
                        style="flex-shrink: 0; margin-top: 2px;">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                        <polyline points="9 12 12 15 16 10" stroke-width="2.5" />
                    </svg>
                    <div class="text">
                        <strong>Το GiveHope προστατεύει τη δωρεά σας</strong><br>
                        Εγγυόμαστε πλήρη επιστροφή χρημάτων για έως και ένα έτος στη σπάνια περίπτωση απάτης.
                        <a href="<?php echo BASE_URL; ?>/giving-guarantee.php">Δείτε το GiveHope Giving Guarantee</a>.
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function selectAmount(amount, btn) {
        const amountInput = document.getElementById('donationAmount');
        amountInput.value = amount;
        amountInput.dispatchEvent(new Event('input', { bubbles: true }));
        document.querySelectorAll('.amount-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
    }

    document.addEventListener('DOMContentLoaded', function () {
        const suggestedBtn = document.querySelector('.amount-btn.suggested');
        if (suggestedBtn) suggestedBtn.classList.add('active');

        const amountInput = document.getElementById('donationAmount');
        amountInput.addEventListener('input', function () {
            const val = parseInt(this.value);
            document.querySelectorAll('.amount-btn').forEach(btn => {
                const btnAmount = parseInt(btn.textContent);
                btn.classList.toggle('active', btnAmount === val);
            });
        });

        const anon = document.getElementById('donateAnonymous');
        const nameInput = document.getElementById('donorNameInput');
        const emailInput = document.getElementById('donorEmailInput');
        const nameStar = document.getElementById('donorNameRequired');
        const emailStar = document.getElementById('donorEmailRequired');
        function syncAnonymousFields() {
            const hide = anon && anon.checked;
            if (nameInput) nameInput.required = !hide;
            if (emailInput) emailInput.required = !hide;
            if (nameStar) nameStar.style.display = hide ? 'none' : '';
            if (emailStar) emailStar.style.display = hide ? 'none' : '';
        }
        if (anon) {
            anon.addEventListener('change', syncAnonymousFields);
            syncAnonymousFields();
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>