<?php

// MySQL connection details. Change the password to your MySQL password.
$host = '127.0.0.1';
$username = 'quiz_app';
$password = 'local_db_password';
$database = 'iiitkotaexam';

// Also support the password used in the existing server-start command.
if (getenv('QUIZ_DB_PASSWORD')) {
    $password = getenv('QUIZ_DB_PASSWORD');
}

$db = mysqli_connect($host, $username, $password, $database);
return $db;
