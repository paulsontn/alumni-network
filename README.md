# Official Alumni Network — University Graduate Portal

A full-stack web application built with **HTML5, CSS3, JavaScript, PHP, and MySQL** that connects graduates, showcases verified career opportunities, manages alumni summits, and provides an executive administration dashboard.

## Key Features
- **Secure Authentication & Session Control:** Member registration (`signup.php`) and sign-in (`login.php`) using `password_hash()`, prepared MySQLi statements, and full session destruction on logout (`logout.php`).
- **Global Alumni Directory:** Real-time search filter (`index.php`) combining featured alumni and verified database graduates with an interactive profile modal.
- **Executive Member Profiles:** Logged-in alumni can update their institution, current role, executive biography, and upload a custom profile photograph.
- **Events & Career Board:** Dynamic job listings and upcoming alumni reunions (`alumni.php`) allowing members to announce events and share career openings.
- **Alumni Relations Hub & Admin Portal:** Two-column engagement hub with categorized inquiries saved to MySQL and managed via a password-protected Admin Dashboard (`admin.php`).

## Tech Stack
- **Frontend:** HTML5, CSS3, Vanilla JavaScript
- **Backend:** PHP 8+ (Procedural MySQLi with Prepared Statements)
- **Database:** MySQL (`alumni_db`)

## Local Setup (XAMPP + MySQL)
1. **Clone the repository** into `C:\xampp\htdocs\alumni\`.
2. **Import the database:** Import `alumni_db.sql` into MySQL Workbench or phpMyAdmin to create `alumni_db` (`users`, `messages`, `events`, `jobs`).
3. **Configure credentials:** Rename `db.example.php` to `db.php` and add your local MySQL password.
4. **Launch:** Start Apache & MySQL in XAMPP and visit `http://localhost/alumni/index.php`.