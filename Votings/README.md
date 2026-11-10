# Philippine National Election System — Process & Code Reviewer

A beginner-friendly explanation of the uploaded **Voting-YourFamilyName** PHP project.

## 1. What this system does

The system lets an organizer maintain elective positions, candidates and voters. An active voter can log in, choose candidates for currently open positions, and submit a ballot once. The system shows vote totals, candidate percentages, and winners.

**Technology:** plain PHP, MySQL, HTML and vanilla CSS. No framework is required.

## 2. XAMPP setup (read this first)

1. Install a XAMPP version with **PHP 8.2 or newer**. The project uses `$conn->execute_query()`, a MySQLi method introduced in PHP 8.2.
2. Open XAMPP Control Panel and start **Apache** and **MySQL**.
3. Place the project in `C:\xampp\htdocs\voting-system\` (the `index.php` file should be directly inside that folder).
4. Visit `http://localhost/phpmyadmin/` and choose the **Import** tab.
5. Import `Election.sql`. It creates the `election` database, four tables and sample records.
6. Open `http://localhost/voting-system/`.
7. If your MySQL root password is not blank, adjust the credentials in `db.php`.

> Important: The other uploaded Clinic ZIP also had a folder named `YourFamilyName`. Give these projects separate folder names in `htdocs` so they do not overwrite each other.

## 3. Files and responsibilities

| File | Purpose |
| --- | --- |
| `Election.sql` | Create database, tables, foreign keys, and sample data |
| `db.php` | Open MySQL connection, start voting session, define `run()` and `h()` |
| `index.php` | Main menu linking to all modules |
| `positions.php` | Add/edit positions; open or close voting for a position |
| `candidates.php` | Add/edit candidates, assign position, activate/deactivate |
| `voters.php` | Add/edit voters, activate/deactivate, view voted status |
| `vote.php` | Voter login, ballot display, limit checks, ballot submission |
| `results.php` | Candidate vote counts and vote percentages |
| `winners.php` | Highest-vote candidates per position, up to available seats |
| `header.php`, `footer.php` | Reusable page layout and navigation |
| `style.css` | Simple page, forms and table styling |

## 4. Database tables and relationships

### `Positions`
- `posID` — primary key, auto-increment.
- `posName` — e.g. President, Vice President, Senator.
- `numOfPositions` — maximum number of candidates a voter may choose **for that position**, and maximum number of winners displayed.
- `posStat` — `open` or `closed`.

### `Voters`
- `voterID` — primary key and login username.
- `voterPass` — sample login password, stored as plain text in this practice project.
- `voterFName`, `voterMName`, `voterLName` — identity fields.
- `voterStat` — `active` or `inactive`.
- `voted` — `N` or `Y`, indicating whether the voter already submitted a ballot.

### `Candidates`
- `candID` — auto-increment primary key.
- `candFName`, `candMName`, `candLName` — names.
- `posID` — foreign key referencing `Positions.posID`.
- `candStat` — `active` or `inactive`.

### `Votes`
- `posID` — foreign key to `Positions`.
- `voterID` — foreign key to `Voters`.
- `candID` — foreign key to `Candidates`.
- One row represents one selected candidate on a ballot. This table has **no separate primary key** in the provided schema.

Relationships:

```text
Positions (posID) ───< Candidates (posID)
         │
         └───────────< Votes (posID)
Voters (voterID) ────< Votes (voterID)
Candidates (candID) ─< Votes (candID)
```

`<` indicates the "many" side: one position may have many candidates and many votes; one voter may have many vote rows (one per selected candidate).

## 5. Full system process (recommended order)

```text
Start Apache + MySQL
        ↓
Import Election.sql
        ↓
Open Main Menu
        ↓
Create Positions (and set number of allowed selections)
        ↓
Add Candidates → assign each to a Position
        ↓
Add Voters / set active status
        ↓
Voter logs in using Voter ID + Password
        ↓
Check: voter active AND has not voted
        ↓
Show open positions and active candidates
        ↓
Choose candidates using checkboxes
        ↓
Validate each position's selection limit
        ↓
Save Votes rows + set Voters.voted = 'Y'
        ↓
View Election Results / Election Winners
```

### A. Positions Management (`positions.php`)

1. Type a position name and number of available slots; click **Save**.
2. When `editing` is empty, the code runs `INSERT`.
3. When you click **Edit**, the page receives `?edit=posID`, fetches the record, and fills the same form.
4. Clicking **Save** while editing runs `UPDATE ... WHERE posID = ?`.
5. **Deactivate** changes `posStat` from `open` to `closed`; **Activate** changes it back.
6. Closed positions are excluded from the ballot, but still appear on the Results and Winners pages.

### B. Candidates Management (`candidates.php`)

1. Enter candidate names and select the position from the dropdown.
2. Save to `Candidates`, using the selected `posID` as a foreign key.
3. Click Edit to update names or position.
4. Activate/Deactivate changes `candStat`; inactive candidates do not appear on a ballot.
5. The list uses `JOIN` to display the position's name rather than only its ID.

### C. Voters Management (`voters.php`)

1. Enter voter ID, password and name; click Save.
2. New voters default to `active` and `voted = 'N'`.
3. Edit can change voter information; activate/deactivate can block or allow login.
4. A voter with `voted = 'Y'` cannot vote again through the normal login flow.

### D. Voting (`vote.php`)

1. Voter enters `voterID` and `voterPass`.
2. PHP checks that those values match a voter, their status is `active`, and `voted` is `N`.
3. A PHP session (`$_SESSION['voterID']`) remembers who is logged in.
4. The ballot queries **open** positions and **active** candidates, grouped by position.
5. Every candidate is a checkbox. For a position of ID `3`, the field looks like `name="pos[3][]"`; PHP receives multiple candidate IDs in `$_POST['pos'][3]`.
6. The page JavaScript prevents excessive checkbox selection as a convenience, and PHP separately validates the maximum selections for each position.
7. On Submit, PHP checks candidates belong to the specified open position and are active, inserts accepted selections into `Votes`, updates the voter to `voted = 'Y'`, and logs them out.
8. If zero candidates are selected, the form displays an error. A user cannot submit again after their `voted` flag changes to `Y`.

**Sample voter login:** `V001` / `1234` (initially eligible). `V003` / `1234` already has `voted = 'Y'`; `V005` / `1234` is inactive.

### E. Election Results (`results.php`)

- Loops through all positions.
- Uses `LEFT JOIN` from `Candidates` to `Votes` so a candidate can appear with **zero** votes.
- Uses `COUNT(v.voterID)` to count votes for each candidate.
- Computes voting percentage as:

```text
candidate percent = candidate vote count ÷ total vote rows for that position × 100
```

- If a position has no votes, percentage is displayed as `0.00`.
- For a multi-seat position, this is the percentage of **candidate selections** for the position, not the percentage of voters.

### F. Winners (`winners.php`)

- Loops through positions and reads `numOfPositions`.
- Sorts candidates by their vote count in descending order and uses `LIMIT` to take the top candidates, up to the number of slots.
- Uses `JOIN Votes`, so **candidates with no votes are not included**.
- Ties are not specially resolved by the current program; add tie-handling if instructions require it.

## 6. The PHP logic to recognize on a skills test

**Database query helper:**

```php
$conn = new mysqli('localhost', 'root', '', 'election');
function run($sql, $values = []) {
    global $conn;
    return $conn->execute_query($sql, $values);
}
```

**Insert (Create):**

```php
run('INSERT INTO Positions (posName, numOfPositions) VALUES (?, ?)',
    [$_POST['posName'], $_POST['numOfPositions']]);
```

**Update (Edit):**

```php
run('UPDATE Positions SET posName = ? WHERE posID = ?', [$newName, $posID]);
```

**Toggle status (soft availability change):**

```sql
UPDATE Positions
SET posStat = IF(posStat = 'open', 'closed', 'open')
WHERE posID = ?;
```

**Join:**

```sql
SELECT c.candID, c.candFName, p.posName
FROM Candidates c
JOIN Positions p ON c.posID = p.posID;
```

**Count votes (result):**

```sql
SELECT c.candID, COUNT(v.voterID) AS totalVotes
FROM Candidates c
LEFT JOIN Votes v ON c.candID = v.candID
GROUP BY c.candID;
```

**Important:** `?` values are supplied through prepared-query parameter arrays. Do not put raw untrusted input directly into SQL.

## 7. Test checklist

- [ ] Positions page: create a new position with a maximum of 1, then edit it.
- [ ] Positions page: close it and verify it is absent from the ballot.
- [ ] Candidates page: add candidates for the position; deactivate one.
- [ ] Voters page: create a new active voter with `voted = 'N'`.
- [ ] Vote page: log in as an inactive voter and confirm it is rejected.
- [ ] Vote page: log in as a voter who already voted and confirm it is rejected.
- [ ] Vote page: select more candidates than allowed and confirm the limit is enforced.
- [ ] Vote page: submit an eligible ballot and check rows appear in `Votes` via phpMyAdmin.
- [ ] Confirm the voter changes from `N` to `Y` and a second vote is rejected.
- [ ] Results page: confirm vote counts and percentages.
- [ ] Winners page: confirm highest-vote candidates are listed.

## 8. Common problems and limitations

- **`Call to undefined method mysqli::execute_query()`**: PHP is older than 8.2; use a newer XAMPP installation or rewrite the helper using `prepare()` and `bind_param()`.
- **Unknown database `election`**: Import `Election.sql` first.
- **Duplicate table errors during import**: The script uses plain `CREATE TABLE`, not `CREATE TABLE IF NOT EXISTS`; import into a fresh database, or back up and intentionally drop the old tables before re-importing.
- **Database login fails**: Check MySQL in XAMPP and the `db.php` credentials.
- **Vote percentages look unusual for Senator**: More than one candidate may be selected, so the denominator is total selections, not number of participating voters.
- **Password security**: The demo stores and displays plaintext voter passwords. For a real election, use `password_hash()` and `password_verify()`, authorization, HTTPS and stricter privacy controls.
- **Voting integrity**: The sample lacks a ballot transaction, database-level uniqueness against duplicate vote rows, and strong concurrency/race protection. It is for classroom practice, **not** a real public election.
- **Security of management pages**: The management and report pages have no admin authentication, so do not expose this demo on the public internet.

## 9. How to adapt the process to another exam

The reusable pattern is:

```text
Master tables (positions / candidates / voters)
    ↓
Transaction table (Votes)
    ↓
Validation (eligibility and selection limits)
    ↓
Reports (JOIN + COUNT + ORDER BY + LIMIT)
```

The same pattern works for student elections, event registration, booking, enrollment, or product orders. You change the **table names, field names and business rules**, while keeping the general PHP form → SQL → report process.
