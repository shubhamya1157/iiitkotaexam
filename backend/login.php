<?php

require_once __DIR__ . '/auth.php';

// The login form submits with POST. Send direct visits back to the form.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../frontend/login.html');
    exit;
}

// Use an empty string for missing fields. Trim only the email.
$email = trim((string) ($_POST['email'] ?? ''));
$password = (string) ($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    header('Location: ../frontend/login.html?error=invalid');
    exit;
}

// Connect to MySQL and check the submitted credentials.
try {
    require __DIR__ . '/db.php';
    $user = authenticate($db, $email, $password);
} catch (mysqli_sql_exception | RuntimeException $exception) {
    // Log the technical details on the server and show a simple error.
    error_log('Quiz login database error: ' . $exception->getMessage());
    header('Location: ../frontend/login.html?error=unavailable');
    exit;
}

if ($user === null) {
    header('Location: ../frontend/login.html?error=invalid');
    exit;
}

// Give the logged-in user a fresh session ID and remember their ID and role.
start_app_session();
session_regenerate_id(true);
$_SESSION['user_id'] = $user['id'];
$_SESSION['role'] = $user['role'];

// Choose the dashboard for the role returned by authenticate().
if ($user['role'] === 'admin') {
    $dashboard = '../frontend/admin/dashboard.php';
} else {
    $dashboard = '../frontend/student/dashboard.php';
}

header('Location: ' . $dashboard);
exit;
