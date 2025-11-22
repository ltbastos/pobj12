<?php

namespace App\Domain\ValueObject;

class StatusIndicador
{
    const ATINGIDO = '01';
    const NAO_ATINGIDO = '02';
    const TODOS = '03';

    public static function getLabel($value)
    {
        switch ($value) {
            case self::ATINGIDO:
                return 'Atingido';
            case self::NAO_ATINGIDO:
                return 'Não Atingido';
            case self::TODOS:
                return 'Todos';
            default:
                return null;
        }
    }

    public static function getDefaults(): array
    {
        return [
            ['id' => self::ATINGIDO, 'label' => self::getLabel(self::ATINGIDO)],
            ['id' => self::NAO_ATINGIDO, 'label' => self::getLabel(self::NAO_ATINGIDO)],
            ['id' => self::TODOS, 'label' => self::getLabel(self::TODOS)],
        ];
    }

    public static function getDefaultsForFilter(): array
    {
        return [
            ['id' => self::ATINGIDO, 'label' => self::getLabel(self::ATINGIDO)],
            ['id' => self::NAO_ATINGIDO, 'label' => self::getLabel(self::NAO_ATINGIDO)],
            ['id' => self::TODOS, 'label' => self::getLabel(self::TODOS)],
        ];
    }
}

