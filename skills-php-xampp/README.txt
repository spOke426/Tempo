STUDENT MANAGEMENT SYSTEM - SIMPLE PHP + VANILLA CSS + MYSQL
===========================================================

ABOUT
-----
Converted from the uploaded Flask + SQLite "SCHOOL v1.0" project.
The original features were: login, logout, add/edit/delete/list students.
The new version also includes a basic search and safer PHP database queries.

HOW TO USE WITH XAMPP (WINDOWS)
------------------------------
1. Unzip this project. The folder should be named "skills-php-xampp".
2. Put the entire folder in:
   C:\xampp\htdocs\skills-php-xampp\
3. Open XAMPP Control Panel; click START for Apache and MySQL.
4. In your browser, open http://localhost/phpmyadmin/
5. Click the Import tab, choose "database.sql" from this folder,
   and click Import/Go. (The SQL creates database skills_php.)
6. Open http://localhost/skills-php-xampp/
7. Login using any of the following demo accounts:

   Email: alpha@uc.com     Password: hello123
   Email: bravo@uc.com     Password: hello123
   Email: charlie@uc.com   Password: hello123

FILES
-----
index.php          Login page
 dashboard.php     Student list + add/edit form + search
 save_student.php  Insert or update student in MySQL
 delete_student.php Delete student (via POST)
 logout.php        Logout
 config.php        MySQL connection settings
 functions.php     Simple helpers, session and form protection
 header.php        Page heading/navigation
 footer.php        Page footer
 style.css         Pure CSS (no CSS frameworks)
 database.sql      Database schema + original sample data

DATABASE
--------
Name: skills_php
Tables:
  users    (id, email, password - hashed)
  students (id, idno, lastname, firstname, course, level)

This includes the 3 original demo user accounts and 4 student records from
skills.db in the uploaded project. Original demo passwords were migrated
into password hashes rather than stored as plain text.

IF MYSQL PASSWORD IS NOT BLANK
-----------------------------
Open config.php and update $username or $password to match your MySQL setup.
The normal new XAMPP installation uses user "root" with an empty password.

IMPORTANT NOTES
---------------
* PHP 8.0 or later with the pdo_mysql extension is recommended.
* This is a learning/demo app for local XAMPP use only.
* Delete or change the demo accounts/passwords before deploying online.
* Unlike the old Flask version, deleting students uses a POST form.
* The ID Number is stored as text so leading zeroes are preserved.
* The internal "id" is the database primary key used when editing/deleting.
* No Python, virtualenv, Flask, SQLite or W3.CSS is needed.
