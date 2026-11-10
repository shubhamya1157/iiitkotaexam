# IIIT Kota Exam

A web-based exam application built using **HTML, CSS, JavaScript, PHP, and MySQL**.

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Database connection:** MySQLi
- **Database management:** phpMyAdmin

## Folder Structure

```text
iiitkotaexam/
│
├── backend/
│   ├── auth.php              # Authentication, sessions and roles
│   ├── db.php                # MySQL database connection
│   ├── exam.php              # Quiz scheduling and grading helpers
│   ├── login.php             # Handles login form submissions
│   ├── logout.php            # Ends the user session
│   └── schema.sql            # Database and table creation
│
├── frontend/
│   ├── admin/
│   │   └── dashboard.php     # Admin dashboard
│   │
│   ├── student/
│   │   └── dashboard.php     # Student dashboard
│   │
│   ├── css/
│   │   └── index.css
│   │
│   ├── index.html
│   └── login.html
│
├── .env
├── .gitignore
└── README.md
```

## Database Schema

The MySQL database is named `iiitkotaexam`. The importable schema is in [backend/schema.sql](backend/schema.sql).

| Table | Purpose |
| --- | --- |
| `users` | Stores admin and student accounts with unique email addresses. |
| `quizzes` | Stores quiz titles, schedules, and multiple-choice questions as JSON. |
| `quiz_attempts` | Stores each student's score and submission time. The composite primary key allows one attempt per quiz per student. |

`quiz_attempts.quiz_id` references `quizzes.id`. The application uses `users.id` as `quiz_attempts.student_id`. The quiz opening, closing, and submission times are stored as Unix timestamps in seconds.

```sql
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

# How to Run Locally (macOS)

## 1. Install Prerequisites

We use Homebrew to install the required software.

### 1.1 Install Homebrew (if not already installed)

Visit:

https://brew.sh/

Follow the installation instructions.

Verify installation:

```bash
brew --version
```

### 1.2 Install PHP, MySQL and phpMyAdmin

Run:

```bash
brew install php
brew install mysql
brew install phpmyadmin
```

These commands install:

| Software | Purpose |
|---|---|
| PHP | Executes backend PHP code |
| MySQL | Stores application data |
| phpMyAdmin | Provides a graphical interface to manage MySQL |

### 1.3 Verify installations

Check PHP:

```bash
php -v
```

Check MySQL:

```bash
mysql --version
```

Check MySQLi extension:

```bash
php -m | grep -i mysqli
```

Expected output:

```text
mysqli
```

## 2. Start MySQL

Start MySQL using Homebrew:

```bash
brew services start mysql
```

Check whether MySQL is running:

```bash
brew services list
```

Check whether MySQL is listening on port `3306`:

```bash
sudo lsof -nP -iTCP:3306 -sTCP:LISTEN
```

Connect to MySQL:

```bash
mysql -u root -p
```

Enter your MySQL root password if one has been configured.

If you have a fresh Homebrew installation and have not configured a root password, try:

```bash
mysql -u root
```

## 3. Create the Database

There are two ways to import the database schema.

### Option A: Using MySQL command line

Open your project directory:

```bash
cd "/Users/shubham/Desktop/iiitkotaexam"
```

Connect to MySQL:

```bash
mysql -u root -p
```

At the `mysql>` prompt, execute:

```sql
SOURCE backend/schema.sql;
```

This executes the SQL instructions inside `schema.sql`.

### Option B: Using phpMyAdmin

Start phpMyAdmin in a separate terminal:

```bash
php -S 127.0.0.1:8080 -t "$(brew --prefix)/share/phpmyadmin"
```

Open:

http://127.0.0.1:8080/

1. Sign in using your MySQL admin credentials.
2. Select the Import tab.
3. Choose `backend/schema.sql`.
4. Click Import.

Keep this terminal open while using phpMyAdmin.

## 4. Create the Application Database User

In the MySQL command line or phpMyAdmin SQL tab, execute:

```sql
CREATE USER IF NOT EXISTS 'quiz_app'@'127.0.0.1'
IDENTIFIED BY 'local_db_password';

GRANT SELECT ON iiitkotaexam.users
TO 'quiz_app'@'127.0.0.1';

GRANT SELECT, INSERT ON iiitkotaexam.quizzes
TO 'quiz_app'@'127.0.0.1';

GRANT SELECT, INSERT ON iiitkotaexam.quiz_attempts
TO 'quiz_app'@'127.0.0.1';

INSERT INTO iiitkotaexam.users (email, password, role)
VALUES ('student@example.com', 'demo123', 'student');
```

This creates a dedicated MySQL user for the PHP application and inserts a test student.

**Important:**

- If `quiz_app` already exists, use its existing password.
- Insert the test student only once because email addresses must be unique.
- This example assumes the current database uses a `password` column.
- The example uses plain-text passwords for development only.

## 5. Start the PHP Development Server

Open a new terminal in the project root directory.

Execute:

```bash
QUIZ_DB_PASSWORD='local_db_password' php -S 127.0.0.1:8000
```

Expected output:

```text
PHP Development Server started
http://127.0.0.1:8000
```

Keep this terminal open while using the application.

The PHP development server serves both frontend files and backend PHP scripts.

# Application URLs and Endpoints

The PHP server runs on port `8000`.

## Frontend Pages

| Page | URL |
|---|---|
| Homepage | http://127.0.0.1:8000/frontend/index.html |
| Login page | http://127.0.0.1:8000/frontend/login.html |
| Admin dashboard | http://127.0.0.1:8000/frontend/admin/dashboard.php |
| Student dashboard | http://127.0.0.1:8000/frontend/student/dashboard.php |

## Backend PHP Endpoints

| Endpoint | Purpose |
|---|---|
| `/backend/login.php` | Handles login form POST requests |
| `/backend/logout.php` | Logs out the current user |
| `/backend/exam.php` | Quiz scheduling and grading helpers |
| `/backend/auth.php` | Authentication and session helpers |
| `/backend/db.php` | Database connection |

The browser sends the form data to PHP, PHP processes the login request, and PHP communicates with MySQL using MySQLi.

**Note:** `auth.php` and `db.php` are internal PHP files. They should not be exposed as public application pages.

# Ports Used

| Service | Port | Purpose |
|---|---:|---|
| MySQL | 3306 | Database server |
| PHP | 8000 | Frontend and backend server |
| phpMyAdmin | 8080 | Database management interface |

# Test Login Credentials

```text
Email: student@example.com
Password: demo123
```

These are development-only credentials.

## Quizzes

Sign in as an admin to create a multiple-choice quiz and schedule its opening and closing times in IST. All student accounts can submit one attempt during that window. Students see their scores after submission, and admins can view submissions on their dashboard.

For an existing database, reimport the schema and apply the quiz table grants above. PHP 8.2 or newer with MySQLi and mbstring is required.
