<?php

namespace App\Infrastructure\Helpers;

class EnvHelper
{
    private static $loaded = false;

    public static function load()
    {
        if (self::$loaded) {
            return;
        }
        self::$loaded = true;

        $envFile = __DIR__ . '/../../../.env';

        if (!is_file($envFile) || !is_readable($envFile)) {
            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0) {
                continue;
            }
            if (strpos($line, '=') !== false) {
                $parts = explode('=', $line, 2);
                $key = isset($parts[0]) ? trim($parts[0]) : '';
                $value = isset($parts[1]) ? trim($parts[1]) : '';
                $value = trim($value, '"\'');
                if ($key !== '' && !isset($_ENV[$key])) {
                    $_ENV[$key] = $value;
                    putenv("$key=$value");
                }
            }
        }
    }

    public static function get($key, $default = null)
    {
        self::load();

        if (isset($_ENV[$key])) {
            return $_ENV[$key];
        }

        $value = getenv($key);
        if ($value !== false) {
            return $value;
        }

        return $default;
    }
}

