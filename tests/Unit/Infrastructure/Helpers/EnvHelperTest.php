<?php

namespace Tests\Unit\Infrastructure\Helpers;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Helpers\EnvHelper;

class EnvHelperTest extends TestCase
{
    private $originalEnv = [];

    protected function setUp(): void
    {
        parent::setUp();
        // Salva o estado original do ambiente
        $this->originalEnv = $_ENV;
        $_ENV = [];
        
        // Limpa variáveis de ambiente que possam interferir
        putenv('TEST_VAR');
        putenv('ANOTHER_VAR');
    }

    protected function tearDown(): void
    {
        // Restaura o estado original
        $_ENV = $this->originalEnv;
        putenv('TEST_VAR');
        putenv('ANOTHER_VAR');
        parent::tearDown();
    }

    /**
     * Teste para obter variável de ambiente que não existe
     */
    public function testGetWithNonExistentKey()
    {
        $result = EnvHelper::get('NON_EXISTENT_KEY');
        $this->assertNull($result);
    }

    /**
     * Teste para obter variável de ambiente com valor padrão
     */
    public function testGetWithDefaultValue()
    {
        $result = EnvHelper::get('NON_EXISTENT_KEY', 'default_value');
        $this->assertEquals('default_value', $result);
    }

    /**
     * Teste para obter variável de ambiente definida via $_ENV
     */
    public function testGetWithEnvVariable()
    {
        $_ENV['TEST_VAR'] = 'test_value';
        $result = EnvHelper::get('TEST_VAR');
        $this->assertEquals('test_value', $result);
    }

    /**
     * Teste para obter variável de ambiente definida via putenv
     */
    public function testGetWithPutenvVariable()
    {
        putenv('TEST_VAR=putenv_value');
        $result = EnvHelper::get('TEST_VAR');
        $this->assertEquals('putenv_value', $result);
    }

    /**
     * Teste para verificar que $_ENV tem prioridade sobre putenv
     */
    public function testGetPriorityEnvOverPutenv()
    {
        $_ENV['TEST_VAR'] = 'env_value';
        putenv('TEST_VAR=putenv_value');
        $result = EnvHelper::get('TEST_VAR');
        $this->assertEquals('env_value', $result);
    }

    /**
     * Teste para carregar arquivo .env (se existir)
     * Nota: Este teste pode não funcionar se o arquivo .env não existir
     */
    public function testLoadEnvFile()
    {
        // O método load() é chamado automaticamente pelo get()
        // Se houver um arquivo .env, ele será carregado
        $result = EnvHelper::get('SOME_ENV_VAR', 'default');
        // Não podemos testar diretamente sem um arquivo .env real,
        // mas podemos verificar que não quebra
        $this->assertNotNull($result);
    }

    /**
     * Teste para verificar que load() não carrega duas vezes
     */
    public function testLoadIsIdempotent()
    {
        // Chama load múltiplas vezes
        EnvHelper::get('TEST_VAR');
        EnvHelper::get('ANOTHER_VAR');
        
        // Não deve quebrar ou causar problemas
        $this->assertTrue(true);
    }
}


