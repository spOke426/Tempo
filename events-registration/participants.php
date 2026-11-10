<?php
require_once 'functions.php';
$pageTitle = 'Participants Management';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'delete') {
            $id = get_id($_POST['partID'] ?? null);
            if (!$id) throw new Exception('Invalid participant.');
            $check = $pdo->prepare('SELECT COUNT(*) FROM Registration WHERE partID=?');
            $check->execute([$id]);
            if ($check->fetchColumn()) throw new Exception('Delete this participant\'s registration before deleting the participant.');
            $pdo->prepare('DELETE FROM Participants WHERE partID=?')->execute([$id]);
            flash('Participant deleted.');
        } elseif ($action === 'save') {
            $id = get_id($_POST['partID'] ?? null);
            $eventID = get_id($_POST['evCode'] ?? null);
            $first = trim($_POST['partFName'] ?? '');
            $last = trim($_POST['partLName'] ?? '');
            $discountInput = trim($_POST['partDRate'] ?? '');
            if (!$eventID || $first === '' || $last === '' || !valid_amount($discountInput)) {
                throw new Exception('Enter a valid event, first name, last name and discount amount.');
            }
            $discount = (float) $discountInput;
            $feeStmt = $pdo->prepare('SELECT evRFee FROM Events WHERE evCode=?');
            $feeStmt->execute([$eventID]);
            $fee = $feeStmt->fetchColumn();
            if ($fee === false) throw new Exception('The selected event was not found.');
            if ($discount > (float) $fee) throw new Exception('The discount must not exceed the event fee.');
            if ($id) {
                $previous = $pdo->prepare('SELECT evCode, partDRate FROM Participants WHERE partID=?');
                $previous->execute([$id]);
                $old = $previous->fetch();
                if (!$old) throw new Exception('Participant not found.');
                $check = $pdo->prepare('SELECT COUNT(*) FROM Registration WHERE partID=?');
                $check->execute([$id]);
                if ($check->fetchColumn() > 0 && ((int)$old['evCode'] !== $eventID || abs((float)$old['partDRate'] - $discount) > 0.001)) {
                    throw new Exception('Cannot change event or discount after registration. Delete the registration first.');
                }
                $pdo->prepare('UPDATE Participants SET evCode=?, partFName=?, partLName=?, partDRate=? WHERE partID=?')
                    ->execute([$eventID, $first, $last, $discount, $id]);
                flash('Participant updated.');
            } else {
                $pdo->prepare('INSERT INTO Participants (evCode, partFName, partLName, partDRate) VALUES (?, ?, ?, ?)')
                    ->execute([$eventID, $first, $last, $discount]);
                flash('Participant added.');
            }
        } else {
            throw new Exception('Unknown action.');
        }
    } catch (Exception $e) {
        flash($e->getMessage(), 'error');
    }
    redirect('participants.php');
}

$events = $pdo->query('SELECT evCode, evName, evRFee FROM Events ORDER BY evName')->fetchAll();
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM Participants WHERE partID=?');
    $stmt->execute([get_id($_GET['edit'])]);
    $edit = $stmt->fetch() ?: null;
}
$search = trim($_GET['search'] ?? '');
$stmt = $pdo->prepare('SELECT p.*, e.evName, e.evRFee FROM Participants p JOIN Events e ON e.evCode=p.evCode
     WHERE p.partFName LIKE ? OR p.partLName LIKE ? OR e.evName LIKE ? OR CAST(p.partID AS CHAR) LIKE ? ORDER BY p.partID DESC');
$stmt->execute(array_fill(0, 4, '%' . $search . '%'));
$participants = $stmt->fetchAll();
require 'header.php';
?>
<div class="card">
    <h2><?= $edit ? 'Update Participant' : 'Add a Participant' ?></h2>
    <?php if (!$events): ?><p class="note">Add an event in <a href="events.php">Events Management</a> first.</p><?php endif; ?>
    <form method="post">
        <?php csrf_input(); ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="partID" value="<?= h($edit['partID'] ?? '') ?>">
        <div class="form-grid">
            <div class="field"><label for="partFName">First Name</label><input id="partFName" name="partFName" maxlength="100" required value="<?= h($edit['partFName'] ?? '') ?>"></div>
            <div class="field"><label for="partLName">Last Name</label><input id="partLName" name="partLName" maxlength="100" required value="<?= h($edit['partLName'] ?? '') ?>"></div>
            <div class="field"><label for="evCode">Event</label><select id="evCode" name="evCode" required><option value="">Select event</option>
                <?php foreach ($events as $event): ?><option value="<?= h($event['evCode']) ?>" <?= (int)($edit['evCode'] ?? 0) === (int)$event['evCode'] ? 'selected' : '' ?>><?= h($event['evName']) ?> (<?= peso($event['evRFee']) ?>)</option><?php endforeach; ?>
            </select></div>
            <div class="field"><label for="partDRate">Discount Amount (₱)</label><input id="partDRate" name="partDRate" type="number" min="0" max="99999999.99" step="0.01" required value="<?= h($edit['partDRate'] ?? '0.00') ?>"><p class="help">Column name: partDRate. The test sheet calculates fee paid as evRFee - partDRate.</p></div>
        </div>
        <div class="form-actions"><button class="btn" type="submit" <?= !$events ? 'disabled' : '' ?>><?= $edit ? 'Save Changes' : 'Add Participant' ?></button><?php if ($edit): ?><a class="btn btn-secondary" href="participants.php">Cancel Edit</a><?php endif; ?></div>
    </form>
</div>
<div class="card">
    <h2>Participant Records</h2>
    <form class="search-form" method="get">
        <div class="field"><label for="search">Search by ID, Name, or Event</label><input id="search" name="search" placeholder="Search participants..." value="<?= h($search) ?>"></div>
        <button class="btn" type="submit">Search</button><a class="btn btn-secondary" href="participants.php">Show All</a>
    </form>
    <div class="table-wrap"><table><thead><tr><th>Participant ID</th><th>First Name</th><th>Last Name</th><th>Event</th><th>Discount</th><th>Actions</th></tr></thead><tbody>
    <?php foreach ($participants as $p): ?>
        <tr><td><?= h($p['partID']) ?></td><td><?= h($p['partFName']) ?></td><td><?= h($p['partLName']) ?></td><td><?= h($p['evName']) ?></td><td><?= peso($p['partDRate']) ?></td>
        <td class="actions"><a href="participants.php?edit=<?= (int) $p['partID'] ?>">Edit</a>
        <form class="inline" method="post" onsubmit="return confirm('Delete this participant?');">
        <?php csrf_input(); ?><input type="hidden" name="action" value="delete"><input type="hidden" name="partID" value="<?= h($p['partID']) ?>"><button class="btn btn-danger btn-small" type="submit">Delete</button></form></td></tr>
    <?php endforeach; ?>
    <?php if (!$participants): ?><tr><td class="empty" colspan="6">No participants found.</td></tr><?php endif; ?>
    </tbody></table></div>
</div>
