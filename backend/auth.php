<?php

// Return the user's ID and role on success, or null if login fails.
function authenticate($db, $email, $password)
{
    // The ? is a placeholder. Bind the email separately as a string ('s').
    $statement = $db->prepare(
        'SELECT id, password, role FROM users WHERE email = ? LIMIT 1'
    );
    $statement->bind_param('s', $email);
    $statement->execute();
    $user = $statement->get_result()->fetch_assoc();
    $statement->close();

    if (!$user) {
        return null;
    }

    // Compare the submitted password with the stored password.
    if ($password !== $user['password']) {
        return null;
    }

    if (!in_array($user['role'], ['admin', 'student'], true)) {
        return null;
    }

    return ['id' => (int) $user['id'], 'role' => $user['role']];
}

// Start a session so PHP can remember who is logged in between requests.
function start_app_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    // Set the session cookie options before starting the session.
    session_set_cookie_params([
        'httponly' => true,
        'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
    ]);
    session_start();
}

// Send visitors to login if their session lacks the required role.
function require_role(string $role, string $loginUrl): void
{
    start_app_session();

    if (
        !isset($_SESSION['user_id'], $_SESSION['role'])
        || (int) $_SESSION['user_id'] < 1
        || $_SESSION['role'] !== $role
    ) {
        header('Location: ' . $loginUrl);
        exit;
    }
}
