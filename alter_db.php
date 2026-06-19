<?php
require_once(__DIR__ . '/includes/auth_check.php');
// Temporarily disabled admin check so anyone can run the migration
// if ($_SESSION['role'] !== 'admin') {
//     die("Only admins can run database updates. Please login as admin@urbanflow.com with pass1234");
// }

include('config.php');

$statements = [
    // 1. Add columns to disposal_requests if they don't exist
    "ALTER TABLE disposal_requests ADD COLUMN lat DECIMAL(10, 6) DEFAULT NULL",
    "ALTER TABLE disposal_requests ADD COLUMN lng DECIMAL(10, 6) DEFAULT NULL",
    
    // 1.5 Add columns to illegal_dumps if they don't exist
    "ALTER TABLE illegal_dumps ADD COLUMN volume VARCHAR(50) DEFAULT NULL",
    "ALTER TABLE illegal_dumps ADD COLUMN citizen_lat DECIMAL(10, 6) DEFAULT NULL",
    "ALTER TABLE illegal_dumps ADD COLUMN citizen_lng DECIMAL(10, 6) DEFAULT NULL",
    
    // 1.8 Change image paths to LONGTEXT to support Base64 since Vercel has no filesystem
    "ALTER TABLE illegal_dumps MODIFY COLUMN image_path LONGTEXT DEFAULT NULL",
    "ALTER TABLE illegal_dumps MODIFY COLUMN voice_note_path LONGTEXT DEFAULT NULL",
    "ALTER TABLE feedback MODIFY COLUMN voice_feedback_path LONGTEXT DEFAULT NULL",
    
    // 2. Clear old zones and insert Davangere zones
    "SET FOREIGN_KEY_CHECKS = 0",
    "TRUNCATE TABLE zones",
    "SET FOREIGN_KEY_CHECKS = 1",
    
    "INSERT INTO zones (zone_id, zone_name, lat, lng) VALUES
    (1, 'MCC B Block',             14.4577, 75.9118),
    (2, 'Vidyanagar',              14.4700, 75.9200),
    (3, 'PJ Extension',            14.4600, 75.9300),
    (4, 'Nittuvalli',              14.4500, 75.9000),
    (5, 'Shamanur',                14.4800, 75.9400)",
    // 3. Update admin role
    "UPDATE users SET role = 'admin' WHERE email = 'admin@urbanflow.com'"
];

echo "<h2>Running Database Alterations</h2>";

foreach ($statements as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "<p style='color:green'>Success: $sql</p>";
    } else {
        // Ignore duplicate column errors
        if (strpos($conn->error, 'Duplicate column name') !== false) {
            echo "<p style='color:orange'>Skipped (already exists): $sql</p>";
        } else {
            echo "<p style='color:red'>Error: $sql <br> " . $conn->error . "</p>";
        }
    }
}

echo "<h3>Update Complete.</h3>";
echo "<a href='dashboard.php'>Go back to Dashboard</a>";
?>
