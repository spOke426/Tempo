<?php
require 'db.php';

// CHECKBOX FILTER: category[] sends an array; unchecked checkboxes send nothing.
$categories = ['School', 'Office', 'Food', 'Other'];
$requested = $_GET['category'] ?? [];
if (!is_array($requested)) {
    $requested = [];
}
$selected = array_values(array_intersect($categories, $requested));

// RADIO SORT: all radio buttons share name="sort", so PHP receives one value.
$sortChoices = [
    'name_asc' => ['label' => 'Name (A–Z)', 'sql' => 'productName ASC'],
    'name_desc' => ['label' => 'Name (Z–A)', 'sql' => 'productName DESC'],
    'price_low' => ['label' => 'Price (Low–High)', 'sql' => 'price ASC'],
    'price_high' => ['label' => 'Price (High–Low)', 'sql' => 'price DESC']
];
$sort = $_GET['sort'] ?? 'name_asc';
if (!isset($sortChoices[$sort])) {
    $sort = 'name_asc';
}

// WHERE IN filters by chosen categories (or shows all if none are checked).
$where = '';
if (count($selected) > 0) {
    $placeholders = implode(',', array_fill(0, count($selected), '?'));
    $where = " WHERE category IN ($placeholders)";
}

// ORDER BY uses a fixed, trusted list. Never append unchecked user input to SQL.
$sql = 'SELECT productID, productName, category, price, quantity,
               (price * quantity) AS stockValue
        FROM products' . $where . ' ORDER BY ' . $sortChoices[$sort]['sql'];
$stmt = $conn->prepare($sql);
if (count($selected) > 0) {
    $stmt->bind_param(str_repeat('s', count($selected)), ...$selected);
}
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// REPORT TOTALS: COUNT and SUM use the same filters as the table.
$totalsSql = 'SELECT COUNT(*) AS totalProducts,
                     COALESCE(SUM(quantity), 0) AS totalUnits,
                     COALESCE(SUM(price * quantity), 0) AS totalValue
              FROM products' . $where;
$totalsStmt = $conn->prepare($totalsSql);
if (count($selected) > 0) {
    $totalsStmt->bind_param(str_repeat('s', count($selected)), ...$selected);
}
$totalsStmt->execute();
$totals = $totalsStmt->get_result()->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Product Reports</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php">PHP Skills-Test Reviewer</a>
        <nav>
            <a href="index.php">Home</a>
            <a href="manage.php">Manage Products</a>
            <a class="active" href="report.php">Reports</a>
        </nav>
    </div>
</header>
<main class="container">
    <div class="page-title">
        <div><span class="eyebrow">FILTERS + SORTING + TOTALS</span><h1>Product Reports</h1></div>
        <a href="manage.php" class="button button-light">+ Manage Products</a>
    </div>

    <section class="panel">
        <h2>Filter and Sort</h2>
        <form method="GET">
            <div class="filter-groups">
                <fieldset>
                    <legend>Checkboxes: Filter by category</legend>
                    <div class="options">
                        <?php foreach ($categories as $category): ?>
                            <label class="choice">
                                <input type="checkbox" name="category[]" value="<?= e($category) ?>" <?= in_array($category, $selected, true) ? 'checked' : '' ?>>
                                <?= e($category) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <small class="muted">Leave all unchecked to show every category.</small>
                </fieldset>
                <fieldset>
                    <legend>Radio buttons: Sort by</legend>
                    <div class="options options-vertical">
                        <?php foreach ($sortChoices as $value => $choice): ?>
                            <label class="choice">
                                <input type="radio" name="sort" value="<?= e($value) ?>" <?= $sort === $value ? 'checked' : '' ?>>
                                <?= e($choice['label']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </fieldset>
            </div>
            <div class="form-actions">
                <button type="submit" class="button">Apply Filters</button>
                <a href="report.php" class="button button-light">Reset</a>
            </div>
        </form>
    </section>

    <div class="summary-grid">
        <div class="summary-card"><span>Matching Products</span><strong><?= (int) $totals['totalProducts'] ?></strong></div>
        <div class="summary-card"><span>Total Units</span><strong><?= (int) $totals['totalUnits'] ?></strong></div>
        <div class="summary-card"><span>Inventory Value</span><strong>₱<?= number_format((float) $totals['totalValue'], 2) ?></strong></div>
    </div>

    <section class="panel">
        <h2>Filtered Results</h2>
        <div class="table-wrap">
            <table>
                <thead><tr><th>ID</th><th>Name</th><th>Category</th><th>Price</th><th>Qty</th><th>Price × Qty</th></tr></thead>
                <tbody>
                <?php foreach ($rows as $item): ?>
                    <tr>
                        <td><?= e($item['productID']) ?></td>
                        <td><?= e($item['productName']) ?></td>
                        <td><?= e($item['category']) ?></td>
                        <td>₱<?= number_format((float) $item['price'], 2) ?></td>
                        <td><?= e($item['quantity']) ?></td>
                        <td>₱<?= number_format((float) $item['stockValue'], 2) ?></td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$rows): ?>
                    <tr><td colspan="6" class="empty">No matching records. Add products or change your filters.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
