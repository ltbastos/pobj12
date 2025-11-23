<?php

namespace App\Domain\Enum;

/**
 * Enum-like class for StatusIndicador (PHP 7.1 compatible)
 */
class StatusIndicador
{
    const ATINGIDO = '01';
    const NAO_ATINGIDO = '02';
    const TODOS = '03';

    private static $validValues = [
        self::ATINGIDO,
        self::NAO_ATINGIDO,
        self::TODOS,
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

    public function getLabel(): string
    {
        switch ($this->value) {
            case self::ATINGIDO:
                return 'Atingido';
            case self::NAO_ATINGIDO:
                return 'Não Atingido';
            case self::TODOS:
                return 'Todos';
            default:
                return '';
        }
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
            throw new \InvalidArgumentException('Status inválido: ' . $value);
        }
        return $result;
    }

    public static function getDefaults(): array
    {
        return [
            ['id' => self::ATINGIDO, 'label' => self::ATINGIDO()->getLabel()],
            ['id' => self::NAO_ATINGIDO, 'label' => self::NAO_ATINGIDO()->getLabel()],
            ['id' => self::TODOS, 'label' => self::TODOS()->getLabel()],
        ];
    }

    public static function getDefaultsForFilter(): array
    {
        return self::getDefaults();
    }

    public static function ATINGIDO(): self
    {
        return new self(self::ATINGIDO);
    }

    public static function NAO_ATINGIDO(): self
    {
        return new self(self::NAO_ATINGIDO);
    }

    public static function TODOS(): self
    {
        return new self(self::TODOS);
    }
}


