<?php

/**
 * Script de teste para validar a query de ranking executivo
 * 
 * Uso: php tests/TestExecRankingQuery.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Domain\DTO\FilterDTO;
use App\Repository\Pobj\ExecRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Dotenv\Dotenv;

// Carregar variáveis de ambiente
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/../.env');

// Obter EntityManager (ajustar conforme sua configuração)
// Este é um exemplo - você precisará ajustar conforme sua estrutura

echo "=== Teste de Query de Ranking Executivo ===\n\n";

// Criar filtros de exemplo (agência 1268)
$filters = new FilterDTO();
$filters->setSegmento('1');
$filters->setDiretoria('8608');
$filters->setRegional('8487');
$filters->setAgencia('1268');
$filters->setDataInicio('2025-01-01');
$filters->setDataFim('2025-12-07');

echo "Filtros aplicados:\n";
echo "- Segmento: " . $filters->getSegmento() . "\n";
echo "- Diretoria: " . $filters->getDiretoria() . "\n";
echo "- Regional: " . $filters->getRegional() . "\n";
echo "- Agência: " . $filters->getAgencia() . "\n";
echo "- Data Início: " . $filters->getDataInicio() . "\n";
echo "- Data Fim: " . $filters->getDataFim() . "\n\n";

echo "Esperado: A query deve agrupar por Gerente de Gestão (nível abaixo da agência)\n\n";

// Nota: Para executar este teste, você precisará ter acesso ao EntityManager
// Este é um exemplo de como a query seria testada

echo "Para testar completamente, execute a API:\n";
echo "GET http://localhost:8081/api/pobj/exec?segmento=1&diretoria=8608&regional=8487&agencia=1268&dataInicio=2025-01-01&dataFim=2025-12-07\n\n";

echo "Verificações:\n";
echo "1. O ranking deve retornar gerentes de gestão da agência 1268\n";
echo "2. Cada item deve ter: key, label, real_mens, meta_mens, p_mens\n";
echo "3. Os dados devem estar ordenados por p_mens (desempenho) DESC\n";
echo "4. Apenas gerentes de gestão com real_mens > 0 OU meta_mens > 0 devem aparecer\n";

