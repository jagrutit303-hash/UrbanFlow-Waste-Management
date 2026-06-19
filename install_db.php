<?php
/**
 * install_db.php — UrbanFlow One-Time Database Installer
 *
 * ⚠️  DELETE THIS FILE after running it once!
 * Visit: https://your-vercel-url.vercel.app/install_db.php
 *
 * This script creates all tables and inserts mock data
 * using the same DB credentials from your Vercel env vars.
 */

// ── Security: basic token check ──────────────────────────────────────────────
// Change this token to something secret before deploying!
$INSTALL_TOKEN = 'urbanflow2026';

if (!isset($_GET['token']) || $_GET['token'] !== $INSTALL_TOKEN) {
    http_response_code(403);
    die("<h2 style='font-family:sans-serif;color:red'>❌ Forbidden. Add ?token=urbanflow2026 to the URL.</h2>");
}

// ── Load env vars ─────────────────────────────────────────────────────────────
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    $lines = file($env_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (getenv($name) === false) {
            putenv($name . '=' . $value);
        }
    }
}

$host = getenv('DB_HOST') ?: '127.0.0.1';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$db   = getenv('DB_NAME') ?: 'urbanflow_db';
$port = (int)(getenv('DB_PORT') ?: 3306);
$ssl  = filter_var(getenv('DB_SSL'), FILTER_VALIDATE_BOOLEAN);

// ── Connect ───────────────────────────────────────────────────────────────────
mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_init();

if ($ssl) {
    $conn->ssl_set(NULL, NULL, NULL, NULL, NULL);
    $ok = $conn->real_connect($host, $user, $pass, $db, $port, NULL,
        MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT);
} else {
    $ok = $conn->real_connect($host, $user, $pass, $db, $port);
}

if (!$ok) {
    die("<h2 style='font-family:sans-serif;color:red'>❌ Connection failed: " . htmlspecialchars($conn->connect_error) . "</h2>");
}
$conn->set_charset('utf8mb4');

// ── SQL Statements ────────────────────────────────────────────────────────────
$statements = [

"SET FOREIGN_KEY_CHECKS = 0",

"DROP TABLE IF EXISTS fleet_assignments",
"DROP TABLE IF EXISTS feedback",
"DROP TABLE IF EXISTS driver_locations",
"DROP TABLE IF EXISTS illegal_dumps",
"DROP TABLE IF EXISTS disposal_requests",
"DROP TABLE IF EXISTS zones",
"DROP TABLE IF EXISTS users",

"SET FOREIGN_KEY_CHECKS = 1",

"CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('citizen', 'admin', 'collector') NOT NULL DEFAULT 'citizen',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",

"CREATE TABLE zones (
    zone_id INT AUTO_INCREMENT PRIMARY KEY,
    zone_name VARCHAR(100) NOT NULL,
    lat DECIMAL(10, 6) DEFAULT 14.4677,
    lng DECIMAL(10, 6) DEFAULT 75.9218,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)",

"CREATE TABLE disposal_requests (
    request_id INT AUTO_INCREMENT PRIMARY KEY,
    citizen_id INT NOT NULL,
    zone_id INT NOT NULL,
    category VARCHAR(50) NOT NULL,
    comment TEXT,
    urgency_level ENUM('Low', 'Medium', 'High') DEFAULT 'Low',
    status VARCHAR(50) DEFAULT 'Logged',
    lat DECIMAL(10, 6) DEFAULT NULL,
    lng DECIMAL(10, 6) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (citizen_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (zone_id) REFERENCES zones(zone_id) ON DELETE CASCADE
)",

"CREATE TABLE illegal_dumps (
    dump_id INT AUTO_INCREMENT PRIMARY KEY,
    citizen_id INT NOT NULL,
    zone_id INT NOT NULL,
    description TEXT NOT NULL,
    image_path VARCHAR(255) DEFAULT NULL,
    voice_note_path VARCHAR(255) DEFAULT NULL,
    volume VARCHAR(50) DEFAULT NULL,
    severity ENUM('Low', 'Medium', 'High', 'Critical') DEFAULT 'Medium',
    status ENUM('Reported', 'Under Review', 'Cleanup Dispatched', 'Resolved') DEFAULT 'Reported',
    citizen_lat DECIMAL(10, 6) DEFAULT NULL,
    citizen_lng DECIMAL(10, 6) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (citizen_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (zone_id) REFERENCES zones(zone_id) ON DELETE CASCADE
)",

"CREATE TABLE fleet_assignments (
    assignment_id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT DEFAULT NULL,
    dump_id INT DEFAULT NULL,
    collector_id INT NOT NULL,
    vehicle_no VARCHAR(50) NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES disposal_requests(request_id) ON DELETE CASCADE,
    FOREIGN KEY (dump_id) REFERENCES illegal_dumps(dump_id) ON DELETE CASCADE,
    FOREIGN KEY (collector_id) REFERENCES users(user_id) ON DELETE CASCADE
)",

"CREATE TABLE feedback (
    feedback_id INT AUTO_INCREMENT PRIMARY KEY,
    request_id INT NOT NULL,
    rating INT CHECK(rating BETWEEN 1 AND 5),
    comments TEXT,
    voice_feedback_path VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (request_id) REFERENCES disposal_requests(request_id) ON DELETE CASCADE
)",

"CREATE TABLE driver_locations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    driver_id INT NOT NULL UNIQUE,
    lat DECIMAL(10, 6) NOT NULL,
    lng DECIMAL(10, 6) NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES users(user_id) ON DELETE CASCADE
)",

// Password for all: pass1234
"INSERT INTO users (user_id, full_name, email, password, role) VALUES
(1, 'Jagruti (Citizen)', 'citizen@urbanflow.com', '\$2y\$10\$rSBawCIiJCY8ZlD4SEZ4u.Iao.hteQIZqwiiCGAbhfEOWC6q7qoiC', 'citizen'),
(2, 'Driver Dave',       'driver@urbanflow.com',  '\$2y\$10\$rSBawCIiJCY8ZlD4SEZ4u.Iao.hteQIZqwiiCGAbhfEOWC6q7qoiC', 'collector'),
(3, 'Admin User',        'admin@urbanflow.com',   '\$2y\$10\$rSBawCIiJCY8ZlD4SEZ4u.Iao.hteQIZqwiiCGAbhfEOWC6q7qoiC', 'admin')",

"INSERT INTO zones (zone_id, zone_name, lat, lng) VALUES
(1, 'MCC B Block',             14.4577, 75.9118),
(2, 'Vidyanagar',              14.4700, 75.9200),
(3, 'PJ Extension',            14.4600, 75.9300),
(4, 'Nittuvalli',              14.4500, 75.9000),
(5, 'Shamanur',                14.4800, 75.9400)",

"INSERT INTO disposal_requests (request_id, citizen_id, zone_id, category, comment, urgency_level, status) VALUES
(1, 1, 1, 'Organic',    'Pick up from main gate.',    'Medium', 'Logged'),
(2, 1, 2, 'Hazardous',  'Chemical waste in bin.',     'High',   'Dispatched'),
(3, 1, 3, 'Recyclable', 'Old newspapers and boxes.',  'Low',    'Resolved')",

"INSERT INTO illegal_dumps (citizen_id, zone_id, description, severity, status, citizen_lat, citizen_lng) VALUES
(1, 4, 'Large pile of construction debris near park entrance.', 'High',   'Reported',     14.4510, 75.9010),
(1, 1, 'Bags of household trash left outside dumpster area.',   'Medium', 'Under Review', 14.4580, 75.9120)",

"INSERT INTO fleet_assignments (request_id, collector_id, vehicle_no) VALUES (2, 2, 'KA-17-EV-2026')",

"INSERT INTO driver_locations (driver_id, lat, lng) VALUES (2, 14.4700, 75.9100)",

];

// ── Execute ───────────────────────────────────────────────────────────────────
$results = [];
$errors  = [];

foreach ($statements as $sql) {
    $label = substr(trim($sql), 0, 60) . '...';
    if ($conn->query($sql) === TRUE) {
        $results[] = "✅ " . htmlspecialchars($label);
    } else {
        $errors[]  = "❌ " . htmlspecialchars($label) . " → " . htmlspecialchars($conn->error);
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UrbanFlow DB Installer</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #f0fdf4; margin: 0; padding: 40px 20px; color: #1e293b; }
        .card { max-width: 700px; margin: 0 auto; background: white; border-radius: 24px; padding: 40px; box-shadow: 0 20px 60px rgba(0,0,0,0.08); }
        h1 { font-size: 2rem; font-weight: 800; margin-bottom: 5px; }
        .badge { display: inline-block; padding: 4px 14px; border-radius: 50px; font-size: 0.8rem; font-weight: 700; }
        .success { background: #f0fdf4; color: #166534; }
        .error   { background: #fef2f2; color: #991b1b; }
        .log-item { padding: 8px 14px; border-radius: 8px; font-size: 0.85rem; margin: 6px 0; font-family: monospace; }
        .log-ok  { background: #f0fdf4; color: #166534; }
        .log-err { background: #fef2f2; color: #991b1b; }
        .creds { background: #1e293b; color: #4ade80; padding: 20px; border-radius: 12px; font-family: monospace; margin-top: 20px; font-size: 0.9rem; line-height: 1.8; }
        .warn { background: #fef9c3; color: #713f12; padding: 16px 20px; border-radius: 12px; margin-top: 20px; font-size: 0.9rem; font-weight: 600; }
    </style>
</head>
<body>
<div class="card">
    <h1>URBAN<span style="color:#4ade80">FLOW</span> Installer</h1>
    <p style="color:#64748b">Database setup results</p>

    <?php if (empty($errors)): ?>
        <div class="badge success" style="margin-bottom:20px;">✅ All <?= count($results) ?> statements executed successfully!</div>
    <?php else: ?>
        <div class="badge error" style="margin-bottom:20px;">⚠️ <?= count($errors) ?> error(s) — <?= count($results) ?> succeeded</div>
    <?php endif; ?>

    <div style="max-height:400px;overflow-y:auto;">
        <?php foreach ($results as $r): ?>
            <div class="log-item log-ok"><?= $r ?></div>
        <?php endforeach; ?>
        <?php foreach ($errors as $e): ?>
            <div class="log-item log-err"><?= $e ?></div>
        <?php endforeach; ?>
    </div>

    <?php if (empty($errors)): ?>
    <div class="creds">
        🔐 Test Login Credentials (password: <b>pass1234</b>)<br><br>
        👤 Citizen → citizen@urbanflow.com<br>
        🚛 Driver  → driver@urbanflow.com<br>
        🛡️ Admin   → admin@urbanflow.com
    </div>
    <div class="warn">
        ⚠️ <strong>IMPORTANT:</strong> Delete <code>install_db.php</code> from your project immediately after this!<br>
        Anyone with the URL can reset your database.
    </div>
    <p style="margin-top:20px;">
        <a href="/" style="background:#4ade80;color:white;padding:12px 28px;border-radius:12px;text-decoration:none;font-weight:700;">
            → Go to Homepage
        </a>
    </p>
    <?php endif; ?>
</div>
</body>
</html>
