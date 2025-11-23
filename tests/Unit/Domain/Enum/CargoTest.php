<?php

namespace Tests\Unit\Domain\Enum;

use PHPUnit\Framework\TestCase;
use App\Domain\Enum\Cargo;

class CargoTest extends TestCase
{
    /**
     * Teste para criação de Cargo usando métodos estáticos
     */
    public function testGerenteStaticMethod()
    {
        $cargo = Cargo::GERENTE();
        $this->assertEquals(Cargo::GERENTE, $cargo->getValue());
        $this->assertEquals('Gerente', $cargo->getLabel());
    }

    public function testGerenteGestaoStaticMethod()
    {
        $cargo = Cargo::GERENTE_GESTAO();
        $this->assertEquals(Cargo::GERENTE_GESTAO, $cargo->getValue());
        $this->assertEquals('Gerente de Gestão', $cargo->getLabel());
    }

    /**
     * Teste para criação de Cargo usando fromId
     */
    public function testFromIdWithValidId()
    {
        $cargo = Cargo::fromId(Cargo::GERENTE);
        $this->assertEquals(Cargo::GERENTE, $cargo->getValue());
        $this->assertEquals('Gerente', $cargo->getLabel());

        $cargo = Cargo::fromId(Cargo::GERENTE_GESTAO);
        $this->assertEquals(Cargo::GERENTE_GESTAO, $cargo->getValue());
        $this->assertEquals('Gerente de Gestão', $cargo->getLabel());
    }

    /**
     * Teste para fromId com ID inválido (deve lançar exceção)
     */
    public function testFromIdWithInvalidId()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cargo inválido: 999');
        Cargo::fromId(999);
    }

    /**
     * Teste para tryFromId com ID válido
     */
    public function testTryFromIdWithValidId()
    {
        $cargo = Cargo::tryFromId(Cargo::GERENTE);
        $this->assertNotNull($cargo);
        $this->assertEquals(Cargo::GERENTE, $cargo->getValue());

        $cargo = Cargo::tryFromId(Cargo::GERENTE_GESTAO);
        $this->assertNotNull($cargo);
        $this->assertEquals(Cargo::GERENTE_GESTAO, $cargo->getValue());
    }

    /**
     * Teste para tryFromId com ID inválido (deve retornar null)
     */
    public function testTryFromIdWithInvalidId()
    {
        $cargo = Cargo::tryFromId(999);
        $this->assertNull($cargo);

        $cargo = Cargo::tryFromId(0);
        $this->assertNull($cargo);

        $cargo = Cargo::tryFromId(-1);
        $this->assertNull($cargo);
    }

    /**
     * Teste para getValue
     */
    public function testGetValue()
    {
        $cargo = Cargo::GERENTE();
        $this->assertIsInt($cargo->getValue());
        $this->assertEquals(Cargo::GERENTE, $cargo->getValue());
    }

    /**
     * Teste para getLabel
     */
    public function testGetLabel()
    {
        $cargo = Cargo::GERENTE();
        $this->assertIsString($cargo->getLabel());
        $this->assertEquals('Gerente', $cargo->getLabel());

        $cargo = Cargo::GERENTE_GESTAO();
        $this->assertEquals('Gerente de Gestão', $cargo->getLabel());
    }

    /**
     * Teste para verificar que instâncias diferentes com mesmo valor são diferentes objetos
     */
    public function testDifferentInstances()
    {
        $cargo1 = Cargo::GERENTE();
        $cargo2 = Cargo::GERENTE();
        
        $this->assertEquals($cargo1->getValue(), $cargo2->getValue());
        // São objetos diferentes, mas com mesmo valor
        $this->assertNotSame($cargo1, $cargo2);
    }
}

