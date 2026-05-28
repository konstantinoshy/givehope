<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

if (!function_exists('secure_session_start')) {
    function secure_session_start(): void {
        if (session_status() !== PHP_SESSION_NONE) {
            return;
        }
        // Ορισμός διάρκειας session στον server για 1 ημέρα (86400 δευτερόλεπτα)
        ini_set('session.gc_maxlifetime', '86400');

        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['SERVER_PORT'] ?? '') === '443');
        session_set_cookie_params([
            'lifetime' => 86400, // 1 ημέρα (αντί για 0 ώστε να μην σβήνει με το κλείσιμο του tab)
            'path'     => '/',
            'domain'   => '',
            'secure'   => $isHttps,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

if (!function_exists('send_security_headers')) {
    /**
     * Fallback security headers όταν το web server δεν στέλνει .htaccess directives
     * (π.χ. PHP built-in server, nginx χωρίς αντίστοιχο conf).
     * Σε XAMPP/Apache τα headers στέλνονται μέσω .htaccess για κάλυψη και των 404 pages.
     */
    function send_security_headers(): void {
        if (headers_sent()) {
            return;
        }
        header_remove("X-Powered-By");
        // Αν τρέχουμε υπό Apache με mod_headers, το .htaccess τα έχει ήδη στείλει.
        if (function_exists('apache_get_modules')
            && in_array('mod_headers', apache_get_modules(), true)) {
            return;
        }
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: SAMEORIGIN");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        header("Permissions-Policy: geolocation=(), microphone=(), camera=()");
        header(
            "Content-Security-Policy: "
            . "default-src 'self'; "
            . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; "
            . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; "
            . "img-src 'self' data: blob: https://images.unsplash.com; "
            . "font-src 'self' https://fonts.gstatic.com data:; "
            . "connect-src 'self'; "
            . "frame-ancestors 'self'; "
            . "base-uri 'self'; "
            . "form-action 'self'"
        );
    }
}

secure_session_start();
send_security_headers();

define('AUTH_REFRESH_INTERVAL', 30); // 30 δευτερόλεπτα

function _needs_refresh(string $key): bool {
    $tsKey = $key . '_refreshed_at';
    if (!isset($_SESSION[$tsKey])) return true;
    return (time() - $_SESSION[$tsKey]) >= AUTH_REFRESH_INTERVAL;
}

function _mark_refreshed(string $key): void {
    $_SESSION[$key . '_refreshed_at'] = time();
}

// Σύνδεση οργανισμού (session org)
function current_org(): ?array {
    if (!isset($_SESSION['org'])) return null;

    static $done = false;
    if (!$done && _needs_refresh('org')) {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT id, name, email, verified FROM organizations WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute([':id' => $_SESSION['org']['id']]);
        $fresh = $stmt->fetch();
        if ($fresh) {
            $_SESSION['org'] = [
                'id' => $fresh['id'],
                'name' => $fresh['name'],
                'email' => $fresh['email'],
                'verified' => $fresh['verified'] ?? 0,
            ];
        } else {
            unset($_SESSION['org']);
        }
        _mark_refreshed('org');
        $done = true;
    }

    return $_SESSION['org'] ?? null;
}

function require_org(): void {
    if (!current_org()) {
        redirect(BASE_URL . "/login.php?type=org");
    }
}

function login_org(array $org): void {
    session_regenerate_id(true);
    $_SESSION['org'] = [
        'id' => $org['id'],
        'name' => $org['name'],
        'email' => $org['email'],
        'verified' => $org['verified'] ?? 0,
    ];
    _mark_refreshed('org');
}

// Σύνδεση χρήστη (session user)
function current_user(): ?array {
    if (!isset($_SESSION['user'])) return null;

    static $done = false;
    if (!$done && _needs_refresh('user')) {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT id, name, email, id_verified FROM users WHERE id = :id AND deleted_at IS NULL");
        $stmt->execute([':id' => $_SESSION['user']['id']]);
        $fresh = $stmt->fetch();
        if ($fresh) {
            $_SESSION['user'] = [
                'id' => $fresh['id'],
                'name' => $fresh['name'],
                'email' => $fresh['email'],
                'id_verified' => $fresh['id_verified'] ?? 0,
            ];
        } else {
            unset($_SESSION['user']);
        }
        _mark_refreshed('user');
        $done = true;
    }

    return $_SESSION['user'] ?? null;
}

function require_user(): void {
    if (!current_user()) {
        redirect(BASE_URL . "/login.php?type=user");
    }
}

function login_user(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id' => $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'id_verified' => $user['id_verified'] ?? 0,
    ];
    _mark_refreshed('user');
}

// Σύνδεση διαχειριστή
function current_admin(): ?array {
    if (!isset($_SESSION['admin'])) return null;

    static $done = false;
    if (!$done && _needs_refresh('admin')) {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT id, username, email FROM admins WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['admin']['id']]);
        $fresh = $stmt->fetch();
        if ($fresh) {
            $_SESSION['admin'] = [
                'id' => $fresh['id'],
                'username' => $fresh['username'],
                'email' => $fresh['email'],
            ];
        } else {
            unset($_SESSION['admin']);
            return null;
        }
        _mark_refreshed('admin');
        $done = true;
    }

    return $_SESSION['admin'];
}

function require_admin(): void {
    if (!current_admin()) {
        redirect(BASE_URL . "/admin/login.php");
    }
}

function login_admin(array $admin): void {
    session_regenerate_id(true);
    $_SESSION['admin'] = [
        'id' => $admin['id'],
        'username' => $admin['username'],
        'email' => $admin['email'],
    ];
    _mark_refreshed('admin');
}

function logout_admin(): void {
    unset($_SESSION['admin']);
}

function logout_all(): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}
