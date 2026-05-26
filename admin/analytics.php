<?php
// Admin Analytics API — δεδομένα για γραφήματα Chart.js στο admin

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

header('Content-Type: application/json; charset=utf-8');

// Έλεγχος admin πρόσβασης
if (!current_admin()) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$pdo = db();

// Δωρεές ανά ημέρα (30 ημέρες), όλη η πλατφόρμα
$stmt = $pdo->query("
    SELECT DATE(created_at) AS day, SUM(amount) AS total
    FROM donations
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY day ASC
");
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
$stmt = $pdo->query("
    SELECT cat.name, cat.icon, SUM(d.amount) AS total, COUNT(d.id) AS count
    FROM donations d
    JOIN campaigns c ON c.id = d.campaign_id
    JOIN categories cat ON cat.id = c.category_id
    GROUP BY cat.id
    ORDER BY total DESC
");
$donationsByCategory = $stmt->fetchAll();

// Κορυφαίοι έρανοι (πρόοδος)
$stmt = $pdo->query("
    SELECT c.id, c.title, c.current_amount, c.target_amount,
           ROUND((c.current_amount / GREATEST(c.target_amount, 1)) * 100) AS progress,
           COALESCE(u.name, o.name) AS creator
    FROM campaigns c
    LEFT JOIN users u ON u.id = c.user_id
    LEFT JOIN organizations o ON o.id = c.org_id
    WHERE c.status = 'approved' AND c.target_amount > 0
    ORDER BY c.current_amount DESC
    LIMIT 5
");
$campaignsProgress = $stmt->fetchAll();

// Νέες εγγραφές (30 ημέρες)
$stmt = $pdo->query("
    SELECT DATE(created_at) AS day, COUNT(*) AS count, 'user' AS type
    FROM users
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    UNION ALL
    SELECT DATE(created_at) AS day, COUNT(*) AS count, 'org' AS type
    FROM organizations
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY day ASC
");
$registrations = $stmt->fetchAll();

// Επιστροφή JSON
echo json_encode([
    'donations_by_day' => $donationsByDayFilled,
    'donations_by_category' => $donationsByCategory,
    'campaigns_progress' => $campaignsProgress,
    'registrations' => $registrations
], JSON_UNESCAPED_UNICODE);
