<?php
include "db.php";
include "header.php";
?>

<h2>Election Results</h2>

<?php
$positions = run("SELECT * FROM Positions ORDER BY posID");

while ($p = $positions->fetch_assoc()) {

    // COUNT the votes of every candidate in this position
    $cands = run("SELECT c.candFName, c.candMName, c.candLName, COUNT(v.voterID) AS totalVotes
                  FROM Candidates c
                  LEFT JOIN Votes v ON v.candID = c.candID
                  WHERE c.posID = ?
                  GROUP BY c.candID
                  ORDER BY totalVotes DESC", [$p["posID"]]);

    // Total votes of the whole position (used to compute the percentage)
    $positionTotal = run("SELECT COUNT(*) AS n FROM Votes WHERE posID = ?", [$p["posID"]])->fetch_assoc()["n"];
?>
    <table>
        <tr><th colspan="3"><?= h($p["posName"]) ?></th></tr>
        <tr><th>Candidate</th><th>Total Votes</th><th>Voting %</th></tr>
        <?php while ($c = $cands->fetch_assoc()) {
            // percentage = candidate votes / all votes in the position x 100
            $percent = $positionTotal > 0 ? $c["totalVotes"] / $positionTotal * 100 : 0;
        ?>
        <tr>
            <td><?= h($c["candFName"] . " " . $c["candMName"] . " " . $c["candLName"]) ?></td>
            <td><?= $c["totalVotes"] ?></td>
            <td><?= number_format($percent, 2) ?></td>
        </tr>
        <?php } ?>
    </table>
<?php } ?>

<?php include "footer.php"; ?>
