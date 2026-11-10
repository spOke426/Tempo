<?php
include "db.php";
include "header.php";
$error = "";
$done  = false;

// ---------- LOG OUT ----------
if (isset($_POST["logout"])) {
    unset($_SESSION["voterID"]);
}

// ---------- LOG IN (voterID = username, voterPass = password) ----------
if (isset($_POST["login"])) {
    $voter = run("SELECT * FROM Voters WHERE voterID = ? AND voterPass = ?",
                 [$_POST["voterID"], $_POST["voterPass"]])->fetch_assoc();

    if (!$voter) {
        $error = "Wrong voter ID or password.";
    } elseif ($voter["voterStat"] != "active") {       // Rule 1: voter must be active
        $error = "Your voter record is not active.";
    } elseif ($voter["voted"] == "Y") {                // Rule 4: cannot vote again
        $error = "You have already voted.";
    } else {
        $_SESSION["voterID"] = $voter["voterID"];      // remember who is logged in
    }
}

// ---------- SUBMIT THE BALLOT ----------
if (isset($_POST["submitVote"]) && isset($_SESSION["voterID"])) {
    $voterID = $_SESSION["voterID"];

    // Check the voter again (in case something changed)
    $voter = run("SELECT * FROM Voters WHERE voterID = ?", [$voterID])->fetch_assoc();
    if ($voter["voterStat"] != "active" || $voter["voted"] == "Y") {
        $error = "You are not allowed to vote.";
    } else {
        $chosen = $_POST["pos"] ?? [];   // $chosen[posID] = list of candID
        $total  = 0;

        // Rule 3: votes in a position must be <= number of positions
        $positions = run("SELECT * FROM Positions WHERE posStat = 'open'");
        while ($p = $positions->fetch_assoc()) {
            $count = count($chosen[$p["posID"]] ?? []);
            $total += $count;
            if ($count > $p["numOfPositions"]) {
                $error = "You can only vote up to " . $p["numOfPositions"] . " for " . $p["posName"] . ".";
            }
        }
        if ($total == 0 && $error == "") {
            $error = "Please choose at least one candidate.";
        }

        // Everything is OK -> save the votes and mark the voter as voted
        if ($error == "") {
            foreach ($chosen as $posID => $candidates) {
                foreach ($candidates as $candID) {
                    // Only save if the candidate really belongs to that open position and is active
                    $ok = run("SELECT COUNT(*) AS n FROM Candidates c JOIN Positions p ON c.posID = p.posID
                               WHERE c.candID = ? AND c.posID = ? AND c.candStat = 'active' AND p.posStat = 'open'",
                              [$candID, $posID])->fetch_assoc()["n"];
                    if ($ok == 1) {
                        run("INSERT INTO Votes (posID, voterID, candID) VALUES (?, ?, ?)", [$posID, $voterID, $candID]);
                    }
                }
            }
            run("UPDATE Voters SET voted = 'Y' WHERE voterID = ?", [$voterID]);
            unset($_SESSION["voterID"]);
            $done = true;
        }
    }
}
?>

<h2>Voting</h2>

<?php if ($error != "") { ?><p class="err"><?= h($error) ?></p><?php } ?>

<?php if ($done) { ?>

    <p class="msg">Thank you! Your vote has been recorded.</p>

<?php } elseif (!isset($_SESSION["voterID"])) { ?>

    <!-- LOGIN FORM -->
    <form method="post" class="box">
        Voter ID:<br>
        <input name="voterID" required><br>
        Password:<br>
        <input type="password" name="voterPass" required><br><br>
        <button name="login">Login</button>
    </form>

<?php } else { ?>

    <!-- BALLOT -->
    <p>Logged in as <b><?= h($_SESSION["voterID"]) ?></b></p>
    <form method="post">
        <?php
        $positions = run("SELECT * FROM Positions WHERE posStat = 'open'");
        while ($p = $positions->fetch_assoc()) {
            $cands = run("SELECT * FROM Candidates WHERE posID = ? AND candStat = 'active'", [$p["posID"]]);
        ?>
            <div class="box" data-max="<?= $p["numOfPositions"] ?>">
                <b><?= h($p["posName"]) ?></b> (choose up to <?= $p["numOfPositions"] ?>)<br>
                <?php while ($c = $cands->fetch_assoc()) { ?>
                    <label>
                        <input type="checkbox" name="pos[<?= $p["posID"] ?>][]" value="<?= $c["candID"] ?>">
                        <?= h($c["candFName"] . " " . $c["candMName"] . " " . $c["candLName"]) ?>
                    </label><br>
                <?php } ?>
            </div>
        <?php } ?>
        <button name="submitVote" onclick="return confirm('Submit your vote? You cannot vote again.')">Submit Vote</button>
        <button name="logout">Cancel / Logout</button>
    </form>

    <script>
    // Stop the voter from ticking more boxes than the number of positions
    document.querySelectorAll(".box[data-max]").forEach(function (box) {
        box.addEventListener("change", function (e) {
            var max = box.dataset.max;
            if (box.querySelectorAll("input:checked").length > max) {
                e.target.checked = false;
                alert("You can only choose up to " + max + " here.");
            }
        });
    });
    </script>

<?php } ?>

<?php include "footer.php"; ?>
