<?php

require __DIR__ . '/../vendor/autoload.php';

$settings = require __DIR__ . '/../config/settings.php';
$app = new \Slim\App($settings);

require __DIR__ . '/../routes/web.php';
require __DIR__ . '/../routes/api.php';

$app->run();
