<?php
require_once 'functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    go('index.php');
}
check_csrf();

$_SESSION = [];
session_destroy();
go('index.php');
