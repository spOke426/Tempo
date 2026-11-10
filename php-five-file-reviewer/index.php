<?php require 'db.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PHP Skills-Test Reviewer</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php">PHP Skills-Test Reviewer</a>
        <nav>
            <a class="active" href="index.php">Home</a>
            <a href="manage.php">Manage Products</a>
            <a href="report.php">Reports</a>
        </nav>
    </div>
</header>

<main class="container">
    <section class="hero">
        <span class="eyebrow">PLAIN PHP + MYSQL + VANILLA CSS</span>
        <h1>Five-file practice project</h1>
        <p>A simple Product Inventory example. Replace the table names, form fields, and calculations to match the requirements in your printed skills-test instructions.</p>
    </section>

    <div class="cards">
        <a class="feature-card" href="manage.php">
            <span class="card-kicker">01 / CRUD</span>
            <h2>Manage Products</h2>
            <p>Add, view, edit, delete, and search products using one PHP file.</p>
            <span class="card-link">Open product management →</span>
        </a>
        <a class="feature-card" href="report.php">
            <span class="card-kicker">02 / REPORTS</span>
            <h2>Filter and Sort</h2>
            <p>Use category checkboxes, sorting radio buttons, and inventory totals.</p>
            <span class="card-link">Open reports →</span>
        </a>
    </div>

    <section class="panel">
        <h2>How each file works</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>File</th><th>Purpose</th></tr></thead>
                <tbody>
                    <tr><td><code>db.php</code></td><td>Connects to MySQL; creates a practice database/table</td></tr>
                    <tr><td><code>index.php</code></td><td>Main menu and explanation</td></tr>
                    <tr><td><code>manage.php</code></td><td>INSERT, SELECT, UPDATE, DELETE, LIKE search</td></tr>
                    <tr><td><code>report.php</code></td><td>WHERE, IN, ORDER BY, COUNT, SUM</td></tr>
                    <tr><td><code>style.css</code></td><td>Layout, forms, tables, buttons; no framework</td></tr>
                </tbody>
            </table>
        </div>
    </section>

    <section class="panel">
        <h2>How to adapt to another exam system</h2>
        <p>Example: change <code>products</code> to <code>students</code>, then change <code>productName</code>, <code>category</code>, <code>price</code>, and <code>quantity</code> to the fields required by your paper. Update the corresponding form inputs and SQL queries too.</p>
        <p class="muted">For a registration or attendance system, create the related tables and add the specific transaction/calculation rules described on the paper. This starter is intentionally small; it is a practice example, not a code generator.</p>
    </section>
</main>
</body>
</html>
