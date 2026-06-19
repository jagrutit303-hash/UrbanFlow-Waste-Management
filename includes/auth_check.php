<?php
/**
 * auth_check.php
 * Handles Vercel's stateless session problem by falling back to cookies.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// VERCEL FIX: Load session from cookie if it was lost by Lambda
if (!isset($_SESSION['user_id']) && isset($_COOKIE['uf_user_id'])) {
    $_SESSION['user_id'] = $_COOKIE['uf_user_id'];
    $_SESSION['user_name'] = $_COOKIE['uf_user_name'];
    $_SESSION['role'] = $_COOKIE['uf_role'];
}

function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

function require_role($role) {
    require_login();
    if ($_SESSION['role'] !== $role) {
        header("Location: login.php");
        exit();
    }
}
?>
