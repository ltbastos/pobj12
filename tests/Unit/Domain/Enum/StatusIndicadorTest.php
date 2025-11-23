<?php

namespace Tests\Unit\Domain\Enum;

use PHPUnit\Framework\TestCase;
use App\Domain\Enum\StatusIndicador;

class StatusIndicadorTest extends TestCase
{
    /**
     * Teste para criação de StatusIndicador usando métodos estáticos
     */
    public function testAtingidoStaticMethod()
    {
        $status = StatusIndicador::ATINGIDO();
        $this->assertEquals(StatusIndicador::ATINGIDO, $status->getValue());
        $this->assertEquals('Atingido', $status->getLabel());
    }

    public function testNaoAtingidoStaticMethod()
    {
        $status = StatusIndicador::NAO_ATINGIDO();
        $this->assertEquals(StatusIndicador::NAO_ATINGIDO, $status->getValue());
        $this->assertEquals('Não Atingido', $status->getLabel());
    }

    public function testTodosStaticMethod()
    {
        $status = StatusIndicador::TODOS();
        $this->assertEquals(StatusIndicador::TODOS, $status->getValue());
        $this->assertEquals('Todos', $status->getLabel());
    }

    /**
     * Teste para criação de StatusIndicador usando fromString
     */
    public function testFromStringWithValidValue()
    {
        $status = StatusIndicador::fromString(StatusIndicador::ATINGIDO);
        $this->assertEquals(StatusIndicador::ATINGIDO, $status->getValue());
        $this->assertEquals('Atingido', $status->getLabel());

        $status = StatusIndicador::fromString(StatusIndicador::NAO_ATINGIDO);
        $this->assertEquals(StatusIndicador::NAO_ATINGIDO, $status->getValue());
        $this->assertEquals('Não Atingido', $status->getLabel());
    }

    /**
     * Teste para fromString com valor inválido (deve lançar exceção)
     */
    public function testFromStringWithInvalidValue()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Status inválido: 99');
        StatusIndicador::fromString('99');
    }

    /**
     * Teste para tryFromString com valor válido
     */
    public function testTryFromStringWithValidValue()
    {
        $status = StatusIndicador::tryFromString(StatusIndicador::ATINGIDO);
        $this->assertNotNull($status);
        $this->assertEquals(StatusIndicador::ATINGIDO, $status->getValue());

        $status = StatusIndicador::tryFromString(StatusIndicador::TODOS);
        $this->assertNotNull($status);
        $this->assertEquals(StatusIndicador::TODOS, $status->getValue());
    }

    /**
     * Teste para tryFromString com valor inválido (deve retornar null)
     */
    public function testTryFromStringWithInvalidValue()
    {
        $status = StatusIndicador::tryFromString('99');
        $this->assertNull($status);

        $status = StatusIndicador::tryFromString('');
        $this->assertNull($status);

        $status = StatusIndicador::tryFromString('invalid');
        $this->assertNull($status);
    }

    /**
     * Teste para getValue
     */
    public function testGetValue()
    {
        $status = StatusIndicador::ATINGIDO();
        $this->assertIsString($status->getValue());
        $this->assertEquals(StatusIndicador::ATINGIDO, $status->getValue());
    }

    /**
     * Teste para getLabel
     */
    public function testGetLabel()
    {
        $status = StatusIndicador::ATINGIDO();
        $this->assertEquals('Atingido', $status->getLabel());

        $status = StatusIndicador::NAO_ATINGIDO();
        $this->assertEquals('Não Atingido', $status->getLabel());

        $status = StatusIndicador::TODOS();
        $this->assertEquals('Todos', $status->getLabel());
    }

    /**
     * Teste para getDefaults
     */
    public function testGetDefaults()
    {
        $defaults = StatusIndicador::getDefaults();
        
        $this->assertIsArray($defaults);
        $this->assertCount(3, $defaults);
        
        // Verifica estrutura
        foreach ($defaults as $default) {
            $this->assertArrayHasKey('id', $default);
            $this->assertArrayHasKey('label', $default);
            $this->assertIsString($default['id']);
            $this->assertIsString($default['label']);
        }
        
        // Verifica valores
        $ids = array_column($defaults, 'id');
        $this->assertContains(StatusIndicador::ATINGIDO, $ids);
        $this->assertContains(StatusIndicador::NAO_ATINGIDO, $ids);
        $this->assertContains(StatusIndicador::TODOS, $ids);
    }

    /**
     * Teste para getDefaultsForFilter
     */
    public function testGetDefaultsForFilter()
    {
        $defaults = StatusIndicador::getDefaultsForFilter();
        
        $this->assertIsArray($defaults);
        $this->assertCount(3, $defaults);
        
        // Deve ser igual a getDefaults
        $this->assertEquals(StatusIndicador::getDefaults(), $defaults);
    }

    /**
     * Teste para verificar todos os valores válidos
     */
    public function testAllValidValues()
    {
        $validValues = [
            StatusIndicador::ATINGIDO,
            StatusIndicador::NAO_ATINGIDO,
            StatusIndicador::TODOS,
        ];

        foreach ($validValues as $value) {
            $status = StatusIndicador::fromString($value);
            $this->assertEquals($value, $status->getValue());
        }
    }

    /**
     * Teste para verificar que instâncias diferentes com mesmo valor são diferentes objetos
     */
    public function testDifferentInstances()
    {
        $status1 = StatusIndicador::ATINGIDO();
        $status2 = StatusIndicador::ATINGIDO();
        
        $this->assertEquals($status1->getValue(), $status2->getValue());
        // São objetos diferentes, mas com mesmo valor
        $this->assertNotSame($status1, $status2);
    }
}


