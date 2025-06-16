<?php

$files = glob(__DIR__ . '/*.php');
foreach ($files as $file) {
    if (basename($file) !== 'index.php') {
        require_once $file;
    }
}