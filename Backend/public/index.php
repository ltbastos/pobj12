<?php

use App\Kernel;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/config/bootstrap.php';

$appEnv = isset($_SERVER['APP_ENV']) ? $_SERVER['APP_ENV'] : 'dev';
$appDebug = isset($_SERVER['APP_DEBUG']) ? (bool) $_SERVER['APP_DEBUG'] : ('prod' !== $appEnv);

if ($appDebug) {
    umask(0000);
    Debug::enable();
}

$trustedProxies = isset($_SERVER['TRUSTED_PROXIES']) ? $_SERVER['TRUSTED_PROXIES'] : false;
if ($trustedProxies) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_PROTO);
}

$trustedHosts = isset($_SERVER['TRUSTED_HOSTS']) ? $_SERVER['TRUSTED_HOSTS'] : false;
if ($trustedHosts) {
    Request::setTrustedHosts([$trustedHosts]);
}

$kernel = new Kernel($appEnv, $appDebug);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
