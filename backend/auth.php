<?php

session_start();

// Only allow the required type of user to open a dashboard.
function require_role($role, $loginPage)
{
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] != $role) {
        header('Location: ' . $loginPage);
        exit;
    }
}
