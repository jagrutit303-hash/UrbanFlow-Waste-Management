<?php
require_once(__DIR__ . '/includes/auth_check.php');
require_login();
include('config.php');

// Ensure user is authenticated and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $req_id = isset($_POST['request_id']) ? $_POST['request_id'] : null;
    $dump_id = isset($_POST['dump_id']) ? $_POST['dump_id'] : null;
    
    // Static values for the project demo
    $collector_id = 2; 
    $vehicle = "KA-17-EV-2026";

    $success = false;

    if ($req_id) {
        // Handle Disposal Request Dispatch
        $stmt1 = $conn->prepare("UPDATE disposal_requests SET status = 'Dispatched' WHERE request_id = ?");
        $stmt1->bind_param("i", $req_id);
        
        $stmt2 = $conn->prepare("INSERT INTO fleet_assignments (request_id, collector_id, vehicle_no) VALUES (?, ?, ?)");
        $stmt2->bind_param("iis", $req_id, $collector_id, $vehicle);
        
        if ($stmt1->execute() && $stmt2->execute()) $success = true;
        $stmt1->close();
        $stmt2->close();
    } elseif ($dump_id) {
        // Handle Illegal Dump Dispatch
        $stmt1 = $conn->prepare("UPDATE illegal_dumps SET status = 'Cleanup Dispatched' WHERE dump_id = ?");
        $stmt1->bind_param("i", $dump_id);
        
        $stmt2 = $conn->prepare("INSERT INTO fleet_assignments (dump_id, collector_id, vehicle_no) VALUES (?, ?, ?)");
        $stmt2->bind_param("iis", $dump_id, $collector_id, $vehicle);
        
        if ($stmt1->execute() && $stmt2->execute()) $success = true;
        $stmt1->close();
        $stmt2->close();
    }

    if ($success) {
        header("Location: admin.php?dispatched=success");
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
