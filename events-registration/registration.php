<?php
require_once 'functions.php';
$pageTitle = 'Participant Registration';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = $_POST['action'] ?? '';
    try {
        if ($action === 'delete') {
            $id = get_id($_POST['regCode'] ?? null);
            if (!$id) throw new Exception('Invalid registration.');
            $pdo->prepare('DELETE FROM Registration WHERE regCode=?')->execute([$id]);
            flash('Registration deleted.');
        } elseif ($action === 'register') {
            $partID = get_id($_POST['partID'] ?? null);
            $date = trim($_POST['regDate'] ?? '');
            $payment = trim($_POST['regPMode'] ?? '');
            if (!$partID || !valid_date($date) || !in_array($payment, ['Cash', 'Card'], true)) {
                throw new Exception('Enter a valid Participant ID, registration date and payment mode.');
            }
            $stmt = $pdo->prepare('SELECT p.partDRate, e.evRFee, e.evName FROM Participants p JOIN Events e ON e.evCode = p.evCode WHERE p.partID = ?');
            $stmt->execute([$partID]);
            $participant = $stmt->fetch();
            if (!$participant) throw new Exception('Participant ID not found. Add the participant first.');
            $stmt = $pdo->prepare('SELECT COUNT(*) FROM Registration WHERE partID=?');
            $stmt->execute([$partID]);
            if ($stmt->fetchColumn()) throw new Exception('This participant is already registered.');
            // Required formula from the test paper: fee paid = event fee - discount amount.
            $feePaid = round((float)$participant['evRFee'] - (float)$participant['partDRate'], 2);
            if ($feePaid < 0) throw new Exception('Discount must not exceed the event fee.');
            $pdo->prepare('INSERT INTO Registration (partID, regDate, regFPaid, regPMode) VALUES (?, ?, ?, ?)')
                ->execute([$partID, $date, $feePaid, $payment]);
            flash('Registration successful for ' . $participant['evName'] . '. Fee paid: ' . peso($feePaid));
        } else {
            throw new Exception('Unknown action.');
        }
    } catch (PDOException $e) {
        flash('Could not save registration. Check the participant ID and try again.', 'error');
    } catch (Exception $e) {
        flash($e->getMessage(), 'error');
    }
    redirect('registration.php');
}

$available = $pdo->query('SELECT p.partID, p.partFName, p.partLName, p.partDRate, e.evName, e.evRFee
    FROM Participants p JOIN Events e ON p.evCode=e.evCode
    LEFT JOIN Registration r ON p.partID=r.partID
    WHERE r.regCode IS NULL ORDER BY p.partID')->fetchAll();
$rows = $pdo->query('SELECT r.*, p.partFName, p.partLName, p.partDRate, e.evName, e.evRFee
    FROM Registration r JOIN Participants p ON r.partID=p.partID JOIN Events e ON p.evCode=e.evCode
    ORDER BY r.regDate DESC, r.regCode DESC')->fetchAll();
require 'header.php';
?>
<div class="card">
    <h2>Register a Participant to an Event</h2>
    <p class="muted small">Enter an existing Participant ID. The event comes from that participant's profile.</p>
    <form method="post">
        <?php csrf_input(); ?><input type="hidden" name="action" value="register">
        <div class="form-grid">
            <div class="field"><label for="partID">Participant ID</label><input id="partID" name="partID" type="number" min="1" placeholder="e.g. 1006" required></div>
            <div class="field"><label for="regDate">Registration Date</label><input id="regDate" name="regDate" type="date" value="<?= date('Y-m-d') ?>" required></div>
            <div class="field"><label for="regPMode">Payment Mode</label><select id="regPMode" name="regPMode" required><option value="Cash">Cash</option><option value="Card">Card</option></select></div>
        </div>
        <p class="note">Registration Fee Paid = Event Registration Fee (evRFee) − Participant Discount (partDRate). Calculated automatically when you save.</p>
        <button type="submit" class="btn">Register Participant</button>
    </form>
</div>
<div class="card">
    <h2>Participants Ready for Registration</h2>
    <div class="table-wrap"><table><thead><tr><th>Participant ID</th><th>Participant</th><th>Event</th><th>Event Fee</th><th>Discount</th><th>Fee to Pay</th></tr></thead><tbody>
    <?php foreach ($available as $p): ?><tr><td><?= h($p['partID']) ?></td><td><?= h($p['partFName'] . ' ' . $p['partLName']) ?></td><td><?= h($p['evName']) ?></td><td><?= peso($p['evRFee']) ?></td><td><?= peso($p['partDRate']) ?></td><td><strong><?= peso((float)$p['evRFee'] - (float)$p['partDRate']) ?></strong></td></tr><?php endforeach; ?>
    <?php if (!$available): ?><tr><td colspan="6" class="empty">No unregistered participants. Add one in Participants Management.</td></tr><?php endif; ?>
    </tbody></table></div>
</div>
<div class="card">
    <h2>Registration Transactions (Latest to Oldest)</h2>
    <div class="table-wrap"><table><thead><tr><th>Reg. Code</th><th>Participant</th><th>Event</th><th>Registration Date</th><th>Fee Paid</th><th>Payment</th><th>Action</th></tr></thead><tbody>
    <?php foreach ($rows as $r): ?><tr><td><?= h($r['regCode']) ?></td><td><?= h($r['partFName'] . ' ' . $r['partLName']) ?></td><td><?= h($r['evName']) ?></td><td><?= h($r['regDate']) ?></td><td><?= peso($r['regFPaid']) ?></td><td><?= h($r['regPMode']) ?></td><td>
        <form class="inline" method="post" onsubmit="return confirm('Delete this registration transaction?');">
            <?php csrf_input(); ?><input type="hidden" name="action" value="delete"><input type="hidden" name="regCode" value="<?= h($r['regCode']) ?>"><button class="btn btn-danger btn-small" type="submit">Delete</button>
        </form></td></tr><?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="7" class="empty">No registration records yet.</td></tr><?php endif; ?>
    </tbody></table></div>
</div>
