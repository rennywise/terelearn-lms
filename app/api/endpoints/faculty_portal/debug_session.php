<?php
/**
 * API/facultyUI/debug_session.php
 * TEMPORARY — shows what's in the session so you can diagnose the loading issue.
 * DELETE THIS FILE once fixed.
 */
session_start();
header('Content-Type: application/json');

echo json_encode([
    'session'        => $_SESSION,
    'session_id'     => session_id(),
    'cookie_exists'  => isset($_COOKIE[session_name()]),
]);