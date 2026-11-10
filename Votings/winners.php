<?php
include "db.php";
include "header.php";
?>

<h2>Election Winners</h2>

<table>
    <tr><th>Elective Position</th><th>Winner</th><th>Total Votes</th></tr>

    <?php
    $positions = run("SELECT * FROM Positions ORDER BY posID");

    while ($p = $positions->fetch_assoc()) {
        $slots = (int) $p["numOfPositions"];   // how many winners this position has (Senator = 12)

        // Candidates with the most votes first. LIMIT keeps only as many as there are positions.
        $winners = run("SELECT c.candFName, c.candMName, c.candLName, COUNT(v.voterID) AS totalVotes
                        FROM Candidates c
                        JOIN Votes v ON v.candID = c.candID
                        WHERE c.posID = ?
                        GROUP BY c.candID
                        ORDER BY totalVotes DESC
                        LIMIT $slots", [$p["posID"]]);

        while ($w = $winners->fetch_assoc()) { ?>
            <tr>
                <td><?= h($p["posName"]) ?></td>
                <td><?= h($w["candFName"] . " " . $w["candMName"] . " " . $w["candLName"]) ?></td>
                <td><?= $w["totalVotes"] ?></td>
            </tr>
    <?php }
    } ?>
</table>

<?php include "footer.php"; ?>
