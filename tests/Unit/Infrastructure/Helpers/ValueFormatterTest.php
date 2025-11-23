<?php

namespace Tests\Unit\Infrastructure\Helpers;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Helpers\ValueFormatter;

class ValueFormatterTest extends TestCase
{
    /**
     * Teste para conversão de valores para float
     */
    public function testToFloatWithValidNumericString()
    {
        $this->assertEquals(10.5, ValueFormatter::toFloat('10.5'));
        $this->assertEquals(10.0, ValueFormatter::toFloat('10'));
        $this->assertEquals(0.0, ValueFormatter::toFloat('0'));
        $this->assertEquals(-5.5, ValueFormatter::toFloat('-5.5'));
    }

    public function testToFloatWithValidNumeric()
    {
        $this->assertEquals(10.5, ValueFormatter::toFloat(10.5));
        $this->assertEquals(10.0, ValueFormatter::toFloat(10));
        $this->assertEquals(0.0, ValueFormatter::toFloat(0));
    }

    public function testToFloatWithNull()
    {
        $this->assertNull(ValueFormatter::toFloat(null));
    }

    public function testToFloatWithEmptyString()
    {
        $this->assertNull(ValueFormatter::toFloat(''));
    }

    public function testToFloatWithInvalidString()
    {
        $this->assertNull(ValueFormatter::toFloat('abc'));
        $this->assertNull(ValueFormatter::toFloat('not a number'));
    }

    /**
     * Teste para conversão de valores para int
     */
    public function testToIntWithValidNumericString()
    {
        $this->assertEquals(10, ValueFormatter::toInt('10'));
        $this->assertEquals(0, ValueFormatter::toInt('0'));
        $this->assertEquals(-5, ValueFormatter::toInt('-5'));
        $this->assertEquals(10, ValueFormatter::toInt('10.7')); // Trunca para int
    }

    public function testToIntWithValidNumeric()
    {
        $this->assertEquals(10, ValueFormatter::toInt(10));
        $this->assertEquals(10, ValueFormatter::toInt(10.7));
        $this->assertEquals(0, ValueFormatter::toInt(0));
        $this->assertEquals(-5, ValueFormatter::toInt(-5));
    }

    public function testToIntWithNull()
    {
        $this->assertNull(ValueFormatter::toInt(null));
    }

    public function testToIntWithEmptyString()
    {
        $this->assertNull(ValueFormatter::toInt(''));
    }

    public function testToIntWithInvalidString()
    {
        $this->assertNull(ValueFormatter::toInt('abc'));
        $this->assertNull(ValueFormatter::toInt('not a number'));
    }

    /**
     * Teste para conversão de valores para string
     */
    public function testToStringWithString()
    {
        $this->assertEquals('test', ValueFormatter::toString('test'));
        $this->assertEquals('123', ValueFormatter::toString('123'));
        $this->assertEquals('', ValueFormatter::toString(''));
    }

    public function testToStringWithInteger()
    {
        $this->assertEquals('10', ValueFormatter::toString(10));
        $this->assertEquals('0', ValueFormatter::toString(0));
        $this->assertEquals('-5', ValueFormatter::toString(-5));
    }

    public function testToStringWithFloat()
    {
        $this->assertEquals('10.5', ValueFormatter::toString(10.5));
        $this->assertEquals('0.0', ValueFormatter::toString(0.0));
    }

    public function testToStringWithNull()
    {
        $this->assertNull(ValueFormatter::toString(null));
    }

    public function testToStringWithBoolean()
    {
        $this->assertEquals('1', ValueFormatter::toString(true));
        $this->assertEquals('', ValueFormatter::toString(false));
    }
}


