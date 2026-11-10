<?php
require_once 'functions.php';
$pageTitle = 'Events Registration Monitoring';
$events = $pdo->query('SELECT * FROM Events ORDER BY evName')->fetchAll();

// Filter by event name (displayed in dropdown); value uses its unique event code.
$selectedID = get_id($_GET['evCode'] ?? null);
if (!$selectedID && $events) $selectedID = (int)$events[0]['evCode'];
$selectedEvent = null;
foreach ($events as $event) {
    if ((int)$event['evCode'] === $selectedID) {
        $selectedEvent = $event;
        break;
    }
}
$rows = [];
$count = 0;
$totalPaid = 0;
$totalDiscount = 0;
if ($selectedEvent) {
    $stmt = $pdo->prepare('SELECT e.evName, p.partFName, p.partLName, r.regDate, r.regFPaid
        FROM Registration r JOIN Participants p ON p.partID=r.partID JOIN Events e ON e.evCode=p.evCode
        WHERE e.evCode=? ORDER BY r.regDate DESC, r.regCode DESC');
    $stmt->execute([$selectedID]);
    $rows = $stmt->fetchAll();

    // SQL aggregate functions specifically required by the test instructions.
    $stmt = $pdo->prepare('SELECT COUNT(*) AS totalRecords, COALESCE(SUM(r.regFPaid),0) AS totalFees
        FROM Registration r JOIN Participants p ON p.partID=r.partID WHERE p.evCode=?');
    $stmt->execute([$selectedID]);
    $totals = $stmt->fetch();
    $count = (int)$totals['totalRecords'];
    $totalPaid = (float)$totals['totalFees'];
    $totalDiscount = round(($count * (float)$selectedEvent['evRFee']) - $totalPaid, 2);
}
require 'header.php';
?>
<div class="card">
    <h2>Filter by Event's Name</h2>
    <form method="get" class="search-form">
        <div class="field"><label for="evCode">Event Name</label><select id="evCode" name="evCode" required>
        <?php foreach ($events as $event): ?><option value="<?= h($event['evCode']) ?>" <?= $selectedID === (int)$event['evCode'] ? 'selected' : '' ?>><?= h($event['evName']) ?></option><?php endforeach; ?>
        </select></div>
        <button class="btn" type="submit">Filter Records</button>
    </form>
    <?php if (!$events): ?><p class="note">No events yet. <a href="events.php">Add an event first.</a></p><?php endif; ?>
</div>
<?php if ($selectedEvent): ?>
<div class="card">
    <h2>Registration Records — <?= h($selectedEvent['evName']) ?></h2>
    <p class="small muted">Event Fee: <?= peso($selectedEvent['evRFee']) ?> · Event Date: <?= h($selectedEvent['evDate']) ?> · Venue: <?= h($selectedEvent['evVenue']) ?></p>
    <div class="table-wrap"><table><thead><tr><th>Event's Name</th><th>Participant's Full Name</th><th>Registration Date</th><th>Registration Fee Paid</th></tr></thead><tbody>
    <?php foreach ($rows as $row): ?><tr><td><?= h($row['evName']) ?></td><td><?= h($row['partFName'] . ' ' . $row['partLName']) ?></td><td><?= h($row['regDate']) ?></td><td><?= peso($row['regFPaid']) ?></td></tr><?php endforeach; ?>
    <?php if (!$rows): ?><tr><td colspan="4" class="empty">No registrations for this event.</td></tr><?php endif; ?>
    </tbody></table></div>
</div>
<div class="stat-grid">
    <div class="stat"><div class="value"><?= $count ?></div><div class="label">COUNT(*) — Number of Registration Records</div></div>
    <div class="stat"><div class="value"><?= peso($totalPaid) ?></div><div class="label">SUM(regFPaid) — Total Fees Paid</div></div>
    <div class="stat"><div class="value"><?= peso($totalDiscount) ?></div><div class="label">(COUNT × evRFee) − SUM(regFPaid) — Total Discounts</div></div>
</div>
<div class="note">The three totals follow the formulas on your skills-test paper. The <strong>discount</strong> is treated as a peso amount because the paper defines fee paid as <strong>evRFee − partDRate</strong>.</div>
<?php endif; ?>
