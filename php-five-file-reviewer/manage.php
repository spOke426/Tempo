<?php
require 'db.php';

$categories = ['School', 'Office', 'Food', 'Other'];
$error = '';
$form = ['productID' => 0, 'productName' => '', 'category' => 'School', 'price' => '', 'quantity' => ''];

// A. ADD, EDIT, DELETE: Process the form when submitted.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete') {
        $id = (int) ($_POST['productID'] ?? 0);
        if ($id > 0) {
            $stmt = $conn->prepare('DELETE FROM products WHERE productID = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
        }
        header('Location: manage.php?msg=deleted');
        exit;
    }

    if ($action === 'save') {
        $form = [
            'productID' => (int) ($_POST['productID'] ?? 0),
            'productName' => trim($_POST['productName'] ?? ''),
            'category' => $_POST['category'] ?? '',
            'price' => trim($_POST['price'] ?? ''),
            'quantity' => trim($_POST['quantity'] ?? '')
        ];

        if ($form['productName'] === '') {
            $error = 'Enter a product name.';
        } elseif (!in_array($form['category'], $categories, true)) {
            $error = 'Choose a valid category.';
        } elseif (!is_numeric($form['price']) || (float) $form['price'] < 0) {
            $error = 'Price must be zero or greater.';
        } elseif (filter_var($form['quantity'], FILTER_VALIDATE_INT) === false || (int) $form['quantity'] < 0) {
            $error = 'Quantity must be a whole number that is zero or greater.';
        } else {
            $id = $form['productID'];
            $name = $form['productName'];
            $category = $form['category'];
            $price = (float) $form['price'];
            $quantity = (int) $form['quantity'];

            // Prepared statements prevent user input from changing SQL commands.
            if ($id > 0) {
                $stmt = $conn->prepare('UPDATE products SET productName = ?, category = ?, price = ?, quantity = ? WHERE productID = ?');
                $stmt->bind_param('ssdii', $name, $category, $price, $quantity, $id);
            } else {
                $stmt = $conn->prepare('INSERT INTO products (productName, category, price, quantity) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('ssdi', $name, $category, $price, $quantity);
            }
            $stmt->execute();
            header('Location: manage.php?msg=saved');
            exit;
        }
    }
}

// B. LOAD A RECORD INTO THE EDIT FORM (when ?edit=NUMBER is in the URL).
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $stmt = $conn->prepare('SELECT * FROM products WHERE productID = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    if ($existing) {
        $form = $existing;
    }
}

// C. SEARCH (LIKE) or VIEW ALL (SELECT).
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $stmt = $conn->prepare('SELECT * FROM products WHERE productName LIKE ? OR category LIKE ? ORDER BY productID DESC');
    $keyword = '%' . $search . '%';
    $stmt->bind_param('ss', $keyword, $keyword);
    $stmt->execute();
    $products = $stmt->get_result();
} else {
    $products = $conn->query('SELECT * FROM products ORDER BY productID DESC');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Manage Products</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php">PHP Skills-Test Reviewer</a>
        <nav>
            <a href="index.php">Home</a>
            <a class="active" href="manage.php">Manage Products</a>
            <a href="report.php">Reports</a>
        </nav>
    </div>
</header>
<main class="container">
    <div class="page-title">
        <div><span class="eyebrow">CRUD EXAMPLE</span><h1>Manage Products</h1></div>
        <a class="button button-light" href="report.php">View Reports →</a>
    </div>

    <?php if ($error !== ''): ?><p class="notice error"><?= e($error) ?></p><?php endif; ?>
    <?php if (($_GET['msg'] ?? '') === 'saved'): ?><p class="notice success">Product saved successfully.</p><?php endif; ?>
    <?php if (($_GET['msg'] ?? '') === 'deleted'): ?><p class="notice success">Product deleted successfully.</p><?php endif; ?>

    <section class="panel">
        <h2><?= (int) $form['productID'] > 0 ? 'Edit Product' : 'Add Product' ?></h2>
        <form method="POST" class="product-form">
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="productID" value="<?= e($form['productID']) ?>">
            <label>Product Name
                <input type="text" name="productName" value="<?= e($form['productName']) ?>" placeholder="e.g. Notebook" required>
            </label>
            <label>Category
                <select name="category" required>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?= e($category) ?>" <?= $form['category'] === $category ? 'selected' : '' ?>><?= e($category) ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
            <label>Price (₱)
                <input type="number" step="0.01" min="0" name="price" value="<?= e($form['price']) ?>" placeholder="0.00" required>
            </label>
            <label>Quantity
                <input type="number" step="1" min="0" name="quantity" value="<?= e($form['quantity']) ?>" placeholder="0" required>
            </label>
            <div class="form-actions">
                <button type="submit" class="button"><?= (int) $form['productID'] > 0 ? 'Update Product' : 'Add Product' ?></button>
                <?php if ((int) $form['productID'] > 0): ?><a href="manage.php" class="button button-light">Cancel Edit</a><?php endif; ?>
            </div>
        </form>
    </section>

    <section class="panel">
        <div class="section-head"><h2>Product Records</h2><span class="muted"><?= $products->num_rows ?> record(s)</span></div>
        <form method="GET" class="search-bar">
            <input type="text" name="search" value="<?= e($search) ?>" placeholder="Search name or category">
            <button class="button" type="submit">Search</button>
            <a class="button button-light" href="manage.php">Reset</a>
        </form>
        <div class="table-wrap">
            <table>
                <thead><tr><th>ID</th><th>Product Name</th><th>Category</th><th>Price</th><th>Qty</th><th>Actions</th></tr></thead>
                <tbody>
                <?php while ($item = $products->fetch_assoc()): ?>
                    <tr>
                        <td><?= e($item['productID']) ?></td>
                        <td><?= e($item['productName']) ?></td>
                        <td><?= e($item['category']) ?></td>
                        <td>₱<?= number_format((float) $item['price'], 2) ?></td>
                        <td><?= e($item['quantity']) ?></td>
                        <td class="actions">
                            <a class="button button-small button-light" href="manage.php?edit=<?= (int) $item['productID'] ?>">Edit</a>
                            <form method="POST" class="inline-form" onsubmit="return confirm('Delete this product?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="productID" value="<?= e($item['productID']) ?>">
                                <button type="submit" class="button button-small button-danger">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                <?php if ($products->num_rows === 0): ?>
                    <tr><td colspan="6" class="empty">No products found. Add one using the form above.</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
</main>
</body>
</html>
