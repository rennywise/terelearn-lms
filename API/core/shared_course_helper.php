<?php
$path = dirname(__DIR__) . '/../includes/shared_course_helper.php';
if (is_file($path)) {
    require_once $path;
    return;
}
throw new RuntimeException('Unable to locate app/includes/shared_course_helper.php');
