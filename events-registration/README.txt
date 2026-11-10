SIMPLE EVENTS REGISTRATION SYSTEM (PHP + MYSQL + VANILLA CSS)
=============================================================
Based on the UC Main Events Registration skills-test image.
No frameworks, no Composer, and no third-party packages required.

HOW TO INSTALL ON WINDOWS USING XAMPP
-------------------------------------
1. Start Apache and MySQL in the XAMPP Control Panel.
2. Extract the events-registration folder into:
       C:\xampp\htdocs\events-registration
3. In a browser visit:
       http://localhost/phpmyadmin/
4. Click the Import tab, choose events_registration.sql, then click Import.
   This SQL file creates the events_registration database and sample records.
5. Visit:
       http://localhost/events-registration/

If MySQL uses a different username/password, update config.php.
Default XAMPP values: username = root, password = (blank).
If port 80 was changed in Apache, add your Apache port to localhost.

FILES AND FEATURES
------------------
index.php             Main menu and record counts
config.php            MySQL connection settings
functions.php         Basic helpers, protection, and messages
header.php            Common page header/navigation
footer.php            Common footer
events.php            Add/search/edit/delete/view event records
participants.php      Add/search/edit/delete/view participant records
registration.php      Register participant, auto-compute fee, latest-first transactions
monitoring.php        Filter by event name; four columns; SQL COUNT/SUM and discount total
style.css             Vanilla CSS; no libraries
events_registration.sql  MySQL tables and example data

DATABASE TABLES (EXACT NAMES FROM PAPER)
----------------------------------------
Events:       evCode, evName, evDate, evVenue, evRFee
Participants: partID, evCode, partFName, partLName, partDRate
Registration: regCode, partID, regDate, regFPaid, regPMode

FEE CALCULATION IN YOUR PAPER
-----------------------------
regFPaid = evRFee - partDRate

Example:
Event fee = P500.00
Participant discount = P100.00
Fee paid = P400.00

The test paper calls partDRate a "discount rate" but also directly subtracts
it from the fee. To match the required formula, the app interprets it as a
peso discount AMOUNT (not a percent).

MONITORING FOR ONE EVENT
------------------------
1. COUNT(*) = number of registrations.
2. SUM(regFPaid) = total registration fees paid.
3. (COUNT(*) * evRFee) - SUM(regFPaid) = total amount of discounts.

The report selects one event by name (via the event's unique code).
Four required columns: Event Name, Participant Full Name, Registration Date,
Registration Fee Paid.

NOTE ON DUPLICATE PARTICIPANTS
------------------------------
Each participant is assigned one event, matching the Participants table in
the supplied worksheet. Each participant can register only once.
To register the same person in another event, create another participant record.

TESTING EXAMPLE
---------------
1. Open Events Management and try adding a new event.
2. Open Participants Management and add a person assigned to that event.
3. Open Participant Registration, enter the Participant ID, choose a date,
   choose Card or Cash, and click Register Participant.
4. Open Registration Monitoring, select that event, and click Filter Records.

Existing seed example: Participant ID 1001 is already registered.
Participant ID 1003 is unregistered, so you can use 1003 for a test transaction.
For Web Development Workshop before registering 1003:
  registrations = 2
  total fees paid = P850.00
  total discounts = P150.00

DATA CONSISTENCY NOTES
----------------------
To keep the monitoring totals accurate, once a registration exists:
- changing that event's registration fee is blocked;
- changing that participant's event or discount is blocked.
Delete the corresponding registration first if those values must change.
Deleting events/participants with dependent records is also blocked.

CLASSROOM / LOCALHOST USE ONLY
------------------------------
The worksheet says "Local Admin", so this project uses a local-admin menu
instead of a full login/authentication system. Do not expose it publicly
without implementing proper accounts, roles, and security hardening.
