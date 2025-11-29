<?php

namespace App\Infrastructure\Helpers;

class ValueFormatter
{
    /**
     * Converte valor para float ou retorna null
     * @param mixed $value
     * @return float|null
     */
    public static function toFloat($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (float)$value;
        }
        return null;
    }

    /**
     * Converte valor para int ou retorna null
     * @param mixed $value
     * @return int|null
     */
    public static function toInt($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (int)$value;
        }
        return null;
    }

    /**
     * Converte valor para string ou retorna null
     * @param mixed $value
     * @return string|null
     */
    public static function toString($value)
    {
        if ($value === null) {
            return null;
        }
        return (string)$value;
    }
}

