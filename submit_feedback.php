<?php
require_once(__DIR__ . '/includes/auth_check.php');
require_login();
include('config.php');
require_once('includes/cloudinary.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $request_id = intval($_POST['request_id']);
    $rating     = intval($_POST['rating']);
    $comment    = trim($_POST['comment']);
    $uid        = $_SESSION['user_id'];

    // Validate: ensure the request_id exists and belongs to this user
    $check = $conn->prepare("SELECT request_id FROM disposal_requests WHERE request_id = ? AND citizen_id = ?");
    $check->bind_param("ii", $request_id, $uid);
    $check->execute();
    $check->store_result();

    if ($check->num_rows === 0) {
        header("Location: feedback.php?error=invalid_request");
        exit();
    }
    $check->close();

    // Validate rating range
    if ($rating < 1 || $rating > 5) {
        header("Location: feedback.php?error=invalid_rating");
        exit();
    }

    // --- Handle Voice Feedback (Base64) via Cloudinary ---
    $voice_feedback_path = null;
    if (!empty($_POST['voice_feedback_data'])) {
        $voice_data    = $_POST['voice_feedback_data'];
        
        $cloudinary_url = uploadBinaryToCloudinary(base64_decode(str_replace(['data:audio/webm;base64,', ' '], ['', '+'], $voice_data)), 'fb_voice.webm', 'urbanflow/feedback_audio');

        if ($cloudinary_url) {
            $voice_feedback_path = $cloudinary_url;
        } else {
            // Vercel Serverless fallback: store Base64 directly
            $voice_feedback_path = $voice_data;
        }
    }

    // Insert feedback using prepared statement
    $stmt = $conn->prepare("INSERT INTO feedback (request_id, rating, comments, voice_feedback_path) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $request_id, $rating, $comment, $voice_feedback_path);

    if ($stmt->execute()) {
        header("Location: dashboard.php?feedback=success");
    } else {
        header("Location: feedback.php?error=db_error");
    }
    $stmt->close();
    $conn->close();
}
?>
