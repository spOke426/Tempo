<?php
include "db.php";
include "header.php";
$message = "";

// ---------- CREATE or UPDATE (when Save is clicked) ----------
if (isset($_POST["save"])) {
    if ($_POST["editing"] == "") {
        // no ID being edited -> this is a NEW record
        run("INSERT INTO Veterinarian (vetID, vetFName, vetLName, vetAddress, vetSpecial)
             VALUES (?, ?, ?, ?, ?)",
            [$_POST["vetID"], $_POST["vetFName"], $_POST["vetLName"], $_POST["vetAddress"], $_POST["vetSpecial"]]);
        $message = "Veterinarian added.";
    } else {
        // an ID is being edited -> UPDATE it
        run("UPDATE Veterinarian SET vetFName = ?, vetLName = ?, vetAddress = ?, vetSpecial = ?
             WHERE vetID = ?",
            [$_POST["vetFName"], $_POST["vetLName"], $_POST["vetAddress"], $_POST["vetSpecial"], $_POST["editing"]]);
        $message = "Veterinarian updated.";
    }
}

// ---------- LOGICAL DELETE (record stays in the database, only isDeleted becomes 1) ----------
if (isset($_POST["delete"])) {
    run("UPDATE Veterinarian SET isDeleted = 1 WHERE vetID = ?", [$_POST["delete"]]);
    $message = "Veterinarian deleted.";
}

// ---------- If Edit was clicked, load that record into the form ----------
$row = ["vetID" => "", "vetFName" => "", "vetLName" => "", "vetAddress" => "", "vetSpecial" => ""];
$editing = "";
if (isset($_GET["edit"])) {
    $row = run("SELECT * FROM Veterinarian WHERE vetID = ?", [$_GET["edit"]])->fetch_assoc();
    $editing = $row["vetID"];
}

// ---------- READ and SEARCH ----------
$search = $_GET["search"] ?? "";
$list = run("SELECT * FROM Veterinarian
             WHERE isDeleted = 0
             AND (vetID LIKE ? OR vetFName LIKE ? OR vetLName LIKE ? OR vetSpecial LIKE ?)",
            ["%$search%", "%$search%", "%$search%", "%$search%"]);
?>

<h2>Veterinarian Management</h2>
<p class="msg"><?= $message ?></p>

<!-- FORM: create / update -->
<form method="post" class="box">
    <input type="hidden" name="editing" value="<?= h($editing) ?>">
    Vet License No.:<br>
    <input name="vetID" value="<?= h($row["vetID"]) ?>" <?= $editing ? "readonly" : "required" ?>><br>
    First Name:<br>
    <input name="vetFName" value="<?= h($row["vetFName"]) ?>" required><br>
    Last Name:<br>
    <input name="vetLName" value="<?= h($row["vetLName"]) ?>" required><br>
    Address:<br>
    <input name="vetAddress" value="<?= h($row["vetAddress"]) ?>" required><br>
    Specialization:<br>
    <input name="vetSpecial" value="<?= h($row["vetSpecial"]) ?>" required><br><br>
    <button name="save">Save</button>
    <a href="veterinarian.php">Clear</a>
</form>

<!-- FORM: search -->
<form method="get">
    <input name="search" placeholder="Search..." value="<?= h($search) ?>">
    <button>Search</button>
</form>

<!-- TABLE: list of records -->
<table>
    <tr><th>License No.</th><th>First Name</th><th>Last Name</th><th>Address</th><th>Specialization</th><th>Action</th></tr>
    <?php while ($r = $list->fetch_assoc()) { ?>
    <tr>
        <td><?= h($r["vetID"]) ?></td>
        <td><?= h($r["vetFName"]) ?></td>
        <td><?= h($r["vetLName"]) ?></td>
        <td><?= h($r["vetAddress"]) ?></td>
        <td><?= h($r["vetSpecial"]) ?></td>
        <td>
            <a href="veterinarian.php?edit=<?= urlencode($r["vetID"]) ?>">Edit</a>
            <form method="post" style="display:inline" onsubmit="return confirm('Delete this record?')">
                <button name="delete" value="<?= h($r["vetID"]) ?>">Delete</button>
            </form>
        </td>
    </tr>
    <?php } ?>
</table>

<?php include "footer.php"; ?>
