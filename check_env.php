<?php
/**
 * check_env.php — Debug page to verify Vercel environment variables
 * DELETE THIS FILE after verifying! 
 * Visit: https://your-url.vercel.app/check_env.php?token=urbanflow2026
 */
if (!isset($_GET['token']) || $_GET['token'] !== 'urbanflow2026') {
    http_response_code(403); die("Forbidden");
}

$vars = ['DB_HOST','DB_PORT','DB_USER','DB_NAME','DB_SSL','HF_API_TOKEN','HF_MODEL','HF_BASE_URL'];

echo "<!DOCTYPE html><html><head><meta charset='UTF-8'>
<title>UrbanFlow Env Check</title>
<style>body{font-family:monospace;background:#0f172a;color:#f1f5f9;padding:40px;} 
.ok{color:#4ade80;} .missing{color:#f87171;} 
table{border-collapse:collapse;width:100%;margin-top:20px;}
td,th{padding:12px 16px;border:1px solid #334155;text-align:left;}
th{background:#1e293b;}
</style></head><body>
<h2 style='color:#4ade80'>UrbanFlow — Environment Variable Check</h2>
<table><tr><th>Variable</th><th>Set?</th><th>Value (redacted)</th></tr>";

foreach ($vars as $var) {
    $val = getenv($var);
    if ($val !== false && $val !== '') {
        $display = strlen($val) > 6 ? substr($val, 0, 4) . '****' . substr($val, -2) : '****';
        echo "<tr><td>$var</td><td class='ok'>✅ SET</td><td>$display</td></tr>";
    } else {
        echo "<tr><td>$var</td><td class='missing'>❌ MISSING</td><td>—</td></tr>";
    }
}

echo "</table>";

// Try DB connection
echo "<h3 style='margin-top:30px;'>Database Connection Test</h3>";
$host = getenv('DB_HOST') ?: '';
$port = (int)(getenv('DB_PORT') ?: 3306);
$user = getenv('DB_USER') ?: '';
$pass = getenv('DB_PASS') ?: '';
$db   = getenv('DB_NAME') ?: '';
$ssl  = filter_var(getenv('DB_SSL'), FILTER_VALIDATE_BOOLEAN);

echo "<p>Connecting to: <code>" . htmlspecialchars($host) . ":" . $port . "</code> (SSL: " . ($ssl ? 'YES' : 'NO') . ")</p>";

mysqli_report(MYSQLI_REPORT_OFF);
$conn = mysqli_init();
if ($ssl) {
    $conn->ssl_set(NULL, NULL, NULL, NULL, NULL);
    $ok = $conn->real_connect($host, $user, $pass, $db, $port, NULL, MYSQLI_CLIENT_SSL | MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT);
} else {
    $ok = $conn->real_connect($host, $user, $pass, $db, $port);
}

if ($ok && !$conn->connect_errno) {
    $r = $conn->query("SELECT VERSION() as v");
    $row = $r->fetch_assoc();
    echo "<p class='ok'>✅ Connected! MySQL version: " . htmlspecialchars($row['v']) . "</p>";
    $conn->close();
} else {
    echo "<p class='missing'>❌ Failed: " . htmlspecialchars($conn->connect_error) . " (Error #" . $conn->connect_errno . ")</p>";
}

echo "<p style='margin-top:30px;color:#94a3b8;font-size:0.8rem;'>⚠️ Delete check_env.php after reviewing this page.</p>";
echo "</body></html>";
?>
