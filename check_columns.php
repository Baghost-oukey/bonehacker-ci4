<?php
require 'app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';
$db = \Config\Database::connect();
$fields = $db->getFieldNames('histories');
foreach ($fields as $field) {
    echo $field . PHP_EOL;
}
