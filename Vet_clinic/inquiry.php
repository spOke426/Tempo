<?php
include "db.php";
include "header.php";

// This function is used by inquiries 2 to 5.
// It first uses COUNT to get the total, then shows the matching consultations.
function showConsultations($where, $values) {
    $from = "FROM Consultation c
             JOIN Pet p          ON c.petID = p.petID
             JOIN PetOwner o     ON p.petOwnerID = o.petOwnerID
             JOIN Veterinarian v ON c.vetID = v.vetID
             WHERE c.isDeleted = 0 AND $where";

    // COUNT query
    $total = run("SELECT COUNT(*) AS total $from", $values)->fetch_assoc()["total"];
    echo "<p><b>Total consultations found (COUNT): $total</b></p>";

    // Detail query
    $rows = run("SELECT c.consultID, c.consultDate, p.petID, p.petName, o.petOwnerID,
                        o.petOwnerFName, o.petOwnerLName, v.vetID, v.vetLName, c.diagnoses, c.prescription
                 $from ORDER BY c.consultDate", $values);

    echo "<table><tr><th>No.</th><th>Date</th><th>Pet</th><th>Owner</th><th>Vet</th><th>Diagnoses</th><th>Prescription</th></tr>";
    while ($r = $rows->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . h($r["consultID"]) . "</td>";
        echo "<td>" . h($r["consultDate"]) . "</td>";
        echo "<td>" . h($r["petID"] . " " . $r["petName"]) . "</td>";
        echo "<td>" . h($r["petOwnerID"] . " " . $r["petOwnerFName"] . " " . $r["petOwnerLName"]) . "</td>";
        echo "<td>" . h($r["vetID"] . " " . $r["vetLName"]) . "</td>";
        echo "<td>" . h($r["diagnoses"]) . "</td>";
        echo "<td>" . h($r["prescription"]) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

$type  = $_GET["type"] ?? "";    // which inquiry button was used (1 to 5)
$value = $_GET["value"] ?? "";   // the search key typed in
?>

<h2>Consultations Inquiry</h2>

<!-- Each inquiry is a small form. The hidden "type" tells us which one was used. -->
<form method="get" class="box">
    <input type="hidden" name="type" value="1">
    1. Vets with specialization:
    <input name="value" value="<?= $type == "1" ? h($value) : "" ?>" required>
    <button>Search</button>
</form>

<form method="get" class="box">
    <input type="hidden" name="type" value="2">
    2. Consultations of Pet ID:
    <input name="value" value="<?= $type == "2" ? h($value) : "" ?>" required>
    <button>Search</button>
</form>

<form method="get" class="box">
    <input type="hidden" name="type" value="3">
    3. Consultations of Pet Owner ID:
    <input name="value" value="<?= $type == "3" ? h($value) : "" ?>" required>
    <button>Search</button>
</form>

<form method="get" class="box">
    <input type="hidden" name="type" value="4">
    4. Consultations of Vet ID:
    <input name="value" value="<?= $type == "4" ? h($value) : "" ?>" required>
    <button>Search</button>
</form>

<form method="get" class="box">
    <input type="hidden" name="type" value="5">
    5. Consultations from
    <input type="date" name="from" value="<?= h($_GET["from"] ?? "") ?>" required>
    to
    <input type="date" name="to" value="<?= h($_GET["to"] ?? "") ?>" required>
    <button>Search</button>
</form>

<hr>
<h3>Result</h3>

<?php
if ($type == "1") {
    // Inquiry 1: vets with a specialization
    $from = "FROM Veterinarian WHERE isDeleted = 0 AND vetSpecial LIKE ?";
    $total = run("SELECT COUNT(*) AS total $from", ["%$value%"])->fetch_assoc()["total"];
    echo "<p><b>Total veterinarians found (COUNT): $total</b></p>";

    $rows = run("SELECT * $from", ["%$value%"]);
    echo "<table><tr><th>License No.</th><th>Name</th><th>Address</th><th>Specialization</th></tr>";
    while ($r = $rows->fetch_assoc()) {
        echo "<tr><td>" . h($r["vetID"]) . "</td><td>" . h($r["vetFName"] . " " . $r["vetLName"]) .
             "</td><td>" . h($r["vetAddress"]) . "</td><td>" . h($r["vetSpecial"]) . "</td></tr>";
    }
    echo "</table>";

} elseif ($type == "2") {
    showConsultations("c.petID = ?", [$value]);                   // Inquiry 2: by pet

} elseif ($type == "3") {
    showConsultations("p.petOwnerID = ?", [$value]);              // Inquiry 3: by owner

} elseif ($type == "4") {
    showConsultations("c.vetID = ?", [$value]);                   // Inquiry 4: by vet

} elseif ($type == "5") {
    // Inquiry 5: date range. DATE() removes the time so the "to" day is fully included.
    showConsultations("DATE(c.consultDate) BETWEEN ? AND ?", [$_GET["from"], $_GET["to"]]);

} else {
    echo "<p>Choose an inquiry above.</p>";
}

include "footer.php";
?>
