<?php

namespace App\Domain\Enum;

/**
 * Enum-like class for Cargo (PHP 7.1 compatible)
 */
class Cargo
{
    const GERENTE = 1;
    const GERENTE_GESTAO = 3;

    private static $validValues = [
        self::GERENTE,
        self::GERENTE_GESTAO,
    ];

    private $value;

    private function __construct(int $value)
    {
        $this->value = $value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getLabel(): string
    {
        switch ($this->value) {
            case self::GERENTE:
                return 'Gerente';
            case self::GERENTE_GESTAO:
                return 'Gerente de Gestão';
            default:
                return '';
        }
    }

    public static function tryFromId(int $id): ?self
    {
        if (in_array($id, self::$validValues, true)) {
            return new self($id);
        }
        return null;
    }

    public static function fromId(int $id): self
    {
        $result = self::tryFromId($id);
        if ($result === null) {
            throw new \InvalidArgumentException('Cargo inválido: ' . $id);
        }
        return $result;
    }

    public static function GERENTE(): self
    {
        return new self(self::GERENTE);
    }

    public static function GERENTE_GESTAO(): self
    {
        return new self(self::GERENTE_GESTAO);
    }
}


