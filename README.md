# Student Portal (PHP + PDO + MySQL)

## Run it (XAMPP)
1. Copy the `student_system` folder to `C:\xampp\htdocs\`.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Open `http://localhost/student_system/`.
4. Click **Register**, use registration number `25RP14889`, then log in.

The database `practice_db` and all tables (`users`, `students`, `activity_log`) are created
automatically by `config.php` through PDO, and 10 sample students are inserted.
`database.sql` is an optional manual import.

If your MySQL has a root password, change `DB_PASS` in `config.php`.

## Structure
| File | Purpose |
|---|---|
| `config.php` | PDO connection, auto-install of DB/tables, helpers |
| `register.php`, `login.php`, `logout.php` | Authentication |
| `dashboard.php` | Dashboard with photo + registration number |
| `profile.php` | Edit details, upload/change photo, change password |
| `students.php` | CRUD (insert, fetch, update, delete) |
| `task1`–`task5_*.php` | Assignment pages linked in the left panel |
| `includes/header.php` | Left panel (sidebar) + top bar |
"# students_system" 
