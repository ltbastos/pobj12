<?php

use App\Infrastructure\Container\Providers\DatabaseProvider;
use App\Infrastructure\Container\Providers\RepositoryProvider;
use App\Infrastructure\Container\Providers\UseCaseProvider;
use App\Infrastructure\Container\Providers\ControllerProvider;

return function ($container) {
    $providers = [
        new DatabaseProvider(),
        new RepositoryProvider(),
        new UseCaseProvider(),
        new ControllerProvider(),
    ];
    
    foreach ($providers as $provider) {
        $provider->register($container);
    }
};
