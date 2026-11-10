# PHP 5-File Reviewer — XAMPP Skills-Test Guide

A beginner-friendly **Product Inventory System** made with **plain PHP, MySQL (`mysqli`), HTML, and vanilla CSS**. The same logic can be adapted to students, employees, departments, events, and other CRUD systems.

> **This project contains five code files plus this README.** It is a learning example, not a finished template for every possible exam question. Use reference materials during an exam only if the proctor allows them.

---

## 1. What does this system do?

Imagine a small shop managing its products. Each product has a name, category, price, and quantity. The user can:

1. **Add** a new product.
2. **View** all saved products in a table.
3. **Search** by product name or category.
4. **Edit** product information.
5. **Delete** a product.
6. **Filter** products by one or more categories using checkboxes.
7. **Sort** products by name or price using radio buttons.
8. **View totals**: number of matching products, total units, and inventory value.

### System flow

```text
Open index.php (Main Menu)
           |
           +--> Manage Products (manage.php)
           |       |
           |       +--> Add (INSERT)
           |       +--> View (SELECT)
           |       +--> Search (WHERE + LIKE)
           |       +--> Edit (SELECT one + UPDATE)
           |       +--> Delete (DELETE + WHERE)
           |
           +--> Reports (report.php)
                   |
                   +--> Checkbox filters (WHERE + IN)
                   +--> Radio sorting (ORDER BY)
                   +--> Totals (COUNT + SUM)

Both PHP pages require db.php for the MySQL connection.
All pages load style.css for visual design.
```

---

## 2. How to run it in XAMPP

1. Open **XAMPP Control Panel**.
2. Start **Apache** and **MySQL**.
3. Put the folder `php-five-file-reviewer` inside:

   ```text
   C:\xampp\htdocs\php-five-file-reviewer\
   ```

4. Open a browser and visit:

   ```text
   http://localhost/php-five-file-reviewer/
   ```

5. Open **Manage Products**, add one product, and check that it appears in the table.
6. Open **Reports** and try the checkboxes and radio buttons.
7. Optional: Open `http://localhost/phpmyadmin/` and inspect database `skills_practice` > table `products`.

**Important:** You do **not** need to import an SQL file. `db.php` automatically creates the database and `products` table if they do not exist. Default local XAMPP credentials used by this project are username `root` and an empty password. Change those values in `db.php` if your installation differs. The MySQL user must have permission to create a database.

If port 80 is different on your setup, use your Apache port in the URL (for example `http://localhost:8080/php-five-file-reviewer/`).

---

## 3. What each file is for

```text
php-five-file-reviewer/
├── db.php       # MySQL connection + automatic database/table creation
├── index.php    # Main menu (links to Manage and Reports)
├── manage.php   # Add, view, edit, delete, search products
├── report.php   # Checkbox filtering, radio sorting, summary totals
├── style.css    # Vanilla CSS for navigation, forms, tables, buttons
└── README.md    # This learning guide
```

| File | Main concepts to study |
| --- | --- |
| `db.php` | `new mysqli()`, `CREATE DATABASE`, `CREATE TABLE`, `require`, `e()` |
| `index.php` | HTML navigation and links |
| `manage.php` | `$_POST`, `$_GET`, `INSERT`, `SELECT`, `UPDATE`, `DELETE`, `LIKE`, prepared statements |
| `report.php` | checkbox arrays, radio values, `WHERE IN`, `ORDER BY`, `COUNT`, `SUM` |
| `style.css` | selectors, classes, form layout, table style, responsive layout |

---

## 4. Understand the database first (`db.php`)

The automatically created database is **`skills_practice`**, with one table named **`products`**.

| Column | Data type | Meaning |
| --- | --- | --- |
| `productID` | `INT AUTO_INCREMENT PRIMARY KEY` | Unique product ID; MySQL generates it |
| `productName` | `VARCHAR(100) NOT NULL` | Product name |
| `category` | `VARCHAR(30) NOT NULL` | School, Office, Food, or Other |
| `price` | `DECIMAL(10,2) NOT NULL` | Product price in pesos |
| `quantity` | `INT NOT NULL` | Number of available units |

Equivalent MySQL table definition:

```sql
CREATE DATABASE IF NOT EXISTS skills_practice;
USE skills_practice;

CREATE TABLE IF NOT EXISTS products (
    productID INT AUTO_INCREMENT PRIMARY KEY,
    productName VARCHAR(100) NOT NULL,
    category VARCHAR(30) NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    quantity INT NOT NULL DEFAULT 0
);
```

**Why `require 'db.php';` appears at the top of the other PHP pages:** it makes the `$conn` MySQL connection available, so you do not repeat connection code in every page.

**Why `e($value)` is used in HTML:** it escapes special characters when showing user/database text, helping prevent HTML injection (XSS).

---

## 5. CRUD process in `manage.php`

**CRUD = Create, Read, Update, Delete.** The same file uses an HTML form and a records table.

### A. ADD — `INSERT`

1. User types Product Name, selects Category, enters Price and Quantity.
2. User clicks **Add Product**.
3. The form submits with `method="POST"`.
4. PHP checks `$_POST['action'] === 'save'` and sees `productID = 0`.
5. PHP validates the values and executes a prepared `INSERT`.
6. PHP redirects to `manage.php?msg=saved` so refreshing does not re-submit the form.

Key SQL:

```sql
INSERT INTO products (productName, category, price, quantity)
VALUES (?, ?, ?, ?);
```

The `?` values are provided using `bind_param('ssdi', ...)`:
- `s` = string (name)
- `s` = string (category)
- `d` = decimal/float number (price)
- `i` = integer (quantity)

### B. VIEW — `SELECT`

The page runs:

```sql
SELECT * FROM products ORDER BY productID DESC;
```

PHP loops through the result using `$products->fetch_assoc()` to build the HTML table. Newer IDs appear first.

### C. SEARCH — `LIKE`

1. User enters a search term.
2. Search form submits with `method="GET"`, creating a URL like `manage.php?search=note`.
3. PHP reads `$_GET['search']` and searches product name **or** category.

```sql
SELECT * FROM products
WHERE productName LIKE ? OR category LIKE ?
ORDER BY productID DESC;
```

PHP surrounds the search text with `%`, for example `%note%`, to match partial words.

### D. EDIT — `SELECT` one, then `UPDATE`

1. User clicks **Edit** beside a record.
2. Browser opens `manage.php?edit=3` (example).
3. PHP queries that `productID` and fills the form with the existing values.
4. The hidden `productID` now contains the record ID, rather than `0`.
5. User changes fields and clicks **Update Product**.
6. PHP updates **only the matching record**.

```sql
UPDATE products
SET productName = ?, category = ?, price = ?, quantity = ?
WHERE productID = ?;
```

### E. DELETE — `DELETE`

1. User clicks **Delete** beside a row.
2. Browser shows a JavaScript confirmation prompt.
3. If confirmed, the form sends `action=delete` and that row's `productID` by `POST`.
4. PHP executes:

```sql
DELETE FROM products WHERE productID = ?;
```

5. Page redirects to `manage.php?msg=deleted`.

**Critical caution:** Never remove `WHERE` from an `UPDATE` or `DELETE` statement unless you truly intend to affect every row.

### How the same form knows whether to ADD or EDIT

```php
if ($id > 0) {
    // UPDATE the product with this ID
} else {
    // INSERT a new product
}
```

The hidden `productID` is `0` for new products and is a positive ID while editing.

---

## 6. Checkbox filtering in `report.php`

The checkboxes are:

```html
<input type="checkbox" name="category[]" value="School">
<input type="checkbox" name="category[]" value="Office">
<input type="checkbox" name="category[]" value="Food">
<input type="checkbox" name="category[]" value="Other">
```

**Why `category[]` has brackets:** multiple checked boxes are sent to PHP as an **array**.

```php
$requested = $_GET['category'] ?? [];
```

Example: if you check **School** and **Food**, PHP receives values similar to:

```php
['School', 'Food']
```

The project checks the submitted values against its permitted category list. Then it builds SQL with placeholders:

```sql
SELECT * FROM products
WHERE category IN (?, ?);
```

This displays products in either selected category. **If no boxes are checked, it shows all categories.** The code dynamically creates the correct number of placeholders for the chosen categories.

> Remember: unchecked checkboxes usually do not appear in the submitted form data.

---

## 7. Radio-button sorting in `report.php`

All sorting options share the same `name="sort"`. That makes them one **radio group**: users choose only one sorting order.

The project supports:

| Radio value | Meaning | SQL order |
| --- | --- | --- |
| `name_asc` | Product Name A–Z | `ORDER BY productName ASC` |
| `name_desc` | Product Name Z–A | `ORDER BY productName DESC` |
| `price_low` | Lowest to highest price | `ORDER BY price ASC` |
| `price_high` | Highest to lowest price | `ORDER BY price DESC` |

PHP reads the selected value:

```php
$sort = $_GET['sort'] ?? 'name_asc';
```

It then uses a predefined list of allowed sort options. This matters because SQL column names / `ORDER BY` expressions should **not** be copied from unchecked user input.

**Checkbox = multiple selections (`category[]`). Radio = one selection (`sort`).** Both work in the same GET filter form.

---

## 8. Report calculations: `COUNT`, `SUM`, and multiplication

The report calculates an inventory value per product:

```text
stockValue = price × quantity
```

Example: a notebook priced at ₱50 with 4 units has ₱200 inventory value.

Its summary query is conceptually:

```sql
SELECT
    COUNT(*) AS totalProducts,
    COALESCE(SUM(quantity), 0) AS totalUnits,
    COALESCE(SUM(price * quantity), 0) AS totalValue
FROM products;
```

If a category filter is active, the same `WHERE category IN (...)` is added to **both** the table query and the summary query, so the totals match only the filtered rows.

| Summary box | Meaning |
| --- | --- |
| Matching Products | Number of product rows returned by the current filter |
| Total Units | Total of their quantities |
| Inventory Value | Total of `price × quantity` for all matching products |

`COALESCE(..., 0)` converts an empty-table `SUM` result from `NULL` into `0`.

---

## 9. What changes if the exam requires a DIFFERENT system?

Keep the overall process; replace the tables, inputs, validation, and SQL based on the printed instructions.

| Given project | Table example | Common fields | Possible report |
| --- | --- | --- | --- |
| Product Inventory | `products` | Name, category, price, quantity | Total stock value |
| Student Management | `students` | Student ID, name, course, year level | Filter by course |
| Employee Management | `employees` | Employee ID, name, department, rate | Filter by department |
| Event Management | `events` | Event ID, event name, fee, date | Sort events by date |

**Adaptation checklist:**

1. In `db.php`, use the database/table names and fields required by the paper.
2. In `manage.php`, change the form input `name=` attributes and the INSERT/UPDATE/SELECT/DELETE queries to match those fields.
3. Change input validation (for example, required name, valid amount, or date).
4. In `report.php`, change which fields the checkboxes filter and which columns the radio buttons sort.
5. Replace the report formulas with the required calculations.
6. Update labels, headings, and table columns in HTML. Styling in `style.css` can largely remain unchanged.
7. **If the task has related tables** (attendance, registration, voting, consultations), you must add the correct primary/foreign keys, `JOIN`s, and special transaction rules; the five-file inventory example does not already implement those.

### Example: adapting categories to student courses

Old inventory filter:

```sql
WHERE category IN (?, ?)
```

New student filter:

```sql
WHERE course IN (?, ?)
```

Old inventory sort:

```sql
ORDER BY productName ASC
```

New student sort:

```sql
ORDER BY lastName ASC
```

The **logic stays similar**, but you must also update the table, query, form fields, and allowed filter values—not just the SQL column name.

---

## 10. Quick 15-minute self-test

1. Add three products, such as Notebook (School, ₱50, 4 units), Ballpen (School, ₱10, 10 units), and Coffee (Food, ₱20, 5 units).
2. Confirm the table shows all three products.
3. Search for `note`; only the Notebook should match.
4. Edit Notebook to ₱60; confirm the price changes.
5. Open Reports; choose only **School** and click **Apply Filters**.
6. The report should show **2 matching products**, **14 total units**, and **₱340 inventory value** (`60 × 4 + 10 × 10`).
7. Sort by Price (High–Low); Notebook should appear before Ballpen.
8. Uncheck all categories and apply again; all products should reappear.
9. Delete Coffee and check that it disappears from both Manage Products and Reports.

These values assume a fresh database with only the three items listed.

---

## 11. Common XAMPP and PHP problems

| Problem | What to check |
| --- | --- |
| `localhost` does not open | Apache started? Correct port and folder under `htdocs`? |
| `Database connection error` | MySQL started? Credentials correct? Does the user have CREATE DATABASE permission? |
| `Unknown column` or `Table doesn't exist` | PHP query fields must match the database exactly |
| Page submits but no record appears | Form `method`, input `name`, validation, and SQL statement |
| Checkbox filter isn't working | Use `name="category[]"`, not `name="category"` for multiple selections |
| Sort doesn't work | Radio options must share `name="sort"`; check allowed sort keys |
| Edit changes the wrong record | Make sure `productID` is passed and `WHERE productID = ?` is present |
| Delete doesn't work | Check POST action is `delete` and hidden product ID is present |
| Table totals differ from filters | Use the same WHERE conditions in records query and totals query |
| PHP code displays as plain text | Open through `http://localhost/...`, not as a local `.php` file |

This code is for local study. It has no login, per-user authorization, or CSRF protection; do not publish it as-is on the public internet.

---

## 12. Last-minute reviewer: what to memorize

```text
require 'db.php';            --> Reuse the database connection
$_POST['field'] ?? ''         --> Receive an add/edit form field
$_GET['search'] ?? ''         --> Receive a search/filter field
INSERT INTO ... VALUES ...   --> Add
SELECT ... FROM ...          --> Display
UPDATE ... WHERE id = ?      --> Edit one record
DELETE FROM ... WHERE id = ? --> Delete one record
LIKE ?                      --> Partial-word search
WHERE field IN (?, ?)        --> Multiple checkbox filter
ORDER BY field ASC / DESC    --> Sort
COUNT(*)                    --> Number of rows
SUM(quantity)               --> Total quantities
SUM(price * quantity)       --> Calculated total
header('Location: ...');    --> Redirect after saving
exit;                       --> Stop PHP after redirect
```

**Suggested practice order:** database connection → add → display → search → edit → delete → checkbox filter → radio sort → report totals → CSS.
