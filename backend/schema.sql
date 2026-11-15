-- Create the app database if it is missing.
CREATE DATABASE IF NOT EXISTS iiitkotaexam;


-- Local development accounts. Import this file as MySQL root or an administrator.
CREATE USER IF NOT EXISTS 'quiz_app'@'127.0.0.1'
IDENTIFIED BY 'local_db_password';

GRANT SELECT ON iiitkotaexam.users
TO 'quiz_app'@'127.0.0.1';

GRANT SELECT, INSERT ON iiitkotaexam.quizzes
TO 'quiz_app'@'127.0.0.1';

GRANT SELECT, INSERT ON iiitkotaexam.quiz_attempts
TO 'quiz_app'@'127.0.0.1';

-- Run the statements below in this database.
USE iiitkotaexam;



-- Each row in this table represents one user account.
CREATE TABLE IF NOT EXISTS users (
    -- MySQL automatically assigns the next ID to each new user.
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    email VARCHAR(254) NOT NULL,
    -- Store the password as plain text.
    password VARCHAR(255) NOT NULL,
    -- A user can be an admin or a student. New users default to student.
    role ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    -- MySQL records the time when the account is created.
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

    -- The ID identifies the account, and the email must be unique.
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
);
CREATE TABLE IF NOT EXISTS quizzes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(150) NOT NULL,
    opens_at BIGINT NOT NULL,
    closes_at BIGINT NOT NULL,
    questions JSON NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS quiz_attempts (
    quiz_id INT UNSIGNED NOT NULL,
    student_id BIGINT UNSIGNED NOT NULL,
    score SMALLINT UNSIGNED NOT NULL,
    submitted_at BIGINT NOT NULL,
    PRIMARY KEY (quiz_id, student_id),
    FOREIGN KEY (quiz_id) REFERENCES quizzes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;



-- Add demo website logins; keep existing accounts unchanged on reimport.
INSERT INTO users (email, password, role)
VALUES
    ('student@example.com', 'demo123', 'student'),
    ('admin@example.com', 'demo123', 'admin')
ON DUPLICATE KEY UPDATE email = VALUES(email);
