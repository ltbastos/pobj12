<?php

namespace App\Domain\ValueObject;

class Cargo
{
    const GERENTE = 1;
    const GERENTE_GESTAO = 3;

    public static function getLabel($id)
    {
        switch ($id) {
            case self::GERENTE:
                return 'Gerente';
            case self::GERENTE_GESTAO:
                return 'Gerente de Gestão';
            default:
                return null;
        }
    }

    public static function fromId($id)
    {
        if ($id === self::GERENTE || $id === self::GERENTE_GESTAO) {
            return $id;
        }
        throw new \InvalidArgumentException('Cargo inválido: ' . $id);
    }

    public static function tryFromId($id)
    {
        if ($id === self::GERENTE || $id === self::GERENTE_GESTAO) {
            return $id;
        }
        return null;
    }
}

