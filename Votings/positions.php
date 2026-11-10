<?php
include "db.php";
include "header.php";
$message = "";

// ---------- ADD or UPDATE ----------
if (isset($_POST["save"])) {
    if ($_POST["editing"] == "") {
        run("INSERT INTO Positions (posName, numOfPositions) VALUES (?, ?)",
            [$_POST["posName"], $_POST["numOfPositions"]]);
        $message = "Position added.";
    } else {
        run("UPDATE Positions SET posName = ?, numOfPositions = ? WHERE posID = ?",
            [$_POST["posName"], $_POST["numOfPositions"], $_POST["editing"]]);
        $message = "Position updated.";
    }
}

// ---------- DEACTIVATE / ACTIVATE (open <-> closed) ----------
if (isset($_POST["toggle"])) {
    run("UPDATE Positions SET posStat = IF(posStat = 'open', 'closed', 'open') WHERE posID = ?",
        [$_POST["toggle"]]);
    $message = "Position status changed.";
}

// ---------- Load a record into the form when Edit is clicked ----------
$row = ["posID" => "", "posName" => "", "numOfPositions" => ""];
$editing = "";
if (isset($_GET["edit"])) {
    $row = run("SELECT * FROM Positions WHERE posID = ?", [$_GET["edit"]])->fetch_assoc();
    $editing = $row["posID"];
}

$list = run("SELECT * FROM Positions ORDER BY posID");
?>

<h2>Positions Management</h2>
<p class="msg"><?= $message ?></p>

<form method="post" class="box">
    <input type="hidden" name="editing" value="<?= h($editing) ?>">
    Position Name:<br>
    <input name="posName" value="<?= h($row["posName"]) ?>" required><br>
    Number of Positions (e.g. 12 for Senator):<br>
    <input type="number" min="1" name="numOfPositions" value="<?= h($row["numOfPositions"]) ?>" required><br><br>
    <button name="save">Save</button>
    <a href="positions.php">Clear</a>
</form>

<table>
    <tr><th>ID</th><th>Position</th><th>No. of Positions</th><th>Status</th><th>Action</th></tr>
    <?php while ($r = $list->fetch_assoc()) { ?>
    <tr>
        <td><?= h($r["posID"]) ?></td>
        <td><?= h($r["posName"]) ?></td>
        <td><?= h($r["numOfPositions"]) ?></td>
        <td><?= h($r["posStat"]) ?></td>
        <td>
            <a href="positions.php?edit=<?= $r["posID"] ?>">Edit</a>
            <form method="post" style="display:inline">
                <button name="toggle" value="<?= $r["posID"] ?>">
                    <?= $r["posStat"] == "open" ? "Deactivate" : "Activate" ?>
                </button>
            </form>
        </td>
    </tr>
    <?php } ?>
</table>

<?php include "footer.php"; ?>
