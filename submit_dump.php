<?php
require_once(__DIR__ . '/includes/auth_check.php');
require_login();
include('config.php');
require_once('includes/cloudinary.php');

// Only citizens can submit dump reports
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $citizen_id  = $_SESSION['user_id'];
    $zone_id     = intval($_POST['zone_id']);
    $description = trim($_POST['description']);
    $volume      = $_POST['volume'] ?? 'Medium Pile';
    $severity    = $_POST['severity'];
    $citizen_lat = !empty($_POST['citizen_lat']) ? floatval($_POST['citizen_lat']) : null;
    $citizen_lng = !empty($_POST['citizen_lng']) ? floatval($_POST['citizen_lng']) : null;
    $image_path  = null;

    // --- Handle image upload via Cloudinary ---
    if (isset($_FILES['dump_image']) && $_FILES['dump_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $file_type     = $_FILES['dump_image']['type'];
        $file_size     = $_FILES['dump_image']['size'];

        if (in_array($file_type, $allowed_types) && $file_size <= 5 * 1024 * 1024) {
            // Upload directly from the temp file Vercel/PHP created
            $cloudinary_url = uploadImageToCloudinary($_FILES['dump_image']['tmp_name'], 'urbanflow/dumps');
            if ($cloudinary_url) {
                $image_path = $cloudinary_url;
            } else {
                // Fallback: Store as Base64 Data URI in DB directly (Vercel Serverless safe)
                $type = pathinfo($_FILES['dump_image']['name'], PATHINFO_EXTENSION);
                $data = file_get_contents($_FILES['dump_image']['tmp_name']);
                $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                $image_path = $base64;
            }
        }
    }

    // --- Handle Voice Report (Base64 data) via Cloudinary ---
    $voice_note_path = null;
    if (!empty($_POST['voice_data'])) {
        $voice_data   = $_POST['voice_data'];
        
        // Vercel Serverless safe fallback: just store the Base64 data directly!
        $cloudinary_url  = uploadBinaryToCloudinary(base64_decode(str_replace(['data:audio/webm;base64,', ' '], ['', '+'], $voice_data)), 'voice.webm', 'urbanflow/audio');

        if ($cloudinary_url) {
            $voice_note_path = $cloudinary_url;
        } else {
            $voice_note_path = $voice_data; // Store the original base64 payload
        }
    }

    // --- Insert into database ---
    $sql  = "INSERT INTO illegal_dumps (citizen_id, zone_id, volume, description, image_path, voice_note_path, severity, citizen_lat, citizen_lng) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssssdd", $citizen_id, $zone_id, $volume, $description, $image_path, $voice_note_path, $severity, $citizen_lat, $citizen_lng);

    if ($stmt->execute()) {
        header("Location: dashboard.php?dump_reported=1");
    } else {
        header("Location: report_dump.php?error=db_error");
    }
    $stmt->close();
    $conn->close();
}
?>
