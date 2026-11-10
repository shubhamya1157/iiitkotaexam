<?php

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

// Read the MySQL account password from the environment.
$dbPassword = getenv('QUIZ_DB_PASSWORD');
if ($dbPassword === false || $dbPassword === '') {
    throw new RuntimeException('Set QUIZ_DB_PASSWORD before starting PHP.');
}

// Connect using the host, username, password, database name, and port.
$db = new mysqli('127.0.0.1', 'quiz_app', $dbPassword, 'iiitkotaexam', 3306);
$db->set_charset('utf8mb4');
