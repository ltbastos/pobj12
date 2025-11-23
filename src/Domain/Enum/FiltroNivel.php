<?php

namespace App\Domain\Enum;

/**
 * Enum-like class for FiltroNivel (PHP 7.1 compatible)
 */
class FiltroNivel
{
    const SEGMENTOS = 'segmentos';
    const DIRETORIAS = 'diretorias';
    const REGIONAIS = 'regionais';
    const AGENCIAS = 'agencias';
    const GGESTOES = 'ggestoes';
    const GERENTES = 'gerentes';
    const STATUS_INDICADORES = 'status_indicadores';

    private static $validValues = [
        self::SEGMENTOS,
        self::DIRETORIAS,
        self::REGIONAIS,
        self::AGENCIAS,
        self::GGESTOES,
        self::GERENTES,
        self::STATUS_INDICADORES,
    ];

    private $value;

    private function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public static function tryFromString(string $value): ?self
    {
        if (in_array($value, self::$validValues, true)) {
            return new self($value);
        }
        return null;
    }

    public static function fromString(string $value): self
    {
        $result = self::tryFromString($value);
        if ($result === null) {
            throw new \InvalidArgumentException('Nível inválido: ' . $value);
        }
        return $result;
    }

    public static function SEGMENTOS(): self
    {
        return new self(self::SEGMENTOS);
    }

    public static function DIRETORIAS(): self
    {
        return new self(self::DIRETORIAS);
    }

    public static function REGIONAIS(): self
    {
        return new self(self::REGIONAIS);
    }

    public static function AGENCIAS(): self
    {
        return new self(self::AGENCIAS);
    }

    public static function GGESTOES(): self
    {
        return new self(self::GGESTOES);
    }

    public static function GERENTES(): self
    {
        return new self(self::GERENTES);
    }

    public static function STATUS_INDICADORES(): self
    {
        return new self(self::STATUS_INDICADORES);
    }
}


