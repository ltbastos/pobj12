<?php

namespace App\Infrastructure\Container;

interface ServiceProviderInterface
{
    public function register(\Pimple\Container $container);
}

