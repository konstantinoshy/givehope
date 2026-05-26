<?php
// Analytics API — δεδομένα για γραφήματα Chart.js στο dashboard

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

$pdo = db();
$org = current_org();
$isAdmin = current_admin() !== null;

// Για οργανισμό: φιλτράρισμα δεδομένων μόνο για τους δικούς του εράνους
// Για admin: όλα τα δεδομένα της πλατφόρμας
$orgFilter = '';
$params = [];

if ($org && !$isAdmin) {
    $orgFilter = 'AND c.org_id = :org_id';
    $params[':org_id'] = (int) $org['id'];
} elseif (!$isAdmin && !$org) {
    // Μη εξουσιοδοτημένη πρόσβαση
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Δωρεές ανά ημέρα (30 ημέρες)
$sql = "
    SELECT DATE(d.created_at) AS day, SUM(d.amount) AS total
    FROM donations d
    JOIN campaigns c ON c.id = d.campaign_id
    WHERE d.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    $orgFilter
    GROUP BY DATE(d.created_at)
    ORDER BY day ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$donationsByDay = $stmt->fetchAll();

// Γέμισμα κενών ημερών με 0
$filledDays = [];
$today = new DateTime();
for ($i = 29; $i >= 0; $i--) {
    $date = (clone $today)->modify("-$i days")->format('Y-m-d');
    $filledDays[$date] = 0;
}
foreach ($donationsByDay as $row) {
    $filledDays[$row['day']] = (int) $row['total'];
}
$donationsByDayFilled = [];
foreach ($filledDays as $date => $amount) {
    $donationsByDayFilled[] = [
        'day' => $date,
        'label' => date('d/m', strtotime($date)),
        'total' => $amount
    ];
}

// Δωρεές ανά κατηγορία
$sql = "
    SELECT cat.name, cat.icon, SUM(d.amount) AS total
    FROM donations d
    JOIN campaigns c ON c.id = d.campaign_id
    JOIN categories cat ON cat.id = c.category_id
    WHERE 1=1 $orgFilter
    GROUP BY cat.id
    ORDER BY total DESC
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$donationsByCategory = $stmt->fetchAll();

// Κορυφαίοι έρανοι (πρόοδος)
$sql = "
    SELECT c.id, c.title, c.current_amount, c.target_amount,
           ROUND((c.current_amount / GREATEST(c.target_amount, 1)) * 100) AS progress
    FROM campaigns c
    WHERE c.status = 'approved' AND c.target_amount > 0
    $orgFilter
    ORDER BY c.current_amount DESC
    LIMIT 5
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$campaignsProgress = $stmt->fetchAll();

// Επιστροφή JSON
echo json_encode([
    'donations_by_day' => $donationsByDayFilled,
    'donations_by_category' => $donationsByCategory,
    'campaigns_progress' => $campaignsProgress
], JSON_UNESCAPED_UNICODE);
