<?php
$path = dirname(__DIR__) . '/../config/db_connect.php';
if (is_file($path)) {
    require_once $path;
    return;
}
throw new RuntimeException('Unable to locate app/config/db_connect.php');
