<?php
/**
 * config.php — UrbanFlow Database Configuration
 *
 * Supports:
 *  - Local XAMPP (loads from .env file, no SSL)
 *  - Aiven Cloud MySQL (SSL required, credentials from Vercel env vars)
 *
 * All other files in the project use $conn as a mysqli object — unchanged.
 */

// ── Load .env only on local dev (file won't exist on Vercel) ─────────────────
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    require_once('includes/env_loader.php');
    loadEnv($env_file);
}

// ── Read credentials from environment variables ───────────────────────────────
$host = getenv('DB_HOST') ?: '127.0.0.1';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: '';
$db   = getenv('DB_NAME') ?: 'urbanflow_db';
$port = (int)(getenv('DB_PORT') ?: 3307);
$ssl  = filter_var(getenv('DB_SSL'), FILTER_VALIDATE_BOOLEAN); // true on Aiven

// ── Connect ───────────────────────────────────────────────────────────────────
mysqli_report(MYSQLI_REPORT_OFF); // handle errors manually below

$conn = mysqli_init();

if ($conn === false) {
    http_response_code(503);
    die("<div style='padding:40px;font-family:sans-serif;background:#fee2e2;color:#991b1b;border-radius:12px;max-width:600px;margin:40px auto;'>
        <h3>⚠️ mysqli_init() failed</h3><p>PHP mysqli extension is not available.</p></div>");
}

if ($ssl) {
    // Aiven requires SSL. We skip CA verification so no cert file is needed.
    // For production hardening, download ca.pem from Aiven dashboard and set:
    //   $conn->ssl_set(NULL, NULL, '/path/to/ca.pem', NULL, NULL);
    $conn->ssl_set(NULL, NULL, NULL, NULL, NULL);

    // @ suppresses the warning so headers-already-sent doesn't chain
    $connected = @$conn->real_connect(
        $host, $user, $pass, $db, $port,
        NULL,
        MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT
    );
} else {
    // Local XAMPP — plain connection, no SSL needed
    $connected = @$conn->real_connect($host, $user, $pass, $db, $port);
}

if (!$connected || $conn->connect_errno) {
    $err_code = $conn->connect_errno;
    $err_msg  = $conn->connect_error;
    http_response_code(503);
    die("
    <div style='padding:40px;font-family:sans-serif;background:#fee2e2;color:#991b1b;
                border:1px solid #f87171;border-radius:12px;max-width:600px;margin:40px auto;'>
        <h3 style='margin-top:0'>⚠️ Database Unavailable</h3>
        <p>Could not connect to the database. Please try again later.</p>
        <p style='font-size:0.8rem;opacity:0.7'>Error #" . htmlspecialchars($err_code) . "</p>
    </div>");
}

$conn->set_charset('utf8mb4');

// ── API Credentials ───────────────────────────────────────────────────────────
$hf_api_token = getenv('HF_API_TOKEN') ?: '';
?>