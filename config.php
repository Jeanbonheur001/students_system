<?php
declare(strict_types=1);

/**
 * config.php - Database configuration, PDO connection, automatic schema
 * installation and shared helper functions.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

// ---- Database settings (XAMPP / WAMP defaults) ----
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'practice_db');   
const DB_CHARSET = 'utf8mb4';

// ---- Application settings ----
const APP_DEBUG    = true;                  // show real database errors (set false when finished)
const APP_NAME     = 'Student Portal';
const OWNER_REG_NO = '25RP14889';           // registration number linked to the default photo
const OWNER_PHOTO  = 'assets/img/owner.jpg'; // default photo for OWNER_REG_NO
const UPLOAD_DIR   = __DIR__ . '/uploads/profile/';
const UPLOAD_URL   = 'uploads/profile/';
const MAX_UPLOAD   = 2 * 1024 * 1024;        // 2 MB

/**
 * Returns a shared PDO connection. On first use it creates the database
 * and all tables, and seeds sample data.
 */
function db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    try {
        // 1) Connect to the server (no database yet) and create practice_db
        $server = new PDO(
            'mysql:host=' . DB_HOST . ';charset=' . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        $server->exec('CREATE DATABASE IF NOT EXISTS `' . DB_NAME . '` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');

        // 2) Connect to the database itself
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );

        install_schema($pdo);
    } catch (PDOException $e) {
        http_response_code(500);
        exit('<h3 style="font-family:sans-serif;color:#b91c1c">Database connection failed</h3>'
            . '<p style="font-family:sans-serif">Start MySQL in XAMPP/WAMP and check the settings in <code>config.php</code>.</p>'
            . '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>');
    }

    return $pdo;
}

/** Creates all tables (if missing) and inserts sample data once. */
function install_schema(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        full_name     VARCHAR(120) NOT NULL,
        reg_number    VARCHAR(30)  NOT NULL UNIQUE,
        email         VARCHAR(150) NOT NULL UNIQUE,
        phone         VARCHAR(30)  NULL,
        password_hash VARCHAR(255) NOT NULL,
        photo         VARCHAR(255) NULL,
        created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // If a students table from an earlier practical exists with a different
    // structure, keep it as a backup copy and build the correct one.
    $required = ['id', 'reg_number', 'full_name', 'email', 'phone', 'department', 'year_level', 'created_at', 'updated_at'];
    $exists = $pdo->query("SHOW TABLES LIKE 'students'")->fetchColumn();
    if ($exists) {
        $have = array_column($pdo->query('SHOW COLUMNS FROM students')->fetchAll(), 'Field');
        if (array_diff($required, $have)) {
            $pdo->exec('RENAME TABLE students TO students_backup_' . date('Ymd_His'));
        } else {
            // columns exist - make sure the UNIQUE key on reg_number is present
            $uniq = $pdo->query("SELECT COUNT(*) FROM information_schema.STATISTICS
                                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'students'
                                   AND COLUMN_NAME = 'reg_number' AND NON_UNIQUE = 0")->fetchColumn();
            if (!(int) $uniq) {
                $pdo->exec('ALTER TABLE students ADD UNIQUE KEY uq_reg_number (reg_number)');
            }
        }
    }

    $pdo->exec("CREATE TABLE IF NOT EXISTS students (
        id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        reg_number  VARCHAR(30)  NOT NULL UNIQUE,
        full_name   VARCHAR(120) NOT NULL,
        email       VARCHAR(150) NOT NULL,
        phone       VARCHAR(30)  NULL,
        department  VARCHAR(100) NOT NULL DEFAULT 'ICT',
        year_level  TINYINT UNSIGNED NOT NULL DEFAULT 1,
        created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS activity_log (
        id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id    INT UNSIGNED NULL,
        action     VARCHAR(120) NOT NULL,
        ip_address VARCHAR(45)  NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Seed 10 sample students (Task 3) only when the table is empty
    if ((int) $pdo->query('SELECT COUNT(*) FROM students')->fetchColumn() === 0) {
        seed_students($pdo);
    }
}

/** Inserts the 10 sample students using a prepared statement. */
function seed_students(PDO $pdo): int
{
    $rows = [
        ['25RP14880', 'Alice Uwase',      'alice.uwase@example.com',      '0788000001', 'ICT',         1],
        ['25RP14881', 'Eric Mugisha',     'eric.mugisha@example.com',     '0788000002', 'ICT',         1],
        ['25RP14882', 'Diane Ingabire',   'diane.ingabire@example.com',   '0788000003', 'Networking',  1],
        ['25RP14883', 'Patrick Habimana', 'patrick.habimana@example.com', '0788000004', 'Software Dev',1],
        ['25RP14884', 'Grace Mukamana',   'grace.mukamana@example.com',   '0788000005', 'ICT',         1],
        ['25RP14885', 'Joseph Niyonzima', 'joseph.niyonzima@example.com', '0788000006', 'Networking',  1],
        ['25RP14886', 'Sandrine Uwimana', 'sandrine.uwimana@example.com', '0788000007', 'Software Dev',1],
        ['25RP14887', 'Claude Nshuti',    'claude.nshuti@example.com',     '0788000008', 'ICT',         1],
        ['25RP14888', 'Aline Mutesi',     'aline.mutesi@example.com',      '0788000009', 'Networking',  1],
        ['25RP14890', 'David Kamanzi',    'david.kamanzi@example.com',     '0788000010', 'Software Dev',1],
    ];
    $stmt = $pdo->prepare(
        'INSERT IGNORE INTO students (reg_number, full_name, email, phone, department, year_level)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $n = 0;
    foreach ($rows as $r) {
        $stmt->execute($r);
        $n += $stmt->rowCount();
    }
    return $n;
}

// ------------------------------------------------------------------
// Helpers
// ------------------------------------------------------------------

/** Escape output (XSS protection). */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

function csrf_check(): void
{
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals($_SESSION['csrf'] ?? '', $sent)) {
        http_response_code(419);
        exit('Invalid or expired form token. Go back and try again.');
    }
}

function flash(?string $type = null, ?string $message = null): ?array
{
    if ($type !== null && $message !== null) {
        $_SESSION['flash'] = ['type' => $type, 'msg' => $message];
        return null;
    }
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function current_user(): array
{
    static $user = null;
    if ($user === null) {
        $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$_SESSION['user_id'] ?? 0]);
        $user = $stmt->fetch() ?: [];
        if (!$user) {
            session_destroy();
            redirect('login.php');
        }
    }
    return $user;
}

/** URL of a user's photo (uploaded one, owner default, or empty for initials). */
function photo_url(array $user): ?string
{
    if (!empty($user['photo']) && is_file(UPLOAD_DIR . $user['photo'])) {
        return UPLOAD_URL . rawurlencode($user['photo']);
    }
    if (strcasecmp($user['reg_number'] ?? '', OWNER_REG_NO) === 0) {
        return OWNER_PHOTO;
    }
    return null;
}

function initials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name)) ?: [];
    $out = '';
    foreach (array_slice($parts, 0, 2) as $p) {
        $out .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    return $out ?: '?';
}

function log_activity(?int $userId, string $action): void
{
    $stmt = db()->prepare('INSERT INTO activity_log (user_id, action, ip_address) VALUES (?, ?, ?)');
    $stmt->execute([$userId, $action, $_SERVER['REMOTE_ADDR'] ?? null]);
}

/**
 * Validates and stores an uploaded profile image.
 * Returns the stored file name, or throws RuntimeException with a friendly message.
 */
function save_profile_upload(array $file, int $userId): string
{
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Upload failed (error code ' . $file['error'] . ').');
    }
    if ($file['size'] > MAX_UPLOAD) {
        throw new RuntimeException('Image is too large. Maximum size is 2 MB.');
    }
    $info = @getimagesize($file['tmp_name']);
    $allowed = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp'];
    if ($info === false || !isset($allowed[$info[2]])) {
        throw new RuntimeException('Only JPG, PNG or WEBP images are allowed.');
    }
    if (!is_dir(UPLOAD_DIR)) {
        mkdir(UPLOAD_DIR, 0755, true);
    }
    $name = 'user_' . $userId . '_' . bin2hex(random_bytes(6)) . '.' . $allowed[$info[2]];
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $name)) {
        throw new RuntimeException('Could not save the uploaded file.');
    }
    return $name;
}
