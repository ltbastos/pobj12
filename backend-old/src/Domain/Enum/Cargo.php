<?php

namespace App\Domain\Enum;

/**
 * Enum-like class for Cargo (PHP 7.1 compatible)
 */
class Cargo
{
    const DIRETOR = 1;
    const REGIONAL = 2;
    const GERENTE_GESTAO = 3;
    const GERENTE = 4;

    private static $validValues = [
        self::GERENTE,
        self::GERENTE_GESTAO,
        self::DIRETOR,
        self::REGIONAL,
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
            case self::DIRETOR:
                return 'Diretor';
            case self::REGIONAL:
                return 'Regional';
            default:
                return '';
        }
    }

    /**
     * Tenta criar uma instância de Cargo a partir do ID
     * 
     * @param int $id
     * @return self|null
     */
    public static function tryFromId(int $id)
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

    public static function DIRETOR(): self
    {
        return new self(self::DIRETOR);
    }

    public static function REGIONAL(): self
    {
        return new self(self::REGIONAL);
    }
}


