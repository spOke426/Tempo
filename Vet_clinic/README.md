# Veterinary Clinic Consultations System — Process & Code Reviewer

A beginner-friendly explanation of the uploaded **YourFamilyName** veterinary clinic PHP project.

## 1. What this system does

The clinic registers veterinarians, pet owners and pets, records consultations, and provides inquiries filtered by specialization, pet, owner, veterinarian or consultation dates.

**Technology:** plain PHP, MySQL, HTML and vanilla CSS. No framework is required.

## 2. XAMPP setup (read this first)

1. Install a XAMPP version with **PHP 8.2 or newer**. The code uses `$conn->execute_query()`, introduced in PHP 8.2.
2. Open XAMPP Control Panel; start **Apache** and **MySQL**.
3. Place this project at `C:\xampp\htdocs\clinic-system\` (`index.php` should be directly inside this folder).
4. Visit `http://localhost/phpmyadmin/`, open **Import**, and select `Clinic.sql`.
5. The import creates the database `clinic`, four tables, foreign keys, and sample data.
6. Visit `http://localhost/clinic-system/` to open the main menu.
7. If your MySQL username/password differs from XAMPP's default `root` / empty password, edit `db.php`.

> Important: The other uploaded Voting ZIP also had a folder named `YourFamilyName`. Use separate folders in `htdocs` for each project.

## 3. Files and responsibilities

| File | Purpose |
| --- | --- |
| `Clinic.sql` | Create database, related tables and sample data |
| `db.php` | Connect to MySQL; define `run()` and `h()` helper functions |
| `index.php` | Main menu |
| `veterinarian.php` | Add, edit, search and logically delete veterinarian records |
| `petowner.php` | Add, edit, search and logically delete pet owners |
| `pet.php` | Add, edit, search and logically delete pets; choose an owner |
| `consultation.php` | Add, edit, search, logically delete consultations; choose pet and vet |
| `inquiry.php` | Five lookup/report options, using joins and counts |
| `header.php`, `footer.php` | Navigation and shared HTML layout |
| `style.css` | Basic colors, spacing, forms and table styles |

## 4. Database tables and relationships

### `PetOwner`
- `petOwnerID` — primary key, user-entered text (e.g. `OWN-001`).
- `petOwnerFName`, `petOwnerLName`, `petOwnerBDate`, `petOwnerTelNo`.
- `isDeleted` — `0` (visible/active), `1` (logically deleted).

### `Veterinarian`
- `vetID` — primary key / license number (e.g. `VET-001`).
- `vetFName`, `vetLName`, `vetAddress`, `vetSpecial`.
- `isDeleted` — soft-delete flag.

### `Pet`
- `petID` — primary key (e.g. `PET-001`).
- `petName`, `petType`, `petBreed`, `petBDate`.
- `petOwnerID` — foreign key referencing `PetOwner.petOwnerID`.
- `isDeleted` — soft-delete flag.

### `Consultation`
- `consultID` — primary key (e.g. `CON-001`).
- `petID` — foreign key to `Pet.petID`.
- `vetID` — foreign key to `Veterinarian.vetID`.
- `consultDate` — MySQL `DATETIME`.
- `diagnoses`, `prescription` — text.
- `isDeleted` — soft-delete flag.

Relationships:

```text
PetOwner (petOwnerID) ───< Pet (petOwnerID)
                              │
                              └────< Consultation (petID)
Veterinarian (vetID) ──────────────< Consultation (vetID)
```

One owner can have many pets, one pet can have many consultations, and one veterinarian can conduct many consultations.

## 5. Full system process (recommended order)

```text
Start Apache + MySQL
        ↓
Import Clinic.sql
        ↓
Open Main Menu
        ↓
Register Veterinarians
        ↓
Register Pet Owners
        ↓
Register Pets → choose an existing Owner
        ↓
Create Consultation → choose a Pet + Veterinarian
        ↓
Enter consultation date/time, diagnoses, prescription
        ↓
Save consultation to the database
        ↓
Open Inquiry → filter consultation data and show COUNT
```

### A. Veterinarian Management (`veterinarian.php`)

1. Enter Vet License No., first name, last name, address and specialization.
2. Click **Save**. When hidden `editing` is blank, PHP does `INSERT`.
3. Click **Edit** in the table: the URL has `?edit=VET-001`; PHP loads that row into the form.
4. Save again; PHP does `UPDATE ... WHERE vetID = ?`.
5. Search matches license, names and specialization using `LIKE`.
6. **Delete** does not remove the SQL row; it changes `isDeleted = 1`. The normal listing excludes those rows.

### B. Pet Owner Management (`petowner.php`)

1. Add an owner with ID, name, birth date and telephone number.
2. Edit and search work like the Veterinarian page.
3. Delete updates `isDeleted = 1` (soft delete).
4. An owner must exist before a pet can be linked to that owner.

### C. Pet Management (`pet.php`)

1. Enter Pet ID, name, type, breed and birth date.
2. Select a pet owner from the dropdown. The options come from active owners (`isDeleted = 0`).
3. When saved, `petOwnerID` becomes the foreign key in `Pet`.
4. Edit loads an existing pet; search uses `LIKE` over several fields.
5. Delete marks the pet `isDeleted = 1`, so it disappears from the normal pet list and future consultation dropdown.

### D. Consultations Transaction Management (`consultation.php`)

1. Enter Consultation No. (e.g. `CON-005`).
2. Select an active pet and an active veterinarian from the dropdowns.
3. Enter a date/time, diagnoses and prescription.
4. The browser sends `datetime-local` values like `2026-11-11T09:30`; PHP replaces the `T` with a space for MySQL `DATETIME`.
5. **Save** performs `INSERT` for a new record, or `UPDATE` if `editing` contains an existing `consultID`.
6. Search matches consultation number, pet ID, vet ID, diagnoses or prescription.
7. Rows are listed using `ORDER BY consultDate DESC` (newest first).
8. Delete updates `isDeleted = 1` rather than removing the record.

### E. Consultations Inquiry (`inquiry.php`)

There are **five** separate GET forms. Each submits a `type` code that tells PHP which report to run.

| Inquiry | Input | SQL idea | Output |
| --- | --- | --- | --- |
| 1. Vets by specialization | Text, e.g. `Surgery` | `vetSpecial LIKE ?` | Matching vets and `COUNT(*)` |
| 2. Consultations by Pet ID | Text, e.g. `PET-001` | `c.petID = ?` | Related consultations and total |
| 3. Consultations by Owner ID | Text, e.g. `OWN-001` | `p.petOwnerID = ?` | Related consultations and total |
| 4. Consultations by Vet ID | Text, e.g. `VET-001` | `c.vetID = ?` | Related consultations and total |
| 5. Consultations by Date Range | From / To dates | `DATE(c.consultDate) BETWEEN ? AND ?` | Consultations within dates and total |

Inquiries 2–5 call the reusable helper function `showConsultations($where, $values)`:

1. It builds a common JOIN of `Consultation`, `Pet`, `PetOwner` and `Veterinarian`.
2. It uses `COUNT(*)` to show the total number of matching consultations.
3. It runs a second query to fetch matching records with related names.
4. It prints the records in an HTML table in ascending consultation-date order.

**Date-range detail:** `DATE(c.consultDate)` removes the time, so all consultations on the selected ending day are included.

## 6. Core PHP and SQL patterns to remember

**Database connection and prepared query helper (`db.php`):**

```php
$conn = new mysqli('localhost', 'root', '', 'clinic');
function run($sql, $values = []) {
    global $conn;
    return $conn->execute_query($sql, $values);
}
```

**Add (Create):**

```php
run('INSERT INTO PetOwner (petOwnerID, petOwnerFName) VALUES (?, ?)',
    [$id, $firstName]);
```

**Edit (Update):**

```php
run('UPDATE PetOwner SET petOwnerFName = ? WHERE petOwnerID = ?',
    [$firstName, $id]);
```

**Logical delete:**

```php
run('UPDATE PetOwner SET isDeleted = 1 WHERE petOwnerID = ?', [$id]);
```

**Search:**

```php
$search = $_GET['search'] ?? '';
$rows = run('SELECT * FROM Pet WHERE isDeleted = 0 AND petName LIKE ?',
            ["%$search%"]);
```

**Join related tables:**

```sql
SELECT c.consultID, p.petName, v.vetLName
FROM Consultation c
JOIN Pet p ON c.petID = p.petID
JOIN Veterinarian v ON c.vetID = v.vetID
WHERE c.isDeleted = 0;
```

**Filter by date range:**

```sql
SELECT * FROM Consultation
WHERE isDeleted = 0
  AND DATE(consultDate) BETWEEN ? AND ?;
```

**Remember:** `?` placeholders help bind values safely; `WHERE` limits affected/returned rows; `JOIN` combines related tables; `COUNT(*)` counts matching records.

## 7. Test checklist (sample data already included)

- [ ] Add a veterinarian with ID `VET-004` and search by their specialization.
- [ ] Add a pet owner with ID `OWN-004` and edit their telephone number.
- [ ] Add a pet with ID `PET-005`, choosing the new owner.
- [ ] Create consultation `CON-005` linking the new pet and vet.
- [ ] Verify `CON-005` appears in Consultation Management.
- [ ] Inquire by `PET-005`, `OWN-004`, and `VET-004`; each should include the related consultation.
- [ ] Inquire by a date range containing the consultation date.
- [ ] Delete the consultation and confirm it disappears from regular search/inquiry results.
- [ ] Check phpMyAdmin to confirm the deleted row still exists with `isDeleted = 1`.

## 8. Common problems and limitations

- **`Call to undefined method mysqli::execute_query()`**: Use XAMPP PHP 8.2+ or replace the helper with `prepare()`/`bind_param()`.
- **Unknown database `clinic`**: Import `Clinic.sql` first.
- **Duplicate table errors during import**: The file uses `CREATE TABLE` without `IF NOT EXISTS`; import into a clean database or back up and intentionally remove old tables before reimporting.
- **Cannot create Pet**: Make sure an active Pet Owner already exists; a pet needs `petOwnerID`.
- **Cannot create Consultation**: Make sure an active Pet and active Veterinarian exist; both are required foreign keys.
- **Blank page / connection error**: Confirm Apache/MySQL are running and edit credentials if needed.
- **Soft deletion nuance**: Deleting an owner, pet, or vet only marks that table's row as deleted. Existing related rows remain in SQL. Inquiries filter out deleted *consultations*, but do not automatically filter out consultations whose related pet, owner or vet is deleted.
- **Editing a linked record**: If its parent owner/vet was soft-deleted, it may not appear in the active dropdown; the supplied code does not handle that special case.
- **Security**: This is a training demo without authentication, granular permissions, CSRF protection or full business-rule validation; use only for local practice.

## 9. How to adapt this process to another exam

```text
Master 1 (PetOwner) ──> Master 2 (Pet)
Master 3 (Veterinarian) ──┐
                          └──> Transaction (Consultation)
                                     ↓
                           Search + Join + Count + Date Filter
```

For an **Employee Attendance** system, replace PetOwner/Pet/Vet with Department/Employee and Consultation with Attendance. For an **Events Registration** system, replace master records with Events/Participants and transaction records with Registration. The principle stays the same: create related master data first, save transactions using foreign keys, then show filtered reports with `JOIN`, `WHERE`, `COUNT`, or `SUM`.
