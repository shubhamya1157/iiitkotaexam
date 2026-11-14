<?php

require 'auth.php';

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../frontend/login.html');
    exit;
}

$db = require __DIR__ . '/db.php';

// Read the form and find the user by email.
$email = mysqli_real_escape_string($db, $_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$result = mysqli_query($db, "SELECT id, password, role FROM users WHERE email = '$email'");
$user = mysqli_fetch_assoc($result);

if ($user && $password === $user['password']) {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];

    if ($user['role'] == 'admin') {
        header('Location: ../frontend/admin/dashboard.php');
    } else {
        header('Location: ../frontend/student/dashboard.php');
    }
} else {
    header('Location: ../frontend/login.html?error=invalid');
}
exit;
