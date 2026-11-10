# IIIT Kota Exam

A web-based exam application built using **HTML, CSS, JavaScript, PHP, and MySQL**.

- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Database connection:** MySQLi
- **Database management:** phpMyAdmin

## Folder Structure

```text
iiitkotaexam-copy/
│
├── backend/
│   ├── auth.php              # Authentication, sessions and roles
│   ├── db.php                # MySQL database connection
│   ├── exam.php              # Exam functionality (under development)
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
cd "/Users/shubham/Desktop/iiitkotaexam copy"
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
| `/backend/exam.php` | Exam functionality (under development) |
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


