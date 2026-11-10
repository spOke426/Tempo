<?php
include "db.php";
include "header.php";
$message = "";

// ---------- ADD or UPDATE ----------
if (isset($_POST["save"])) {
    if ($_POST["editing"] == "") {
        run("INSERT INTO Candidates (candFName, candMName, candLName, posID) VALUES (?, ?, ?, ?)",
            [$_POST["candFName"], $_POST["candMName"], $_POST["candLName"], $_POST["posID"]]);
        $message = "Candidate added.";
    } else {
        run("UPDATE Candidates SET candFName = ?, candMName = ?, candLName = ?, posID = ? WHERE candID = ?",
            [$_POST["candFName"], $_POST["candMName"], $_POST["candLName"], $_POST["posID"], $_POST["editing"]]);
        $message = "Candidate updated.";
    }
}

// ---------- DEACTIVATE / ACTIVATE ----------
if (isset($_POST["toggle"])) {
    run("UPDATE Candidates SET candStat = IF(candStat = 'active', 'inactive', 'active') WHERE candID = ?",
        [$_POST["toggle"]]);
    $message = "Candidate status changed.";
}

// ---------- Load a record into the form when Edit is clicked ----------
$row = ["candID" => "", "candFName" => "", "candMName" => "", "candLName" => "", "posID" => ""];
$editing = "";
if (isset($_GET["edit"])) {
    $row = run("SELECT * FROM Candidates WHERE candID = ?", [$_GET["edit"]])->fetch_assoc();
    $editing = $row["candID"];
}

$positions = run("SELECT posID, posName FROM Positions");
$list = run("SELECT c.*, p.posName FROM Candidates c JOIN Positions p ON c.posID = p.posID ORDER BY c.posID, c.candID");
?>

<h2>Candidates Management</h2>
<p class="msg"><?= $message ?></p>

<form method="post" class="box">
    <input type="hidden" name="editing" value="<?= h($editing) ?>">
    First Name:<br>
    <input name="candFName" value="<?= h($row["candFName"]) ?>" required><br>
    Middle Name:<br>
    <input name="candMName" value="<?= h($row["candMName"]) ?>"><br>
    Last Name:<br>
    <input name="candLName" value="<?= h($row["candLName"]) ?>" required><br>
    Position:<br>
    <select name="posID" required>
        <option value="">-- choose position --</option>
        <?php while ($p = $positions->fetch_assoc()) { ?>
            <option value="<?= $p["posID"] ?>" <?= $p["posID"] == $row["posID"] ? "selected" : "" ?>>
                <?= h($p["posName"]) ?>
            </option>
        <?php } ?>
    </select><br><br>
    <button name="save">Save</button>
    <a href="candidates.php">Clear</a>
</form>

<table>
    <tr><th>ID</th><th>First</th><th>Middle</th><th>Last</th><th>Position</th><th>Status</th><th>Action</th></tr>
    <?php while ($r = $list->fetch_assoc()) { ?>
    <tr>
        <td><?= h($r["candID"]) ?></td>
        <td><?= h($r["candFName"]) ?></td>
        <td><?= h($r["candMName"]) ?></td>
        <td><?= h($r["candLName"]) ?></td>
        <td><?= h($r["posName"]) ?></td>
        <td><?= h($r["candStat"]) ?></td>
        <td>
            <a href="candidates.php?edit=<?= $r["candID"] ?>">Edit</a>
            <form method="post" style="display:inline">
                <button name="toggle" value="<?= $r["candID"] ?>">
                    <?= $r["candStat"] == "active" ? "Deactivate" : "Activate" ?>
                </button>
            </form>
        </td>
    </tr>
    <?php } ?>
</table>

<?php include "footer.php"; ?>
