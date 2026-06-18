<?php
/**
 * config.php — UrbanFlow Database Configuration
 * 
 * On Vercel: credentials come from Vercel Environment Variables (set in dashboard)
 * On local XAMPP: credentials come from .env file via env_loader.php
 * 
 * Priority: Vercel env vars > .env file > hardcoded defaults
 */

// Only load .env file locally (the file won't exist on Vercel, and that's fine)
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    require_once('includes/env_loader.php');
    loadEnv($env_file);
}

$host = getenv('DB_HOST') ?: "127.0.0.1";
$user = getenv('DB_USER') ?: "root";
$pass = getenv('DB_PASS') ?: "";
$db   = getenv('DB_NAME') ?: "urbanflow_db";
$port = (int)(getenv('DB_PORT') ?: 3307);

// Suppress connection warnings, handle with try-catch
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $db, $port);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
    // Show a user-friendly error without exposing credentials
    http_response_code(503);
    die("
    <div style='padding:40px;background:#fee2e2;color:#991b1b;border:1px solid #f87171;border-radius:12px;font-family:sans-serif;max-width:600px;margin:40px auto;'>
        <h3 style='margin-top:0'>⚠️ Database Unavailable</h3>
        <p>Unable to connect to the database. Please try again later.</p>
        <p style='font-size:0.8rem;opacity:0.7'>Error code: " . htmlspecialchars($e->getCode()) . "</p>
    </div>");
}

/**
 * API Credentials — loaded from environment variables
 */
$hf_api_token = getenv('HF_API_TOKEN') ?: "";
?>