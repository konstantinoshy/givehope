<?php

function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function money_eur(int $amount): string
{
    // ποσό σε EUR (ακέραιος)
    return number_format($amount, 0, ',', '.') . " €";
}

function redirect(string $path): void
{
    header("Location: " . $path);
    exit;
}

function flash(string $type, string $message): void
{
    if (session_status() === PHP_SESSION_NONE)
        session_start();
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}

function get_flash(): ?array
{
    if (session_status() === PHP_SESSION_NONE)
        session_start();
    $flash = $_SESSION['_flash'] ?? null;
    unset($_SESSION['_flash']);
    return $flash;
}

function is_post(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST';
}

// Καταγραφή επεξεργασίας (πίνακας data_processing_log)
function logDataProcessing(PDO $pdo, string $entityType, $entityId, string $action, string $actorType, $actorId, ?string $description = null): void
{
    try {
        $stmt = $pdo->prepare("
            INSERT INTO data_processing_log (entity_type, entity_id, action, actor_type, actor_id, description, ip_address)
            VALUES (:etype, :eid, :action, :atype, :aid, :desc, :ip)
        ");
        $stmt->execute([
            ':etype' => $entityType,
            ':eid' => $entityId,
            ':action' => $action,
            ':atype' => $actorType,
            ':aid' => $actorId,
            ':desc' => $description,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    } catch (Exception $e) {
        // αποτυχία καταγραφής — μη διακοπή ροής
    }
}

// SVG εικονίδια ανά id κατηγορίας (1–8)
function category_icon($categoryId): string
{
    $icons = [
        1 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>',
        2 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>',
        3 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        4 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="4" r="2"/><circle cx="18" cy="8" r="2"/><circle cx="20" cy="16" r="2"/><path d="M9 10a5 5 0 0 1 5 5v3.5a3.5 3.5 0 0 1-6.84 1.045Q6.52 17.48 4.46 16.84A3.5 3.5 0 0 1 5.5 10Z"/></svg>',
        5 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        6 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="M7 10.5v3m5-6v9m5-6v3"/></svg>',
        7 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="13.5" cy="6.5" r="0.5"/><circle cx="17.5" cy="10.5" r="0.5"/><circle cx="8.5" cy="7.5" r="0.5"/><circle cx="6.5" cy="12.5" r="0.5"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.555-2.503 5.555-5.555C21.965 6.012 17.461 2 12 2z"/></svg>',
        8 => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
    ];

    // Αν δοθεί string (όνομα κατηγορίας), αντιστοίχιση
    if (is_string($categoryId) && !is_numeric($categoryId)) {
        $nameMap = [
            'Υγεία' => 1,
            'Ιατρικά' => 1,
            'Εκπαίδευση' => 2,
            'Έκτακτη' => 3,
            'Ανάγκη' => 3,
            'Ζώα' => 4,
            'Κοινωνική' => 5,
            'Αλληλεγγύη' => 5,
            'Περιβάλλον' => 6,
            'Πολιτισμός' => 7,
            'Τέχνη' => 7,
            'Άλλο' => 8,
        ];
        foreach ($nameMap as $key => $id) {
            if (stripos($categoryId, $key) !== false) {
                $categoryId = $id;
                break;
            }
        }
    }

    return $icons[(int) $categoryId] ?? $icons[8]; // Προεπιλογή: εικονίδιο "Άλλο"
}

// Επιστρέφει χρώματα [φόντο, κείμενο] για κατηγορία
function category_color($categoryId): array
{
    $colors = [
        1 => ['#fce7f3', '#be185d'], // Ιατρικά
        2 => ['#dbeafe', '#1d4ed8'], // Εκπαίδευση
        3 => ['#fee2e2', '#dc2626'], // Έκτακτη Ανάγκη
        4 => ['#fef3c7', '#d97706'], // Ζώα
        5 => ['#d1fae5', '#059669'], // Κοινωνική Αλληλεγγύη
        6 => ['#ecfccb', '#65a30d'], // Περιβάλλον
        7 => ['#f3e8ff', '#9333ea'], // Πολιτισμός
        8 => ['#f3f4f6', '#6b7280'], // Άλλο
    ];

    return $colors[(int) $categoryId] ?? $colors[8];
}
