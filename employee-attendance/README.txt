EMPLOYEE ATTENDANCE MONITORING SYSTEM
Plain PHP + MySQL + CSS / No framework
=======================================

HOW TO RUN WITH XAMPP (Windows)

1. Extract the ZIP file.
2. Copy the "employee-attendance" folder to:
       C:\xampp\htdocs\employee-attendance
   Be careful not to create an extra nested folder.
3. Open XAMPP Control Panel.
4. Click Start beside Apache and MySQL.
5. Open http://localhost/phpmyadmin/
6. Select the "Import" tab and choose attendance.sql.
   Click "Import" (or "Go"). The script automatically creates
   the database attendance_db, three tables, and sample records.
7. Open:
       http://localhost/employee-attendance/

If MySQL has a password, change $username and $password at the TOP
of functions.php. The default XAMPP setup uses username root and
an empty password for local development.

IMPORTANT: Importing attendance.sql again will DELETE the existing
attendance_db table data and restore the sample records.

FILES
-----
index.php             Main menu
functions.php         PDO database connection and common functions
style.css             Styling for all pages
departments.php       Add, edit, delete and list departments
employees.php         Add, edit, delete and list employees
attendance.php        Record time in, time out, and cancel attendance
monitor_employee.php  View employee hours and estimated salary
monitor_dates.php     Filter by time-in dates and total hours
attendance.sql        Three database tables with test data
README.txt            These instructions

SAMPLE DATA TO TEST
-------------------
By Employee: choose Employee 234 (Basabe, Neil).
Expected total: 54 hours, estimated pay: PHP 27,000.00.
By Date Range: select From 2024-04-15, To 2024-04-20.
Expected total: 6 records and 54 hours.

WORKFLOW
--------
1. Create a department.
2. Add an employee assigned to that department.
3. Record their attendance with Time In and optionally Time Out.
4. If Time Out was omitted, click "Time Out Now" on the record later.
5. Click "Cancel" to mark an attendance record as cancelled.
6. Use the two monitoring pages to calculate completed work hours.

BEGINNER NOTES
--------------
- $_POST handles submitted forms (add/update/delete actions).
- $_GET handles links (edit links and report filters).
- PDO connects PHP to MySQL and prepared statements protect SQL queries.
- h() escapes database text when displayed in HTML.
- The app calculates hours as (Time Out - Time In) / 3600.
- Sample salary = completed hours * current employee rate per hour.
  This is an example calculation, not full payroll accounting.
- Cancelled attendance is hidden from reports, but preserved in the database.
- Database foreign keys stop the deletion of linked departments and employees.

LIMITATIONS
-----------
- For LOCAL classroom practice only. "Local Admin" is a label, not a
  secure login system; this project does not implement authentication.
- There is no CSRF protection, authorization, or production hardening.
- This is a straightforward training exercise, NOT production payroll.
- Hours include the entire time between clock-in/out; no breaks, overtime,
  holiday pay, overnight attendance policy, or historical rate changes.
- This uses Asia/Manila as the PHP timezone.
- Make sure Apache and MySQL are running in XAMPP.
