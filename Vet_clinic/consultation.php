<?php
include "db.php";
include "header.php";
$message = "";

// datetime-local gives "2026-09-01T09:00". MySQL wants "2026-09-01 09:00", so swap the T for a space.
$date = str_replace("T", " ", $_POST["consultDate"] ?? "");

// ---------- CREATE or UPDATE ----------
if (isset($_POST["save"])) {
    if ($_POST["editing"] == "") {
        run("INSERT INTO Consultation (consultID, petID, vetID, consultDate, diagnoses, prescription)
             VALUES (?, ?, ?, ?, ?, ?)",
            [$_POST["consultID"], $_POST["petID"], $_POST["vetID"], $date, $_POST["diagnoses"], $_POST["prescription"]]);
        $message = "Consultation added.";
    } else {
        run("UPDATE Consultation SET petID = ?, vetID = ?, consultDate = ?, diagnoses = ?, prescription = ?
             WHERE consultID = ?",
            [$_POST["petID"], $_POST["vetID"], $date, $_POST["diagnoses"], $_POST["prescription"], $_POST["editing"]]);
        $message = "Consultation updated.";
    }
}

// ---------- LOGICAL DELETE ----------
if (isset($_POST["delete"])) {
    run("UPDATE Consultation SET isDeleted = 1 WHERE consultID = ?", [$_POST["delete"]]);
    $message = "Consultation deleted.";
}

// ---------- Load a record into the form when Edit is clicked ----------
$row = ["consultID" => "", "petID" => "", "vetID" => "", "consultDate" => "", "diagnoses" => "", "prescription" => ""];
$editing = "";
if (isset($_GET["edit"])) {
    $row = run("SELECT * FROM Consultation WHERE consultID = ?", [$_GET["edit"]])->fetch_assoc();
    $editing = $row["consultID"];
}

// Pets and vets for the dropdown lists
$pets = run("SELECT petID, petName FROM Pet WHERE isDeleted = 0");
$vets = run("SELECT vetID, vetFName, vetLName FROM Veterinarian WHERE isDeleted = 0");

// ---------- READ and SEARCH ----------
$search = $_GET["search"] ?? "";
$list = run("SELECT * FROM Consultation
             WHERE isDeleted = 0
             AND (consultID LIKE ? OR petID LIKE ? OR vetID LIKE ? OR diagnoses LIKE ? OR prescription LIKE ?)
             ORDER BY consultDate DESC",
            ["%$search%", "%$search%", "%$search%", "%$search%", "%$search%"]);
?>

<h2>Consultations Transaction Management</h2>
<p class="msg"><?= $message ?></p>

<form method="post" class="box">
    <input type="hidden" name="editing" value="<?= h($editing) ?>">
    Consultation No.:<br>
    <input name="consultID" value="<?= h($row["consultID"]) ?>" <?= $editing ? "readonly" : "required" ?>><br>
    Pet:<br>
    <select name="petID" required>
        <option value="">-- choose pet --</option>
        <?php while ($p = $pets->fetch_assoc()) { ?>
            <option value="<?= h($p["petID"]) ?>" <?= $p["petID"] == $row["petID"] ? "selected" : "" ?>>
                <?= h($p["petID"] . " - " . $p["petName"]) ?>
            </option>
        <?php } ?>
    </select><br>
    Veterinarian:<br>
    <select name="vetID" required>
        <option value="">-- choose vet --</option>
        <?php while ($v = $vets->fetch_assoc()) { ?>
            <option value="<?= h($v["vetID"]) ?>" <?= $v["vetID"] == $row["vetID"] ? "selected" : "" ?>>
                <?= h($v["vetID"] . " - " . $v["vetFName"] . " " . $v["vetLName"]) ?>
            </option>
        <?php } ?>
    </select><br>
    Date/Time:<br>
    <input type="datetime-local" name="consultDate" value="<?= h(str_replace(" ", "T", $row["consultDate"])) ?>" required><br>
    Diagnoses:<br>
    <textarea name="diagnoses" rows="2" cols="40" required><?= h($row["diagnoses"]) ?></textarea><br>
    Prescription:<br>
    <textarea name="prescription" rows="2" cols="40" required><?= h($row["prescription"]) ?></textarea><br><br>
    <button name="save">Save</button>
    <a href="consultation.php">Clear</a>
</form>

<form method="get">
    <input name="search" placeholder="Search..." value="<?= h($search) ?>">
    <button>Search</button>
</form>

<table>
    <tr><th>No.</th><th>Pet ID</th><th>Vet ID</th><th>Date/Time</th><th>Diagnoses</th><th>Prescription</th><th>Action</th></tr>
    <?php while ($r = $list->fetch_assoc()) { ?>
    <tr>
        <td><?= h($r["consultID"]) ?></td>
        <td><?= h($r["petID"]) ?></td>
        <td><?= h($r["vetID"]) ?></td>
        <td><?= h($r["consultDate"]) ?></td>
        <td><?= h($r["diagnoses"]) ?></td>
        <td><?= h($r["prescription"]) ?></td>
        <td>
            <a href="consultation.php?edit=<?= urlencode($r["consultID"]) ?>">Edit</a>
            <form method="post" style="display:inline" onsubmit="return confirm('Delete this record?')">
                <button name="delete" value="<?= h($r["consultID"]) ?>">Delete</button>
            </form>
        </td>
    </tr>
    <?php } ?>
</table>

<?php include "footer.php"; ?>
