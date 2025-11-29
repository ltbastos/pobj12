<?php

namespace App\Infrastructure\Helpers;

class DateFormatter
{
    public static function toIsoDate($value)
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }
        
        if (is_string($value) && strlen($value) >= 10) {
            return substr($value, 0, 10);
        }
        
        return null;
    }
}

