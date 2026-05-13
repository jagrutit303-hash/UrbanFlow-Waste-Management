<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $category = $_POST['category'];
    $zone_id = $_POST['zone_id'];
    $urgency_level = $_POST['urgency_level'] ?? 'Low';
    $comment = $_POST['comment'];
    
    $lat = $_POST['lat'] ?? null;
    $lng = $_POST['lng'] ?? null;
    
    // Use the actual logged-in user ID
    $citizen_id = $_SESSION['user_id']; 

    // Prepared Statement for Secure Disposal Request
    $stmt = $conn->prepare("INSERT INTO disposal_requests (citizen_id, zone_id, category, urgency_level, comment, lat, lng, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'Logged')");
    $stmt->bind_param("iisssdd", $citizen_id, $zone_id, $category, $urgency_level, $comment, $lat, $lng);

    if ($stmt->execute()) {
        header("Location: dashboard.php?success=1");
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>