<?php
/**
 * API/db_connect.php
 * Clean version — no output, no die(), no mysqli_report strict mode.
 * mysqli_report(STRICT) was causing PHP to print error text before JSON
 * which broke all fetch() calls in signin.php and other APIs.
 */

$host = "localhost";
$user = "root";
$pass = "";
$db   = "dbterelearn";

/* Silence mysqli errors — APIs handle connection failure themselves */
mysqli_report(MYSQLI_REPORT_OFF);

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    /* Do NOT die() or echo here — callers check $conn themselves */
    $conn = null;
}