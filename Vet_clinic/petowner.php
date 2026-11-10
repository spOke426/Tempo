<?php
include "db.php";
include "header.php";
$message = "";

// ---------- CREATE or UPDATE ----------
if (isset($_POST["save"])) {
    if ($_POST["editing"] == "") {
        run("INSERT INTO PetOwner (petOwnerID, petOwnerFName, petOwnerLName, petOwnerBDate, petOwnerTelNo)
             VALUES (?, ?, ?, ?, ?)",
            [$_POST["petOwnerID"], $_POST["petOwnerFName"], $_POST["petOwnerLName"], $_POST["petOwnerBDate"], $_POST["petOwnerTelNo"]]);
        $message = "Pet owner added.";
    } else {
        run("UPDATE PetOwner SET petOwnerFName = ?, petOwnerLName = ?, petOwnerBDate = ?, petOwnerTelNo = ?
             WHERE petOwnerID = ?",
            [$_POST["petOwnerFName"], $_POST["petOwnerLName"], $_POST["petOwnerBDate"], $_POST["petOwnerTelNo"], $_POST["editing"]]);
        $message = "Pet owner updated.";
    }
}

// ---------- LOGICAL DELETE ----------
if (isset($_POST["delete"])) {
    run("UPDATE PetOwner SET isDeleted = 1 WHERE petOwnerID = ?", [$_POST["delete"]]);
    $message = "Pet owner deleted.";
}

// ---------- Load a record into the form when Edit is clicked ----------
$row = ["petOwnerID" => "", "petOwnerFName" => "", "petOwnerLName" => "", "petOwnerBDate" => "", "petOwnerTelNo" => ""];
$editing = "";
if (isset($_GET["edit"])) {
    $row = run("SELECT * FROM PetOwner WHERE petOwnerID = ?", [$_GET["edit"]])->fetch_assoc();
    $editing = $row["petOwnerID"];
}

// ---------- READ and SEARCH ----------
$search = $_GET["search"] ?? "";
$list = run("SELECT * FROM PetOwner
             WHERE isDeleted = 0
             AND (petOwnerID LIKE ? OR petOwnerFName LIKE ? OR petOwnerLName LIKE ? OR petOwnerTelNo LIKE ?)",
            ["%$search%", "%$search%", "%$search%", "%$search%"]);
?>

<h2>Pet Owner Management</h2>
<p class="msg"><?= $message ?></p>

<form method="post" class="box">
    <input type="hidden" name="editing" value="<?= h($editing) ?>">
    Pet Owner ID:<br>
    <input name="petOwnerID" value="<?= h($row["petOwnerID"]) ?>" <?= $editing ? "readonly" : "required" ?>><br>
    First Name:<br>
    <input name="petOwnerFName" value="<?= h($row["petOwnerFName"]) ?>" required><br>
    Last Name:<br>
    <input name="petOwnerLName" value="<?= h($row["petOwnerLName"]) ?>" required><br>
    Birth Date:<br>
    <input type="date" name="petOwnerBDate" value="<?= h($row["petOwnerBDate"]) ?>" required><br>
    Telephone No.:<br>
    <input name="petOwnerTelNo" value="<?= h($row["petOwnerTelNo"]) ?>" required><br><br>
    <button name="save">Save</button>
    <a href="petowner.php">Clear</a>
</form>

<form method="get">
    <input name="search" placeholder="Search..." value="<?= h($search) ?>">
    <button>Search</button>
</form>

<table>
    <tr><th>ID</th><th>First Name</th><th>Last Name</th><th>Birth Date</th><th>Telephone</th><th>Action</th></tr>
    <?php while ($r = $list->fetch_assoc()) { ?>
    <tr>
        <td><?= h($r["petOwnerID"]) ?></td>
        <td><?= h($r["petOwnerFName"]) ?></td>
        <td><?= h($r["petOwnerLName"]) ?></td>
        <td><?= h($r["petOwnerBDate"]) ?></td>
        <td><?= h($r["petOwnerTelNo"]) ?></td>
        <td>
            <a href="petowner.php?edit=<?= urlencode($r["petOwnerID"]) ?>">Edit</a>
            <form method="post" style="display:inline" onsubmit="return confirm('Delete this record?')">
                <button name="delete" value="<?= h($r["petOwnerID"]) ?>">Delete</button>
            </form>
        </td>
    </tr>
    <?php } ?>
</table>

<?php include "footer.php"; ?>
