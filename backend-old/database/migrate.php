<?php

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Migrations\DatabaseMigrationRepository;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Events\Dispatcher;

// CARREGAR VARIÁVEIS DE AMBIENTE
$dotenv = new Dotenv\Dotenv(__DIR__ . '/../');
$dotenv->load();

// CONFIGURAR ELOQUENT
$capsule = new Capsule;

$connectionConfig = [
    'driver'    => 'mysql',
    'host'      => getenv('DB_HOST') ?: 'localhost',
    'database'  => getenv('DB_NAME') ?: '',
    'username'  => getenv('DB_USER') ?: 'root',
    'password'  => getenv('DB_PASS') ?: '',
    'charset'   => getenv('DB_CHARSET') ?: 'utf8',
    'collation' => getenv('DB_COLLATION') ?: 'utf8_unicode_ci',
    'prefix'    => '',
];

if (getenv('DB_PORT')) {
    $connectionConfig['port'] = getenv('DB_PORT');
}

$capsule->addConnection($connectionConfig);

$capsule->setAsGlobal();
$capsule->bootEloquent();

// REPOSITÓRIO DE MIGRATIONS
$repository = new DatabaseMigrationRepository($capsule->getDatabaseManager(), 'migrations');

if (!$repository->repositoryExists()) {
    $repository->createRepository();
    echo "Tabela migrations criada.\n";
}

// MIGRATOR
$migrator = new Migrator(
    $repository,
    $capsule->getDatabaseManager(),
    new Filesystem(),
    new Dispatcher()
);

$migrationsPath = __DIR__.'/migrations';

$migrator->run($migrationsPath);

echo "Migrations executadas.\n";
