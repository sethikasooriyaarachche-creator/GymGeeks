<?php
session_start();
include("includes/db.php");

// Only run if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        header("Location: index.php?error=empty");
        exit();
    }

    // Check Admins
    $stmt = $conn->prepare("SELECT * FROM admins WHERE username=? OR email=?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['role'] = "admin";
        header("Location: admin/dashboard.php");
        exit();
    }

    // Check Users
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=? OR email=?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['role'] = "user";
        header("Location: members.php");
        exit();
    }

    // Invalid credentials
    header("Location: index.php?error=invalid");
    exit();
} else {
    // If accessed directly without POST
    header("Location: index.php");
    exit();
}