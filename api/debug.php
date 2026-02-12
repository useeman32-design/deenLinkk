<?php
// Simple test to check if PHP is working
header('Content-Type: text/plain');
echo "PHP IS WORKING\n";
echo "Current directory: " . __DIR__ . "\n";
echo "Request method: " . $_SERVER['REQUEST_METHOD'] . "\n";
?>