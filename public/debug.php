<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP está funcionando!\n";
echo "REQUEST_URI: " . ($_SERVER['REQUEST_URI'] ?? 'não definido') . "\n";
echo "SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'não definido') . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? 'não definido') . "\n";
