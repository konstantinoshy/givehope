<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

require_user();

if (!is_post()) {
    redirect(BASE_URL . "/my-campaigns.php");
}

csrf_verify();

$pdo = db();
$userId = (int)current_user()['id'];
$id = (int)($_POST['id'] ?? 0);

// Επιβεβαίωση ότι ο έρανος ανήκει στον χρήστη
$stmt = $pdo->prepare("SELECT id FROM campaigns WHERE id = :id AND user_id = :uid");
$stmt->execute([':id' => $id, ':uid' => $userId]);
if (!$stmt->fetch()) {
    redirect(BASE_URL . "/my-campaigns.php");
}

// Έλεγχος αν υπάρχουν δωρεές
$cntStmt = $pdo->prepare("SELECT COUNT(*) FROM donations WHERE campaign_id = :id");
$cntStmt->execute([':id' => $id]);
$hasDonations = (int) $cntStmt->fetchColumn() > 0;

if ($hasDonations) {
    // Soft-delete: δεν σβήνουμε εράνους που έχουν δωρεές
    $upd = $pdo->prepare("UPDATE campaigns SET status = 'deleted' WHERE id = :id AND user_id = :uid");
    $upd->execute([':id' => $id, ':uid' => $userId]);
} else {
    $del = $pdo->prepare("DELETE FROM campaigns WHERE id = :id AND user_id = :uid");
    $del->execute([':id' => $id, ':uid' => $userId]);
}

redirect(BASE_URL . "/my-campaigns.php");

