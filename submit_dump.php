<?php
session_start();
include('config.php');

// Only citizens can submit dump reports
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $citizen_id = $_SESSION['user_id'];
    $zone_id = intval($_POST['zone_id']);
    $description = trim($_POST['description']);
    $volume = $_POST['volume'] ?? 'Medium Pile';
    $severity = $_POST['severity'];
    $citizen_lat = !empty($_POST['citizen_lat']) ? floatval($_POST['citizen_lat']) : null;
    $citizen_lng = !empty($_POST['citizen_lng']) ? floatval($_POST['citizen_lng']) : null;
    $image_path = null;

    // Handle image upload
    if (isset($_FILES['dump_image']) && $_FILES['dump_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/dumps/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
        $file_type = $_FILES['dump_image']['type'];
        $file_size = $_FILES['dump_image']['size'];

        if (in_array($file_type, $allowed_types) && $file_size <= 5 * 1024 * 1024) { // 5MB max
            $ext = pathinfo($_FILES['dump_image']['name'], PATHINFO_EXTENSION);
            $filename = 'dump_' . time() . '_' . rand(1000, 9999) . '.' . $ext;
            $target = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['dump_image']['tmp_name'], $target)) {
                $image_path = $target;
            }
        }
    }

    // Handle Voice Report (Base64 data)
    $voice_note_path = null;
    if (!empty($_POST['voice_data'])) {
        $voice_data = $_POST['voice_data'];
        $voice_data = str_replace('data:audio/webm;base64,', '', $voice_data);
        $voice_data = str_replace(' ', '+', $voice_data);
        $audio_content = base64_decode($voice_data);
        
        $upload_dir = 'uploads/voice/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
        
        $filename = 'voice_' . time() . '_' . rand(1000, 9999) . '.webm';
        $voice_note_path = $upload_dir . $filename;
        file_put_contents($voice_note_path, $audio_content);
    }

    // Insert into database
    $sql = "INSERT INTO illegal_dumps (citizen_id, zone_id, volume, description, image_path, voice_note_path, severity, citizen_lat, citizen_lng) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iisssssdd", $citizen_id, $zone_id, $volume, $description, $image_path, $voice_note_path, $severity, $citizen_lat, $citizen_lng);
    
    if ($stmt->execute()) {
        header("Location: dashboard.php?dump_reported=1");
    } else {
        echo "Error: " . $conn->error;
    }
    $stmt->close();
}
?>
