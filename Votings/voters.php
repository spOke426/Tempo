<?php
include "db.php";
include "header.php";
$message = "";

// ---------- ADD or UPDATE ----------
if (isset($_POST["save"])) {
    if ($_POST["editing"] == "") {
        run("INSERT INTO Voters (voterID, voterPass, voterFName, voterMName, voterLName) VALUES (?, ?, ?, ?, ?)",
            [$_POST["voterID"], $_POST["voterPass"], $_POST["voterFName"], $_POST["voterMName"], $_POST["voterLName"]]);
        $message = "Voter added.";
    } else {
        run("UPDATE Voters SET voterPass = ?, voterFName = ?, voterMName = ?, voterLName = ? WHERE voterID = ?",
            [$_POST["voterPass"], $_POST["voterFName"], $_POST["voterMName"], $_POST["voterLName"], $_POST["editing"]]);
        $message = "Voter updated.";
    }
}

// ---------- DEACTIVATE / ACTIVATE ----------
if (isset($_POST["toggle"])) {
    run("UPDATE Voters SET voterStat = IF(voterStat = 'active', 'inactive', 'active') WHERE voterID = ?",
        [$_POST["toggle"]]);
    $message = "Voter status changed.";
}

// ---------- Load a record into the form when Edit is clicked ----------
$row = ["voterID" => "", "voterPass" => "", "voterFName" => "", "voterMName" => "", "voterLName" => ""];
$editing = "";
if (isset($_GET["edit"])) {
    $row = run("SELECT * FROM Voters WHERE voterID = ?", [$_GET["edit"]])->fetch_assoc();
    $editing = $row["voterID"];
}

$list = run("SELECT * FROM Voters ORDER BY voterID");
?>

<h2>Voters Management</h2>
<p class="msg"><?= $message ?></p>

<form method="post" class="box">
    <input type="hidden" name="editing" value="<?= h($editing) ?>">
    Voter ID (username):<br>
    <input name="voterID" value="<?= h($row["voterID"]) ?>" <?= $editing ? "readonly" : "required" ?>><br>
    Password:<br>
    <input name="voterPass" value="<?= h($row["voterPass"]) ?>" required><br>
    First Name:<br>
    <input name="voterFName" value="<?= h($row["voterFName"]) ?>" required><br>
    Middle Name:<br>
    <input name="voterMName" value="<?= h($row["voterMName"]) ?>"><br>
    Last Name:<br>
    <input name="voterLName" value="<?= h($row["voterLName"]) ?>" required><br><br>
    <button name="save">Save</button>
    <a href="voters.php">Clear</a>
</form>

<table>
    <tr><th>Voter ID</th><th>Name</th><th>Status</th><th>Voted?</th><th>Action</th></tr>
    <?php while ($r = $list->fetch_assoc()) { ?>
    <tr>
        <td><?= h($r["voterID"]) ?></td>
        <td><?= h($r["voterFName"] . " " . $r["voterMName"] . " " . $r["voterLName"]) ?></td>
        <td><?= h($r["voterStat"]) ?></td>
        <td><?= h($r["voted"]) ?></td>
        <td>
            <a href="voters.php?edit=<?= urlencode($r["voterID"]) ?>">Edit</a>
            <form method="post" style="display:inline">
                <button name="toggle" value="<?= h($r["voterID"]) ?>">
                    <?= $r["voterStat"] == "active" ? "Deactivate" : "Activate" ?>
                </button>
            </form>
        </td>
    </tr>
    <?php } ?>
</table>

<?php include "footer.php"; ?>
