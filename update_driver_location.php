<?php
session_start();
include('config.php');

header('Content-Type: application/json');

// Only collectors/drivers
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'collector') {
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

    $driver_id = $_SESSION['user_id'];
    $lat = isset($_REQUEST['lat']) ? floatval($_REQUEST['lat']) : 0;
    $lng = isset($_REQUEST['lng']) ? floatval($_REQUEST['lng']) : 0;

    // Upsert: Insert or update driver location
    $stmt = $conn->prepare("INSERT INTO driver_locations (driver_id, lat, lng) VALUES (?, ?, ?) 
                            ON DUPLICATE KEY UPDATE lat = VALUES(lat), lng = VALUES(lng), updated_at = CURRENT_TIMESTAMP");
    $stmt->bind_param("idd", $driver_id, $lat, $lng);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'lat' => $lat, 'lng' => $lng]);
    } else {
        echo json_encode(['error' => $conn->error]);
    }
    $stmt->close();
?>
