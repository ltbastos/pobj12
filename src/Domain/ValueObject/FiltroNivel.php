<?php

namespace App\Domain\ValueObject;

class FiltroNivel
{
    const SEGMENTOS = 'segmentos';
    const DIRETORIAS = 'diretorias';
    const REGIONAIS = 'regionais';
    const AGENCIAS = 'agencias';
    const GGESTOES = 'ggestoes';
    const GERENTES = 'gerentes';
    const STATUS_INDICADORES = 'status_indicadores';

    public static function tryFromString(string $value)
    {
        $validValues = [
            self::SEGMENTOS,
            self::DIRETORIAS,
            self::REGIONAIS,
            self::AGENCIAS,
            self::GGESTOES,
            self::GERENTES,
            self::STATUS_INDICADORES,
        ];

        if (in_array($value, $validValues, true)) {
            return $value;
        }

        return null;
    }

    public static function fromString(string $value): string
    {
        $result = self::tryFromString($value);
        if ($result === null) {
            throw new \InvalidArgumentException('Nível inválido: ' . $value);
        }
        return $result;
    }
}

