UC MAIN INTRAMURALS SYSTEM — SIMPLE PHP + MYSQL + CSS
====================================================

A school-practice project based on the uploaded skills-test sheet.
No Laravel, Bootstrap, or frontend frameworks required.

1) REQUIREMENTS
   - XAMPP (Apache + MySQL)
   - A web browser
   - PHP 8.0+ (XAMPP includes PHP)

2) INSTALL USING XAMPP
   A. Extract this folder into:
         C:\xampp\htdocs\uc-main-intramurals\
   B. Start "Apache" and "MySQL" from XAMPP Control Panel.
   C. Visit http://localhost/phpmyadmin/
   D. Click "Import"; select intramurals.sql and click Go.
      (The SQL file creates the uc_intramurals database.)
   E. Visit:
         http://localhost/uc-main-intramurals/
   F. You will be directed to login. Click Register first.

3) RECOMMENDED TEST ORDER
   - Register as ADMIN with ID 1001, password Test@1234
   - Log in as admin. Department Management: see 5 sample departments.
   - Log out. Register a TOURNAMENT MANAGER using ID 2001.
     Enter full name, mobile and department during registration.
   - Log in as tournament manager. Open Events; create
     "Volleyball Men", category Athletic, limit 10.
   - Log out. Register a COACH (ID 3001), then a DEAN (ID 4001).
   - Log out. Register an ATHLETE/STUDENT (ID 5001).
   - Log in as athlete; open Athletes; fill in the athlete profile.
     Choose the event and optionally the coach and dean.
   - Visit the Menu to view the totals.

   The test credentials above are only examples. Accounts are not
   precreated; you enter the password at registration.

4) THE FILES
   index.php         - Main Menu
   login.php         - Sign in
   register.php      - Create accounts of 5 roles
   logout.php        - Sign out
   departments.php   - Department Add / Edit / Delete (admin)
   events.php        - Event Add / Edit / Delete (manager/admin)
   people.php        - Managers, Coaches, Deans (directory/edit own profile)
   athletes.php      - Athlete details / assigned event
   functions.php     - Database + useful PHP helper functions
   config.php        - MySQL connection settings
   header.php        - Shared title and navigation
   footer.php        - Shared footer
   style.css         - All formatting in one CSS file
   intramurals.sql   - Database + sample departments

5) ROLES
   admin:               Manage departments and events, edit athletes
   tournament_manager: Create and update own events
   coach/dean:          View directory and athletes, edit own staff profile
   athlete:             Register or update own athlete profile

6) SCHEMA NOTES
   Tables match the provided sheet: Registration, Department,
   TournamentManager, Coach, Dean, Event, AthletesProfile.

   "confirmPassword" from the sheet is checked during registration,
   but is NOT stored in the database (password safety).
   Passwords are stored using PHP password_hash(), not plain text.
   To keep the activity simple, AthletesProfile.idNum is the primary
   key: each athlete has ONE assigned event in this version.
   For multiple events per athlete, the database design would need a
   separate athlete-event enrollment table.

7) COMMON ERRORS
   "Unknown database"       -> Import intramurals.sql first.
   "Access denied for root" -> Update config.php password.
   "could not find driver"   -> Enable pdo_mysql in PHP/XAMPP.
   "Connection refused"     -> Start MySQL in XAMPP.
   Blank page               -> Check PHP error log in XAMPP.
   If your folder has a different name, edit the browser URL.

8) IMPORTANT NOTE
   This is a LOCAL SCHOOL DEMO, not a production application.
   Anyone can select the admin role during self-registration so
   teachers can quickly test each role. In real deployments, admin
   accounts must be created by an already-authorized administrator.
   Protect account creation, phone numbers and athlete data before
   using this on a public website. Use HTTPS and proper permissions.
