<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

$settings = require __DIR__ . '/../config/settings.php';

echo "DB Host: " . ($settings['settings']['db']['host'] ?? 'não definido') . "\n";
echo "DB Name: " . ($settings['settings']['db']['database'] ?? 'não definido') . "\n";
echo "DB User: " . ($settings['settings']['db']['username'] ?? 'não definido') . "\n";

try {
    $capsule = new \Illuminate\Database\Capsule\Manager;
    $capsule->addConnection($settings['settings']['db']);
    $capsule->setAsGlobal();
    $capsule->bootEloquent();
    
    $result = $capsule->select('SELECT 1 as test');
    echo "Conexão OK! Resultado: " . print_r($result, true);
} catch (\Throwable $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . "\n";
    echo "Linha: " . $e->getLine() . "\n";
}
