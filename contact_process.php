<?php
// Simple contact process to handle the "Have a Question" form
// In a real app, you'd save this to a 'contact_messages' table or email it.

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? 'Citizen';
    // We'll just redirect back with a success flag
    header("Location: about.php?success=1&name=" . urlencode($name));
    exit();
} else {
    header("Location: about.php");
    exit();
}
?>
