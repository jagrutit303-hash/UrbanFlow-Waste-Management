<?php
include('config.php');
session_start();

if (isset($_POST['register'])) {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Prepared Statement for Registration
    $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $pass, $role);
    
    if ($stmt->execute()) {
        header("Location: login.php?registered=success");
    }
    $stmt->close();
}

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    // Prepared Statement for Login
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];

        if ($user['role'] == 'admin') header("Location: admin.php");
        elseif ($user['role'] == 'collector') header("Location: driver.php");
        else header("Location: dashboard.php");
    } else {
        header("Location: login.php?error=invalid");
    }
    $stmt->close();
}
?>