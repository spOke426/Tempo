<?php
require_once 'functions.php';
require_login();
require_once 'config.php';

// Optional student record to edit (using its internal database ID).
$editing = null;
if (isset($_GET['edit'])) {
    $id = filter_var($_GET['edit'], FILTER_VALIDATE_INT);
    if (!$id || $id < 1) {
        flash('Invalid student record.', 'error');
        go('dashboard.php');
    }
    $stmt = $pdo->prepare('SELECT * FROM students WHERE id = ?');
    $stmt->execute([$id]);
    $editing = $stmt->fetch();
    if (!$editing) {
        flash('Student not found.', 'error');
        go('dashboard.php');
    }
}

// Search by ID number, name, or course.
$search = trim($_GET['search'] ?? '');
if ($search !== '') {
    $term = '%' . $search . '%';
    $stmt = $pdo->prepare('SELECT * FROM students
        WHERE idno LIKE ? OR lastname LIKE ? OR firstname LIKE ? OR course LIKE ?
        ORDER BY id DESC');
    $stmt->execute([$term, $term, $term, $term]);
    $students = $stmt->fetchAll();
} else {
    $students = $pdo->query('SELECT * FROM students ORDER BY id DESC')->fetchAll();
}

$page_title = 'Students - Student Management System';
require 'header.php';
?>
<div class="page-heading">
    <div>
        <h1>Student Management</h1>
        <p class="muted">Add, edit, search, and delete student records.</p>
    </div>
    <?php if ($editing): ?>
        <a class="btn btn-light" href="dashboard.php">Cancel Edit</a>
    <?php endif; ?>
</div>

<section class="card">
    <h2><?= $editing ? 'Edit Student' : 'Add Student' ?></h2>
    <form method="POST" action="save_student.php">
        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= h($editing['id'] ?? '') ?>">

        <div class="form-grid">
            <div class="field">
                <label for="idno">ID Number</label>
                <input type="text" id="idno" name="idno" maxlength="10" inputmode="numeric" pattern="[0-9]+" placeholder="e.g. 1001" value="<?= h($editing['idno'] ?? '') ?>" required>
            </div>
            <div class="field">
                <label for="lastname">Last Name</label>
                <input type="text" id="lastname" name="lastname" maxlength="50" placeholder="Last name" value="<?= h($editing['lastname'] ?? '') ?>" required>
            </div>
            <div class="field">
                <label for="firstname">First Name</label>
                <input type="text" id="firstname" name="firstname" maxlength="50" placeholder="First name" value="<?= h($editing['firstname'] ?? '') ?>" required>
            </div>
            <div class="field">
                <label for="course">Course</label>
                <input type="text" id="course" name="course" maxlength="10" placeholder="e.g. BSIT" value="<?= h($editing['course'] ?? '') ?>" required>
            </div>
            <div class="field">
                <label for="level">Level</label>
                <input type="text" id="level" name="level" maxlength="5" placeholder="e.g. 3" value="<?= h($editing['level'] ?? '') ?>" required>
            </div>
        </div>
        <div class="form-actions">
            <button class="btn btn-primary" type="submit"><?= $editing ? 'Update Student' : 'Add Student' ?></button>
            <a class="btn btn-light" href="dashboard.php">Clear</a>
        </div>
    </form>
</section>

<section class="card">
    <div class="table-heading">
        <div>
            <h2>Student Records</h2>
            <p class="muted"><?= count($students) ?> student(s) found</p>
        </div>
        <form method="GET" action="dashboard.php" class="search-bar">
            <input type="search" name="search" placeholder="Search ID, name or course" value="<?= h($search) ?>" aria-label="Search students">
            <button class="btn btn-primary" type="submit">Search</button>
            <?php if ($search !== ''): ?>
                <a class="btn btn-light" href="dashboard.php">Reset</a>
            <?php endif; ?>
        </form>
    </div>

    <div class="table-scroll">
        <table>
            <thead>
                <tr>
                    <th>ID</th><th>ID Number</th><th>Last Name</th><th>First Name</th><th>Course</th><th>Level</th><th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$students): ?>
                    <tr><td colspan="7" class="empty">No students found.</td></tr>
                <?php else: ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= h($student['id']) ?></td>
                            <td><?= h($student['idno']) ?></td>
                            <td><?= h($student['lastname']) ?></td>
                            <td><?= h($student['firstname']) ?></td>
                            <td><?= h($student['course']) ?></td>
                            <td><?= h($student['level']) ?></td>
                            <td>
                                <div class="row-actions">
                                    <a class="btn btn-small btn-light" href="dashboard.php?edit=<?= urlencode((string) $student['id']) ?>">Edit</a>
                                    <form method="POST" action="delete_student.php">
                                        <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
                                        <input type="hidden" name="id" value="<?= h($student['id']) ?>">
                                        <button class="btn btn-small btn-danger" type="submit">Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php require 'footer.php'; ?>
