<?php
/**
 * Database Configuration
 */
$host = getenv('DB_HOST') ?: "127.0.0.1";
$user = getenv('DB_USER') ?: "root";
$pass = getenv('DB_PASS') ?: "";
$db   = getenv('DB_NAME') ?: "urbanflow_db";
$port = getenv('DB_PORT') ?: 3306; // Default MySQL port

// Use the 5th parameter for the port
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

/**
 * API Security
 * We use getenv here so your Hugging Face token is never exposed in the code
 */
$hf_api_token = getenv('HF_TOKEN') ?: ""; 
?>