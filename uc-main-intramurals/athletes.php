<?php
require_once __DIR__ . '/functions.php';
require_login();
$error = '';
$canManage = role_is('admin', 'athlete');
$athleteAccounts = people_in_role('athlete');
$coaches = db()->query('SELECT userName, fName, lName FROM Coach ORDER BY lName')->fetchAll();
$deans = db()->query('SELECT userName, fName, lName FROM Dean ORDER BY lName')->fetchAll();
$eventOptions = db()->query('SELECT eventID, eventName FROM `Event` ORDER BY eventName')->fetchAll();

function can_change_athlete(string $id): bool
{
    return role_is('admin') || (role_is('athlete') && $id === $_SESSION['userName']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    if (!$canManage) { http_response_code(403); exit('Access denied'); }

    // Never accept a different athlete ID from an athlete account.
    $id = role_is('athlete') ? $_SESSION['userName'] : trim($_POST['idNum'] ?? '');
    $action = $_POST['action'] ?? '';
    $eventID = (int) ($_POST['eventID'] ?? 0);
    $deptID = (int) ($_POST['deptID'] ?? 0);
    $last = trim($_POST['lastName'] ?? '');
    $first = trim($_POST['firstName'] ?? '');
    $middle = trim($_POST['middleInit'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $year = trim($_POST['year'] ?? '');
    $civilStatus = trim($_POST['civilStatus'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $birthdate = trim($_POST['birthdate'] ?? '');
    $contact = trim($_POST['contactNo'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $coachID = trim($_POST['coachID'] ?? '') ?: null;
    $deanID = trim($_POST['deanID'] ?? '') ?: null;

    $stmt = db()->prepare("SELECT COUNT(*) FROM Registration WHERE userName=? AND `role`='athlete'");
    $stmt->execute([$id]);
    $validAthlete = (int) $stmt->fetchColumn() === 1;
    $dateValid = DateTime::createFromFormat('!Y-m-d', $birthdate);
    if (!$validAthlete || !can_change_athlete($id)) {
        $error = 'Select an athlete account that has already registered.';
    } elseif ($eventID < 1 || $deptID < 1 || $last === '' || $first === '' || $course === '' || $year === '' || $civilStatus === '' || $gender === '' || !$dateValid || $dateValid->format('Y-m-d') !== $birthdate || $contact === '' || $address === '') {
        $error = 'Complete all required athlete profile fields.';
    } else {
        try {
            db()->beginTransaction();
            // Check the event capacity. Lock it while counting to avoid overbooking.
            $stmt = db()->prepare('SELECT noOfParticipant FROM `Event` WHERE eventID=? FOR UPDATE');
            $stmt->execute([$eventID]);
            $limit = $stmt->fetchColumn();
            if ($limit === false) {
                $error = 'Select an existing event.';
            } else {
                $stmt = db()->prepare('SELECT COUNT(*) FROM AthletesProfile WHERE eventID=? AND idNum<>?');
                $stmt->execute([$eventID, $id]);
                if ((int) $stmt->fetchColumn() >= (int) $limit) {
                    $error = 'This event is already full.';
                }
            }
            if ($error === '') {
                $stmt = db()->prepare('SELECT idNum FROM AthletesProfile WHERE idNum=?');
                $stmt->execute([$id]);
                $exists = (bool) $stmt->fetch();
                if ($exists) {
                    $stmt = db()->prepare('UPDATE AthletesProfile SET eventID=?, deptID=?, lastName=?, firstName=?, middleInit=?, course=?, `year`=?, civilStatus=?, gender=?, birthdate=?, contactNo=?, address=?, coachID=?, deanID=? WHERE idNum=?');
                    $stmt->execute([$eventID, $deptID, $last, $first, $middle, $course, $year, $civilStatus, $gender, $birthdate, $contact, $address, $coachID, $deanID, $id]);
                } else {
                    $stmt = db()->prepare('INSERT INTO AthletesProfile (idNum,eventID,deptID,lastName,firstName,middleInit,course,`year`,civilStatus,gender,birthdate,contactNo,address,coachID,deanID) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)');
                    $stmt->execute([$id, $eventID, $deptID, $last, $first, $middle, $course, $year, $civilStatus, $gender, $birthdate, $contact, $address, $coachID, $deanID]);
                }
                db()->commit();
                flash($exists ? 'Athlete profile updated.' : 'Athlete profile created.');
                go('athletes.php');
            }
            db()->rollBack();
        } catch (PDOException $e) {
            if (db()->inTransaction()) db()->rollBack();
            $error = 'Could not save profile. Check the event, department, coach, and dean selected.';
        }
    }
}

$edit = null;
if ($canManage) {
    $editingID = role_is('athlete') ? $_SESSION['userName'] : (string) ($_GET['edit'] ?? '');
    if ($editingID !== '') {
        $stmt = db()->prepare('SELECT * FROM AthletesProfile WHERE idNum=?');
        $stmt->execute([$editingID]);
        $maybeProfile = $stmt->fetch();
        if ($maybeProfile && can_change_athlete($editingID)) $edit = $maybeProfile;
    }
}

$rows = db()->query('SELECT a.*, e.eventName, d.depName, c.lName AS coachLastName, n.lName AS deanLastName
    FROM AthletesProfile a
    JOIN Event e ON a.eventID = e.eventID
    JOIN Department d ON a.deptID = d.deptID
    LEFT JOIN Coach c ON a.coachID = c.userName
    LEFT JOIN Dean n ON a.deanID = n.userName
    ORDER BY a.lastName, a.firstName')->fetchAll();
$pageTitle = 'Athletes Profile';
require __DIR__ . '/header.php';
?>
<?php if ($canManage): ?>
<div class="card">
    <h2><?= $edit ? 'Edit Athlete' : 'Create Athlete Profile' ?></h2>
    <p class="small">The athlete must first register an account, and a tournament manager must create an event.</p>
    <?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>
    <form method="post">
        <?= csrf_input() ?>
        <input type="hidden" name="action" value="save">
        <div class="fields-two">
            <div class="field"><label>Student / Employee ID</label>
                <?php if (role_is('athlete')): ?>
                    <input type="text" value="<?= h($_SESSION['userName']) ?>" readonly>
                <?php else: ?>
                    <select name="idNum" required><option value="">-- Select athlete account --</option>
                        <?php foreach ($athleteAccounts as $person): ?><option value="<?= h($person['userName']) ?>" <?= ($edit['idNum'] ?? '') === $person['userName'] ? 'selected' : '' ?>><?= h($person['userName']) ?></option><?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
            <div class="field"><label>Event</label><select name="eventID" required><option value="">-- Select event --</option>
                <?php foreach ($eventOptions as $e): ?><option value="<?= h($e['eventID']) ?>" <?= (int) ($edit['eventID'] ?? 0) === (int) $e['eventID'] ? 'selected' : '' ?>><?= h($e['eventName']) ?></option><?php endforeach; ?>
            </select></div>
            <div class="field"><label>Department</label><select name="deptID" required><option value="">-- Select department --</option>
                <?php foreach (departments() as $d): ?><option value="<?= h($d['deptID']) ?>" <?= (int) ($edit['deptID'] ?? 0) === (int) $d['deptID'] ? 'selected' : '' ?>><?= h($d['depName']) ?></option><?php endforeach; ?>
            </select></div>
            <div class="field"><label>Last Name</label><input type="text" name="lastName" required value="<?= h($edit['lastName'] ?? '') ?>"></div>
            <div class="field"><label>First Name</label><input type="text" name="firstName" required value="<?= h($edit['firstName'] ?? '') ?>"></div>
            <div class="field"><label>Middle Initial</label><input type="text" name="middleInit" maxlength="10" placeholder="Optional" value="<?= h($edit['middleInit'] ?? '') ?>"></div>
            <div class="field"><label>Course</label><input type="text" name="course" required placeholder="e.g. BSIT" value="<?= h($edit['course'] ?? '') ?>"></div>
            <div class="field"><label>Year</label><select name="year" required><option value="">-- Select year --</option>
                <?php foreach (['1st', '2nd', '3rd', '4th', '5th'] as $y): ?><option value="<?= h($y) ?>" <?= ($edit['year'] ?? '') === $y ? 'selected' : '' ?>><?= h($y) ?></option><?php endforeach; ?>
            </select></div>
            <div class="field"><label>Civil Status</label><select name="civilStatus" required><option value="">-- Select --</option>
                <?php foreach (['Single', 'Married', 'Other'] as $s): ?><option value="<?= h($s) ?>" <?= ($edit['civilStatus'] ?? '') === $s ? 'selected' : '' ?>><?= h($s) ?></option><?php endforeach; ?>
            </select></div>
            <div class="field"><label>Gender</label><select name="gender" required><option value="">-- Select --</option>
                <?php foreach (['Male', 'Female', 'Other', 'Prefer not to say'] as $s): ?><option value="<?= h($s) ?>" <?= ($edit['gender'] ?? '') === $s ? 'selected' : '' ?>><?= h($s) ?></option><?php endforeach; ?>
            </select></div>
            <div class="field"><label>Birthdate</label><input type="date" name="birthdate" required value="<?= h($edit['birthdate'] ?? '') ?>"></div>
            <div class="field"><label>Contact Number</label><input type="text" name="contactNo" required value="<?= h($edit['contactNo'] ?? '') ?>"></div>
            <div class="field"><label>Coach</label><select name="coachID"><option value="">-- None yet --</option>
                <?php foreach ($coaches as $c): ?><option value="<?= h($c['userName']) ?>" <?= ($edit['coachID'] ?? '') === $c['userName'] ? 'selected' : '' ?>><?= h($c['lName'] . ', ' . $c['fName']) ?></option><?php endforeach; ?>
            </select></div>
            <div class="field"><label>Dean</label><select name="deanID"><option value="">-- None yet --</option>
                <?php foreach ($deans as $d): ?><option value="<?= h($d['userName']) ?>" <?= ($edit['deanID'] ?? '') === $d['userName'] ? 'selected' : '' ?>><?= h($d['lName'] . ', ' . $d['fName']) ?></option><?php endforeach; ?>
            </select></div>
        </div>
        <div class="field"><label>Address</label><textarea name="address" rows="2" required><?= h($edit['address'] ?? '') ?></textarea></div>
        <button class="btn">Save Athlete Profile</button>
        <?php if ($edit && role_is('admin')): ?><a class="btn btn-muted" href="athletes.php">Clear / Add New</a><?php endif; ?>
    </form>
</div>
<?php endif; ?>
<div class="card">
    <h2>Athletes List</h2>
    <div class="table-wrap"><table>
        <thead><tr><th>ID</th><th>Athlete Name</th><th>Department</th><th>Course / Year</th><th>Event</th><th>Coach</th><th>Dean</th><?php if ($canManage): ?><th>Action</th><?php endif; ?></tr></thead>
        <tbody>
            <?php foreach ($rows as $a): ?>
                <tr><td><?= h($a['idNum']) ?></td><td><?= h($a['lastName'] . ', ' . $a['firstName']) ?></td><td><?= h($a['depName']) ?></td>
                    <td><?= h($a['course'] . ' / ' . $a['year']) ?></td><td><?= h($a['eventName']) ?></td>
                    <td><?= h($a['coachLastName'] ?? '—') ?></td><td><?= h($a['deanLastName'] ?? '—') ?></td>
                    <?php if ($canManage): ?><td><?= can_change_athlete($a['idNum']) ? '<a href="athletes.php?edit=' . urlencode($a['idNum']) . '">Edit</a>' : '—' ?></td><?php endif; ?>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?><tr><td colspan="8">No athlete profiles yet.</td></tr><?php endif; ?>
        </tbody>
    </table></div>
</div>
<?php require __DIR__ . '/footer.php'; ?>
