<?php
// Authentication helper

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
function checkUserLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit();
    }
}

// Check if admin is logged in
function checkAdminLogin() {
    if (!isset($_SESSION['admin_id'])) {
        header("Location: ../admin/index.php");
        exit();
    }
}
?>