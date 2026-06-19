<?php
require_once(__DIR__ . '/includes/auth_check.php');
require_login();
include('config.php');

header('Content-Type: application/json');

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

    if ($user_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID.']);
        exit();
    }

    // Prevent admin from deleting themselves
    if ($user_id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'You cannot remove your own account.']);
        exit();
    }

    // Delete user from database
    // Note: Database foreign keys are set to ON DELETE CASCADE, 
    // so related data in other tables will be deleted automatically.
    $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'User removed successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found or already removed.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>

