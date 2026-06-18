<?php
/**
 * auth_action.php — Handles Login & Registration
 * 
 * session_start() MUST be the very first thing, before any include
 * that might output HTML (like header.php).
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If already logged in, redirect immediately
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === 'admin') {
        header("Location: admin.php");
    } elseif ($_SESSION['role'] === 'collector') {
        header("Location: driver.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

// Load DB — config.php loads env vars but does NOT output HTML
include('config.php');

// ── REGISTRATION ─────────────────────────────────────────────
if (isset($_POST['register'])) {
    $name  = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role  = $_POST['role'];

    // Check if email already exists
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $check->close();
        header("Location: register.php?error=email_taken");
        exit();
    }
    $check->close();

    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $pass, $role);

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: login.php?registered=success");
        exit();
    } else {
        $stmt->close();
        $conn->close();
        header("Location: register.php?error=db_error");
        exit();
    }
}

// ── LOGIN ─────────────────────────────────────────────────────
if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $pass  = $_POST['password'];

    $stmt = $conn->prepare("SELECT user_id, full_name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user   = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    if ($user && password_verify($pass, $user['password'])) {
        // Regenerate session ID to prevent session fixation attacks
        session_regenerate_id(true);

        $_SESSION['user_id']   = $user['user_id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['role']      = $user['role'];

        if ($user['role'] === 'admin') {
            header("Location: admin.php");
        } elseif ($user['role'] === 'collector') {
            header("Location: driver.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    } else {
        header("Location: login.php?error=invalid");
        exit();
    }
}

// If somehow reached here without POST data, go home
header("Location: index.php");
exit();
?>