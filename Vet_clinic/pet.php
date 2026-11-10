<?php
include "db.php";
include "header.php";
$message = "";

// ---------- CREATE or UPDATE ----------
if (isset($_POST["save"])) {
    if ($_POST["editing"] == "") {
        run("INSERT INTO Pet (petID, petName, petType, petBreed, petBDate, petOwnerID)
             VALUES (?, ?, ?, ?, ?, ?)",
            [$_POST["petID"], $_POST["petName"], $_POST["petType"], $_POST["petBreed"], $_POST["petBDate"], $_POST["petOwnerID"]]);
        $message = "Pet added.";
    } else {
        run("UPDATE Pet SET petName = ?, petType = ?, petBreed = ?, petBDate = ?, petOwnerID = ?
             WHERE petID = ?",
            [$_POST["petName"], $_POST["petType"], $_POST["petBreed"], $_POST["petBDate"], $_POST["petOwnerID"], $_POST["editing"]]);
        $message = "Pet updated.";
    }
}

// ---------- LOGICAL DELETE ----------
if (isset($_POST["delete"])) {
    run("UPDATE Pet SET isDeleted = 1 WHERE petID = ?", [$_POST["delete"]]);
    $message = "Pet deleted.";
}

// ---------- Load a record into the form when Edit is clicked ----------
$row = ["petID" => "", "petName" => "", "petType" => "", "petBreed" => "", "petBDate" => "", "petOwnerID" => ""];
$editing = "";
if (isset($_GET["edit"])) {
    $row = run("SELECT * FROM Pet WHERE petID = ?", [$_GET["edit"]])->fetch_assoc();
    $editing = $row["petID"];
}

// Owners for the dropdown list
$owners = run("SELECT petOwnerID, petOwnerFName, petOwnerLName FROM PetOwner WHERE isDeleted = 0");

// ---------- READ and SEARCH ----------
$search = $_GET["search"] ?? "";
$list = run("SELECT * FROM Pet
             WHERE isDeleted = 0
             AND (petID LIKE ? OR petName LIKE ? OR petType LIKE ? OR petBreed LIKE ? OR petOwnerID LIKE ?)",
            ["%$search%", "%$search%", "%$search%", "%$search%", "%$search%"]);
?>

<h2>Pet Management</h2>
<p class="msg"><?= $message ?></p>

<form method="post" class="box">
    <input type="hidden" name="editing" value="<?= h($editing) ?>">
    Pet ID:<br>
    <input name="petID" value="<?= h($row["petID"]) ?>" <?= $editing ? "readonly" : "required" ?>><br>
    Name:<br>
    <input name="petName" value="<?= h($row["petName"]) ?>" required><br>
    Type (Dog, Cat, etc.):<br>
    <input name="petType" value="<?= h($row["petType"]) ?>" required><br>
    Breed:<br>
    <input name="petBreed" value="<?= h($row["petBreed"]) ?>" required><br>
    Birth Date:<br>
    <input type="date" name="petBDate" value="<?= h($row["petBDate"]) ?>" required><br>
    Owner:<br>
    <select name="petOwnerID" required>
        <option value="">-- choose owner --</option>
        <?php while ($o = $owners->fetch_assoc()) { ?>
            <option value="<?= h($o["petOwnerID"]) ?>" <?= $o["petOwnerID"] == $row["petOwnerID"] ? "selected" : "" ?>>
                <?= h($o["petOwnerID"] . " - " . $o["petOwnerFName"] . " " . $o["petOwnerLName"]) ?>
            </option>
        <?php } ?>
    </select><br><br>
    <button name="save">Save</button>
    <a href="pet.php">Clear</a>
</form>

<form method="get">
    <input name="search" placeholder="Search..." value="<?= h($search) ?>">
    <button>Search</button>
</form>

<table>
    <tr><th>ID</th><th>Name</th><th>Type</th><th>Breed</th><th>Birth Date</th><th>Owner ID</th><th>Action</th></tr>
    <?php while ($r = $list->fetch_assoc()) { ?>
    <tr>
        <td><?= h($r["petID"]) ?></td>
        <td><?= h($r["petName"]) ?></td>
        <td><?= h($r["petType"]) ?></td>
        <td><?= h($r["petBreed"]) ?></td>
        <td><?= h($r["petBDate"]) ?></td>
        <td><?= h($r["petOwnerID"]) ?></td>
        <td>
            <a href="pet.php?edit=<?= urlencode($r["petID"]) ?>">Edit</a>
            <form method="post" style="display:inline" onsubmit="return confirm('Delete this record?')">
                <button name="delete" value="<?= h($r["petID"]) ?>">Delete</button>
            </form>
        </td>
    </tr>
    <?php } ?>
</table>

<?php include "footer.php"; ?>
