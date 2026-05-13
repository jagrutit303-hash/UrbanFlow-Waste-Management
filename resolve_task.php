<?php
session_start();
include('config.php');

// Only collectors can resolve tasks
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'collector') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $req_id = isset($_POST['request_id']) ? $_POST['request_id'] : null;
    $dump_id = isset($_POST['dump_id']) ? $_POST['dump_id'] : null;
    $driver_id = $_SESSION['user_id'];

    if ($req_id) {
        $check = $conn->prepare("SELECT * FROM fleet_assignments WHERE request_id = ? AND collector_id = ?");
        $check->bind_param("ii", $req_id, $driver_id);
    } else {
        $check = $conn->prepare("SELECT * FROM fleet_assignments WHERE dump_id = ? AND collector_id = ?");
        $check->bind_param("ii", $dump_id, $driver_id);
    }
    
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        if ($req_id) {
            $stmt = $conn->prepare("UPDATE disposal_requests SET status = 'Resolved' WHERE request_id = ?");
            $stmt->bind_param("i", $req_id);
        } else {
            $stmt = $conn->prepare("UPDATE illegal_dumps SET status = 'Resolved' WHERE dump_id = ?");
            $stmt->bind_param("i", $dump_id);
        }

        if ($stmt->execute()) {
            header("Location: driver.php?resolved=success");
        } else {
            echo "Error: " . $conn->error;
        }
        $stmt->close();
    } else {
        header("Location: driver.php");
    }
    $check->close();
}
?>
