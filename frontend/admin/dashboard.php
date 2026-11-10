<?php
require_once __DIR__ . '/../../backend/auth.php';

require_role('admin', '../login.html');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin dashboard | IIIT Kota Exam Platform</title>
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>
    <main class="dashboard-card">
        <p class="eyebrow">Admin dashboard</p>
        <h1>Welcome, administrator</h1>
        <p>You are signed in with an administrator account.</p>
        <form action="../../backend/logout.php" method="post">
            <button type="submit">Log out</button>
        </form>
    </main>
</body>
</html>