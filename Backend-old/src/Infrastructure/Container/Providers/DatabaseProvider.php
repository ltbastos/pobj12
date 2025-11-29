<?php

namespace App\Infrastructure\Container\Providers;

use App\Infrastructure\Container\ServiceProviderInterface;
use Illuminate\Database\Capsule\Manager as Capsule;

class DatabaseProvider implements ServiceProviderInterface
{
    public function register(\Pimple\Container $container)
    {
        $container['db'] = function ($c) {
            $settings = $c->get('settings')['db'];
            
            $capsule = new Capsule;
            
            $host = ($settings['host'] === 'localhost') ? '127.0.0.1' : $settings['host'];
            
            $connectionConfig = [
                'driver'    => $settings['driver'],
                'host'      => $host,
                'database'  => $settings['database'],
                'username'  => $settings['username'],
                'password'  => $settings['password'],
                'charset'   => $settings['charset'],
                'collation' => $settings['collation'] ?? 'utf8_unicode_ci',
                'prefix'    => '',
            ];
            
            if (!empty($settings['port'])) {
                $connectionConfig['port'] = $settings['port'];
            }
            
            $capsule->addConnection($connectionConfig);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();
            
            return $capsule;
        };
    }
}

