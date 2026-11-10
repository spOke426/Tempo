<?php
require_once __DIR__ . '/functions.php';
require_login();
$canManage = role_is('admin', 'tournament_manager');
$error = '';
$categories = ['Athletic', 'Cultural', 'Academic'];
$managers = db()->query('SELECT t.userName, t.fName, t.lName FROM TournamentManager t ORDER BY t.lName')->fetchAll();

// A tournament manager can change only their own events; admin can change any.
function event_owner_allowed(array $event): bool
{
    return role_is('admin') || (role_is('tournament_manager') && $event['tournamentManager'] === $_SESSION['userName']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (!$canManage) { http_response_code(403); exit('Access denied'); }
    $action = $_POST['action'] ?? '';
    $eventID = (int) ($_POST['eventID'] ?? 0);
    $category = $_POST['category'] ?? '';
    $name = trim($_POST['eventName'] ?? '');
    $capacity = (int) ($_POST['noOfParticipant'] ?? 0);
    $managerID = role_is('admin') ? trim($_POST['tournamentManager'] ?? '') : $_SESSION['userName'];

    try {
        $oldEvent = null;
        if ($action === 'edit' || $action === 'delete') {
            $stmt = db()->prepare('SELECT * FROM `Event` WHERE eventID = ?');
            $stmt->execute([$eventID]);
            $oldEvent = $stmt->fetch();
            if (!$oldEvent || !event_owner_allowed($oldEvent)) {
                throw new RuntimeException('Event not found or not yours.');
            }
        }
        if ($action === 'delete') {
            $stmt = db()->prepare('DELETE FROM `Event` WHERE eventID = ?');
            $stmt->execute([$eventID]);
            flash('Event deleted.');
            go('events.php');
        } elseif (!in_array($category, $categories, true) || $name === '' || $capacity < 1 || $managerID === '') {
            $error = 'Fill in the event name, category, participant limit, and manager.';
        } elseif ($action === 'add') {
            $stmt = db()->prepare('INSERT INTO `Event` (category, eventName, noOfParticipant, tournamentManager) VALUES (?, ?, ?, ?)');
            $stmt->execute([$category, $name, $capacity, $managerID]);
            flash('Event created.');
            go('events.php');
        } elseif ($action === 'edit') {
            $stmt = db()->prepare('SELECT COUNT(*) FROM AthletesProfile WHERE eventID = ?');
            $stmt->execute([$eventID]);
            $alreadyEnrolled = (int) $stmt->fetchColumn();
            if ($capacity < $alreadyEnrolled) {
                $error = 'Participant limit cannot be less than the current number enrolled (' . $alreadyEnrolled . ').';
            } else {
                $stmt = db()->prepare('UPDATE `Event` SET category=?, eventName=?, noOfParticipant=?, tournamentManager=? WHERE eventID=?');
                $stmt->execute([$category, $name, $capacity, $managerID, $eventID]);
                flash('Event updated.');
                go('events.php');
            }
        } else {
            $error = 'Unknown action.';
        }
    } catch (RuntimeException $e) {
        $error = $e->getMessage();
    } catch (PDOException $e) {
        $error = 'Could not save/delete event. Check the manager ID or remove related athlete profiles first.';
    }
}

$edit = null;
if ($canManage && isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM `Event` WHERE eventID = ?');
    $stmt->execute([(int) $_GET['edit']]);
    $test = $stmt->fetch();
    if ($test && event_owner_allowed($test)) $edit = $test;
}
$events = db()->query("SELECT e.*, t.fName AS managerFirstName, t.lName AS managerLastName,
    (SELECT COUNT(*) FROM AthletesProfile a WHERE a.eventID=e.eventID) AS enrolled
    FROM `Event` e JOIN TournamentManager t ON e.tournamentManager=t.userName ORDER BY e.eventID DESC")->fetchAll();
$pageTitle = 'Event Management';
require __DIR__ . '/header.php';
?>
<?php if ($canManage): ?>
<div class="card">
    <h2><?= $edit ? 'Edit Event' : 'Add an Event' ?></h2>
    <?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>
    <?php if (!$managers): ?><p class="error">Create a Tournament Manager account using Registration before adding events.</p><?php endif; ?>
    <form method="post">
        <?= csrf_input() ?>
        <input type="hidden" name="action" value="<?= $edit ? 'edit' : 'add' ?>">
        <?php if ($edit): ?><input type="hidden" name="eventID" value="<?= h($edit['eventID']) ?>"><?php endif; ?>
        <div class="fields-two">
            <div class="field"><label>Event Category</label><select name="category" required><option value="">-- Select --</option>
                <?php foreach ($categories as $c): ?><option value="<?= h($c) ?>" <?= ($edit['category'] ?? '') === $c ? 'selected' : '' ?>><?= h($c) ?></option><?php endforeach; ?>
            </select></div>
            <div class="field"><label>Event Name</label><input type="text" name="eventName" required placeholder="e.g. Volleyball Men" value="<?= h($edit['eventName'] ?? '') ?>"></div>
            <div class="field"><label>Number of Participants (Limit)</label><input type="number" name="noOfParticipant" min="1" required value="<?= h($edit['noOfParticipant'] ?? '') ?>"></div>
            <?php if (role_is('admin')): ?>
            <div class="field"><label>Tournament Manager</label><select name="tournamentManager" required><option value="">-- Select manager --</option>
                <?php foreach ($managers as $m): ?><option value="<?= h($m['userName']) ?>" <?= ($edit['tournamentManager'] ?? '') === $m['userName'] ? 'selected' : '' ?>><?= h($m['userName'] . ' - ' . $m['lName'] . ', ' . $m['fName']) ?></option><?php endforeach; ?>
            </select></div>
            <?php else: ?><div class="field"><label>Manager ID</label><input type="text" value="<?= h($_SESSION['userName']) ?>" readonly></div><?php endif; ?>
        </div>
        <button class="btn" type="submit"><?= $edit ? 'Update Event' : 'Add Event' ?></button>
        <?php if ($edit): ?><a class="btn btn-muted" href="events.php">Cancel Edit</a><?php endif; ?>
    </form>
</div>
<?php endif; ?>
<div class="card">
    <h2>List of Events</h2>
    <div class="table-wrap"><table>
        <thead><tr><th>Event ID</th><th>Category</th><th>Event</th><th>Participants</th><th>Manager</th><?php if ($canManage): ?><th>Actions</th><?php endif; ?></tr></thead>
        <tbody>
        <?php foreach ($events as $e): ?>
        <tr>
            <td><?= h($e['eventID']) ?></td><td><?= h($e['category']) ?></td><td><?= h($e['eventName']) ?></td>
            <td><?= h($e['enrolled']) ?> / <?= h($e['noOfParticipant']) ?></td><td><?= h($e['managerLastName'] . ', ' . $e['managerFirstName']) ?></td>
            <?php if ($canManage): ?><td>
                <?php if (event_owner_allowed($e)): ?><div class="actions">
                    <a href="events.php?edit=<?= urlencode($e['eventID']) ?>">Edit</a>
                    <form method="post" onsubmit="return confirm('Delete this event?');">
                        <?= csrf_input() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="eventID" value="<?= h($e['eventID']) ?>"><button class="btn btn-danger">Delete</button>
                    </form>
                </div><?php else: ?>—<?php endif; ?>
            </td><?php endif; ?>
        </tr>
        <?php endforeach; ?>
        <?php if (!$events): ?><tr><td colspan="6">No events yet. Register a tournament manager to create one.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
