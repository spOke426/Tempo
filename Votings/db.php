<?php
session_start();

// Connect to the database (XAMPP default: user "root", no password)
$conn = new mysqli("localhost", "root", "", "election");

// run() = run any SQL with ? placeholders. Placeholders keep the SQL safe from hacking.
function run($sql, $values = []) {
    global $conn;
    return $conn->execute_query($sql, $values);
}

// h() = show text safely on the page
function h($text) {
    return htmlspecialchars($text ?? "");
}
