<?php
require_once 'functions.php';
$pageTitle = 'Events Management';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'delete') {
            $id = get_id($_POST['evCode'] ?? null);
            if (!$id) throw new Exception('Invalid event.');
            $check = $pdo->prepare('SELECT COUNT(*) FROM Participants WHERE evCode = ?');
            $check->execute([$id]);
            if ($check->fetchColumn() > 0) {
                throw new Exception('This event has participants. Delete their registrations and participant records first.');
            }
            $pdo->prepare('DELETE FROM Events WHERE evCode = ?')->execute([$id]);
            flash('Event deleted.');
        } elseif ($action === 'save') {
            $id = get_id($_POST['evCode'] ?? null);
            $name = trim($_POST['evName'] ?? '');
            $date = trim($_POST['evDate'] ?? '');
            $venue = trim($_POST['evVenue'] ?? '');
            $feeInput = trim($_POST['evRFee'] ?? '');
            if ($name === '' || $venue === '' || !valid_date($date) || !valid_amount($feeInput)) {
                throw new Exception('Enter a valid event name, date, venue and non-negative fee (up to 2 decimals).');
            }
            $fee = (float) $feeInput;
            if ($fee > 99999999.99) throw new Exception('The event fee is too large.');
            if ($id) {
                // A fee already used for a registration must not silently change history.
                $previous = $pdo->prepare('SELECT evRFee FROM Events WHERE evCode = ?');
                $previous->execute([$id]);
                $old = $previous->fetchColumn();
                if ($old === false) throw new Exception('Event not found.');
                $count = $pdo->prepare('SELECT COUNT(*) FROM Registration r JOIN Participants p ON p.partID = r.partID WHERE p.evCode = ?');
                $count->execute([$id]);
                if ($count->fetchColumn() > 0 && abs((float) $old - $fee) > 0.001) {
                    throw new Exception('Cannot change the fee after registrations. Delete its registrations first.');
                }
                $pdo->prepare('UPDATE Events SET evName=?, evDate=?, evVenue=?, evRFee=? WHERE evCode=?')
                    ->execute([$name, $date, $venue, $fee, $id]);
                flash('Event updated.');
            } else {
                $pdo->prepare('INSERT INTO Events (evName, evDate, evVenue, evRFee) VALUES (?, ?, ?, ?)')
                    ->execute([$name, $date, $venue, $fee]);
                flash('Event added.');
            }
        } else {
            throw new Exception('Unknown action.');
        }
    } catch (Exception $e) {
        flash($e->getMessage(), 'error');
    }
    redirect('events.php');
}

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare('SELECT * FROM Events WHERE evCode=?');
    $stmt->execute([get_id($_GET['edit'])]);
    $edit = $stmt->fetch() ?: null;
}
$search = trim($_GET['search'] ?? '');
$stmt = $pdo->prepare('SELECT * FROM Events WHERE evName LIKE ? OR CAST(evCode AS CHAR) LIKE ? ORDER BY evCode DESC');
$stmt->execute(['%' . $search . '%', '%' . $search . '%']);
$events = $stmt->fetchAll();
require 'header.php';
?>
<div class="card">
    <h2><?= $edit ? 'Update Event' : 'Add an Event' ?></h2>
    <form method="post">
        <?php csrf_input(); ?>
        <input type="hidden" name="action" value="save">
        <input type="hidden" name="evCode" value="<?= h($edit['evCode'] ?? '') ?>">
        <div class="form-grid">
            <div class="field"><label for="evName">Event Name</label><input id="evName" name="evName" maxlength="150" required value="<?= h($edit['evName'] ?? '') ?>" placeholder="e.g. Tech Seminar"></div>
            <div class="field"><label for="evDate">Event Date</label><input id="evDate" name="evDate" type="date" required value="<?= h($edit['evDate'] ?? '') ?>"></div>
            <div class="field"><label for="evVenue">Event Venue</label><input id="evVenue" name="evVenue" maxlength="150" required value="<?= h($edit['evVenue'] ?? '') ?>" placeholder="e.g. UC Main Gym"></div>
            <div class="field"><label for="evRFee">Registration Fee (₱)</label><input id="evRFee" name="evRFee" type="number" min="0" max="99999999.99" step="0.01" required value="<?= h($edit['evRFee'] ?? '') ?>"></div>
        </div>
        <div class="form-actions"><button class="btn" type="submit"><?= $edit ? 'Save Changes' : 'Add Event' ?></button><?php if ($edit): ?><a href="events.php" class="btn btn-secondary">Cancel Edit</a><?php endif; ?></div>
    </form>
</div>
<div class="card">
    <h2>Event Records</h2>
    <form class="search-form" method="get">
        <div class="field"><label for="search">Search by Code or Event Name</label><input id="search" name="search" value="<?= h($search) ?>" placeholder="Search events..."></div>
        <button type="submit" class="btn">Search</button><a class="btn btn-secondary" href="events.php">Show All</a>
    </form>
    <div class="table-wrap"><table><thead><tr><th>Code</th><th>Event Name</th><th>Date</th><th>Venue</th><th>Registration Fee</th><th>Actions</th></tr></thead><tbody>
    <?php foreach ($events as $event): ?>
        <tr><td><?= h($event['evCode']) ?></td><td><?= h($event['evName']) ?></td><td><?= h($event['evDate']) ?></td><td><?= h($event['evVenue']) ?></td><td><?= peso($event['evRFee']) ?></td>
            <td class="actions"><a href="events.php?edit=<?= (int) $event['evCode'] ?>">Edit</a>
                <form class="inline" method="post" onsubmit="return confirm('Delete this event?');">
                    <?php csrf_input(); ?><input type="hidden" name="action" value="delete"><input type="hidden" name="evCode" value="<?= h($event['evCode']) ?>"><button type="submit" class="btn btn-danger btn-small">Delete</button>
                </form></td></tr>
    <?php endforeach; ?>
    <?php if (!$events): ?><tr><td class="empty" colspan="6">No events found.</td></tr><?php endif; ?>
    </tbody></table></div>
</div>
