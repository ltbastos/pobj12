<?php

namespace Tests\Unit\Infrastructure\Helpers;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Helpers\DateFormatter;
use DateTime;
use DateTimeImmutable;

class DateFormatterTest extends TestCase
{
    /**
     * Teste para conversão de DateTime para ISO date
     */
    public function testToIsoDateWithDateTime()
    {
        $date = new DateTime('2024-01-15 14:30:00');
        $result = DateFormatter::toIsoDate($date);
        $this->assertEquals('2024-01-15', $result);
    }

    public function testToIsoDateWithDateTimeImmutable()
    {
        $date = new DateTimeImmutable('2023-12-25 00:00:00');
        $result = DateFormatter::toIsoDate($date);
        $this->assertEquals('2023-12-25', $result);
    }

    /**
     * Teste para conversão de string para ISO date
     */
    public function testToIsoDateWithValidString()
    {
        $this->assertEquals('2024-01-15', DateFormatter::toIsoDate('2024-01-15'));
        $this->assertEquals('2024-01-15', DateFormatter::toIsoDate('2024-01-15 14:30:00'));
        $this->assertEquals('2024-01-15', DateFormatter::toIsoDate('2024-01-15T14:30:00Z'));
    }

    public function testToIsoDateWithShortString()
    {
        // String com menos de 10 caracteres deve retornar null
        $this->assertNull(DateFormatter::toIsoDate('2024-01'));
        $this->assertNull(DateFormatter::toIsoDate('2024'));
        $this->assertNull(DateFormatter::toIsoDate(''));
    }

    public function testToIsoDateWithNull()
    {
        $this->assertNull(DateFormatter::toIsoDate(null));
    }

    public function testToIsoDateWithInteger()
    {
        // Integer não é uma string válida nem DateTimeInterface
        $this->assertNull(DateFormatter::toIsoDate(1234567890));
    }

    public function testToIsoDateWithArray()
    {
        // Array não é uma string válida nem DateTimeInterface
        $this->assertNull(DateFormatter::toIsoDate([]));
    }
}


