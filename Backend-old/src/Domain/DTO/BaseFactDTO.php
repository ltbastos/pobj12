<?php

namespace App\Domain\DTO;

abstract class BaseFactDTO
{
    public function toArray()
    {
        $result = [];
        foreach (get_object_vars($this) as $key => $value) {
            $result[$key] = $value;
        }
        return $result;
    }
}

