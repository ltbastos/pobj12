<?php

namespace Tests\Unit\Domain\Enum;

use PHPUnit\Framework\TestCase;
use App\Domain\Enum\FiltroNivel;

class FiltroNivelTest extends TestCase
{
    /**
     * Teste para criação de FiltroNivel usando métodos estáticos
     */
    public function testSegmentosStaticMethod()
    {
        $filtro = FiltroNivel::SEGMENTOS();
        $this->assertEquals(FiltroNivel::SEGMENTOS, $filtro->getValue());
    }

    public function testDiretoriasStaticMethod()
    {
        $filtro = FiltroNivel::DIRETORIAS();
        $this->assertEquals(FiltroNivel::DIRETORIAS, $filtro->getValue());
    }

    public function testRegionaisStaticMethod()
    {
        $filtro = FiltroNivel::REGIONAIS();
        $this->assertEquals(FiltroNivel::REGIONAIS, $filtro->getValue());
    }

    public function testAgenciasStaticMethod()
    {
        $filtro = FiltroNivel::AGENCIAS();
        $this->assertEquals(FiltroNivel::AGENCIAS, $filtro->getValue());
    }

    public function testGgestoesStaticMethod()
    {
        $filtro = FiltroNivel::GGESTOES();
        $this->assertEquals(FiltroNivel::GGESTOES, $filtro->getValue());
    }

    public function testGerentesStaticMethod()
    {
        $filtro = FiltroNivel::GERENTES();
        $this->assertEquals(FiltroNivel::GERENTES, $filtro->getValue());
    }

    public function testStatusIndicadoresStaticMethod()
    {
        $filtro = FiltroNivel::STATUS_INDICADORES();
        $this->assertEquals(FiltroNivel::STATUS_INDICADORES, $filtro->getValue());
    }

    /**
     * Teste para criação de FiltroNivel usando fromString
     */
    public function testFromStringWithValidValue()
    {
        $filtro = FiltroNivel::fromString(FiltroNivel::SEGMENTOS);
        $this->assertEquals(FiltroNivel::SEGMENTOS, $filtro->getValue());

        $filtro = FiltroNivel::fromString(FiltroNivel::DIRETORIAS);
        $this->assertEquals(FiltroNivel::DIRETORIAS, $filtro->getValue());
    }

    /**
     * Teste para fromString com valor inválido (deve lançar exceção)
     */
    public function testFromStringWithInvalidValue()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Nível inválido: invalid_level');
        FiltroNivel::fromString('invalid_level');
    }

    /**
     * Teste para tryFromString com valor válido
     */
    public function testTryFromStringWithValidValue()
    {
        $filtro = FiltroNivel::tryFromString(FiltroNivel::SEGMENTOS);
        $this->assertNotNull($filtro);
        $this->assertEquals(FiltroNivel::SEGMENTOS, $filtro->getValue());

        $filtro = FiltroNivel::tryFromString(FiltroNivel::REGIONAIS);
        $this->assertNotNull($filtro);
        $this->assertEquals(FiltroNivel::REGIONAIS, $filtro->getValue());
    }

    /**
     * Teste para tryFromString com valor inválido (deve retornar null)
     */
    public function testTryFromStringWithInvalidValue()
    {
        $filtro = FiltroNivel::tryFromString('invalid_level');
        $this->assertNull($filtro);

        $filtro = FiltroNivel::tryFromString('');
        $this->assertNull($filtro);

        $filtro = FiltroNivel::tryFromString('segmento'); // Singular, não plural
        $this->assertNull($filtro);
    }

    /**
     * Teste para getValue
     */
    public function testGetValue()
    {
        $filtro = FiltroNivel::SEGMENTOS();
        $this->assertIsString($filtro->getValue());
        $this->assertEquals(FiltroNivel::SEGMENTOS, $filtro->getValue());
    }

    /**
     * Teste para verificar todos os valores válidos
     */
    public function testAllValidValues()
    {
        $validValues = [
            FiltroNivel::SEGMENTOS,
            FiltroNivel::DIRETORIAS,
            FiltroNivel::REGIONAIS,
            FiltroNivel::AGENCIAS,
            FiltroNivel::GGESTOES,
            FiltroNivel::GERENTES,
            FiltroNivel::STATUS_INDICADORES,
        ];

        foreach ($validValues as $value) {
            $filtro = FiltroNivel::fromString($value);
            $this->assertEquals($value, $filtro->getValue());
        }
    }

    /**
     * Teste para verificar que instâncias diferentes com mesmo valor são diferentes objetos
     */
    public function testDifferentInstances()
    {
        $filtro1 = FiltroNivel::SEGMENTOS();
        $filtro2 = FiltroNivel::SEGMENTOS();
        
        $this->assertEquals($filtro1->getValue(), $filtro2->getValue());
        // São objetos diferentes, mas com mesmo valor
        $this->assertNotSame($filtro1, $filtro2);
    }
}


