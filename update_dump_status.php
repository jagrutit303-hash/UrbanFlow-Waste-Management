<?php
session_start();
include('config.php');

// Admin only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $dump_id = intval($_POST['dump_id']);
    $new_status = $_POST['new_status'];

    $allowed = ['Reported', 'Under Review', 'Cleanup Dispatched', 'Resolved'];
    if (in_array($new_status, $allowed)) {
        $stmt = $conn->prepare("UPDATE illegal_dumps SET status = ? WHERE dump_id = ?");
        $stmt->bind_param("si", $new_status, $dump_id);
        
        if ($stmt->execute()) {
            header("Location: admin.php?dump_updated=success");
        } else {
            echo "Error: " . $conn->error;
        }
        $stmt->close();
    } else {
        header("Location: admin.php");
    }
}
?>
