-- Create the app database if it is missing.
CREATE DATABASE IF NOT EXISTS iiitkotaexam;

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
) 
