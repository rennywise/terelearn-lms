<?php
$path = dirname(__DIR__) . '/../config/config_ai.php';
if (is_file($path)) {
    require_once $path;
    return;
}
throw new RuntimeException('Unable to locate app/config/config_ai.php');
