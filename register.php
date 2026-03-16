<?php
include("includes/db.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users 
        (username, email, password, weight, height, chest, waist, arms, legs, bmi, created_at) 
        VALUES (?, ?, ?, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NOW())");
    $stmt->bind_param("sss", $username, $email, $password);

    if ($stmt->execute()) {
        header("Location: login.php?msg=Registered successfully, please login");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Register - GYMgeekS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <div class="container py-5" data-reveal>
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="text-dark mb-0">Create your GYMgeekS account</h2>
        </div>
        <div class="card p-4 shadow mx-auto" style="max-width:500px;">
            <form method="POST" class="needs-validation" novalidate>
                <div class="mb-3">
                    <label class="form-label text-dark">Username</label>
                    <input type="text" name="username" class="form-control" required>
                    <div class="invalid-feedback">Please choose a username.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-dark">Email</label>
                    <input type="email" name="email" class="form-control" required>
                    <div class="invalid-feedback">Please enter a valid email address.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label text-dark">Password</label>
                    <input type="password" name="password" class="form-control" required>
                    <div class="invalid-feedback">Please set a password.</div>
                </div>
                <button type="submit" class="btn btn-primary w-100">Create account</button>
            </form>
            <hr class="border-secondary">
            <p class="text-center text-muted">Already have an account?</p>
            <a href="index.php" class="btn btn-outline-primary w-100">Login</a>
        </div>
    </div>
    <script src="assets/js/site.js"></script>
    <script>
        (function () {
            'use strict'
            const forms = document.querySelectorAll('.needs-validation')
            Array.from(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }
                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>

</html>