<?php

namespace App\Repository\Pobj;

use App\Domain\DTO\FilterDTO;
use App\Domain\Enum\Cargo;
use App\Entity\Pobj\FHistoricoRankingPobj;
use App\Entity\Pobj\DEstrutura;
use App\Entity\Pobj\DCalendario;
use App\Entity\Pobj\Segmento;
use App\Entity\Pobj\Diretoria;
use App\Entity\Pobj\Regional;
use App\Entity\Pobj\Agencia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FHistoricoRankingPobjRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FHistoricoRankingPobj::class);
    }

    /**
     * Retorna o nome da tabela de uma entidade
     */
    private function getTableName(string $entityClass): string
    {
        return $this->getEntityManager()
            ->getClassMetadata($entityClass)
            ->getTableName();
    }

    /**
     * Lista todo o histórico ordenado por ranking
     */
    public function findAllOrderedByRanking(): array
    {
        return $this->createQueryBuilder('h')
                    ->orderBy('h.ranking', 'ASC')
                    ->addOrderBy('h.realizado', 'DESC')
                    ->getQuery()
                    ->getResult();
    }

    /**
     * Busca dados de ranking com filtros e informações de estrutura
     */
    public function findRankingWithFilters(?FilterDTO $filters = null): array
    {
        // Primeiro, verifica se há dados na tabela
        $rankingTable = $this->getTableName(FHistoricoRankingPobj::class);
        $conn = $this->getEntityManager()->getConnection();
        $countResult = $conn->executeQuery("SELECT COUNT(*) as total FROM {$rankingTable}");
        $count = $countResult->fetchOne();
        
        // Se não houver dados, retorna vazio
        if ($count == 0) {
            return [];
        }
        
        $rankingTable = $this->getTableName(FHistoricoRankingPobj::class);
        $estruturaTable = $this->getTableName(DEstrutura::class);
        $calendarioTable = $this->getTableName(DCalendario::class);
        $segmentoTable = $this->getTableName(Segmento::class);
        $diretoriaTable = $this->getTableName(Diretoria::class);
        $regionalTable = $this->getTableName(Regional::class);
        $agenciaTable = $this->getTableName(Agencia::class);

        // Adiciona parâmetros de cargo primeiro
        $params = [
            'cargoGerente' => Cargo::GERENTE,
            'cargoGerenteGestao' => Cargo::GERENTE_GESTAO
        ];
        $whereClause = '';

        if ($filters) {
            // Filtros de estrutura (aplica apenas o mais específico da hierarquia)
            $gerente = $filters->getGerente();
            $gerenteGestao = $filters->getGerenteGestao();
            $agencia = $filters->getAgencia();
            $regional = $filters->getRegional();
            $diretoria = $filters->getDiretoria();
            $segmento = $filters->getSegmento();

            // Se tiver gerente, filtra apenas por funcional
            if ($gerente !== null && $gerente !== '') {
                $whereClause .= " AND est.funcional = :gerenteFuncional";
                $params['gerenteFuncional'] = $gerente;
            } elseif ($gerenteGestao !== null && $gerenteGestao !== '') {
                // Se tiver gerente gestão, filtra por todos os gerentes da mesma estrutura
                $whereClause .= " AND EXISTS (
                    SELECT 1 FROM {$estruturaTable} AS ggestao 
                    WHERE ggestao.funcional = :gerenteGestaoFuncional
                    AND ggestao.cargo_id = :cargoGerenteGestao
                    AND ggestao.segmento_id = est.segmento_id
                    AND ggestao.diretoria_id = est.diretoria_id
                    AND ggestao.regional_id = est.regional_id
                    AND ggestao.agencia_id = est.agencia_id
                )";
                $params['gerenteGestaoFuncional'] = $gerenteGestao;
                $params['cargoGerenteGestao'] = Cargo::GERENTE_GESTAO;
            } else {
                // Aplica apenas o filtro mais específico da hierarquia de estrutura
                if ($agencia !== null && $agencia !== '') {
                    $whereClause .= " AND est.agencia_id = :agencia";
                    $params['agencia'] = $agencia;
                } elseif ($regional !== null && $regional !== '') {
                    $whereClause .= " AND est.regional_id = :regional";
                    $params['regional'] = $regional;
                } elseif ($diretoria !== null && $diretoria !== '') {
                    $whereClause .= " AND est.diretoria_id = :diretoria";
                    $params['diretoria'] = $diretoria;
                } elseif ($segmento !== null && $segmento !== '') {
                    $whereClause .= " AND est.segmento_id = :segmento";
                    $params['segmento'] = $segmento;
                }
            }

            // Filtros de data
            $dataInicio = $filters->getDataInicio();
            $dataFim = $filters->getDataFim();

            if ($dataInicio !== null && $dataInicio !== '') {
                $whereClause .= " AND h.data >= :dataInicio";
                $params['dataInicio'] = $dataInicio;
            }

            if ($dataFim !== null && $dataFim !== '') {
                $whereClause .= " AND h.data <= :dataFim";
                $params['dataFim'] = $dataFim;
            }
        }

        $sql = "SELECT
                    h.data AS data,
                    DATE_FORMAT(h.data, '%Y-%m') AS competencia,
                    CAST(seg.id AS CHAR) AS segmento_id,
                    seg.nome AS segmento,
                    CAST(dir.id AS CHAR) AS diretoria_id,
                    dir.nome AS diretoria_nome,
                    CAST(reg.id AS CHAR) AS gerencia_id,
                    reg.nome AS gerencia_nome,
                    CAST(ag.id AS CHAR) AS agencia_id,
                    ag.nome AS agencia_nome,
                    CASE 
                        WHEN est.cargo_id = :cargoGerente THEN est.funcional
                        WHEN est.cargo_id = :cargoGerenteGestao THEN NULL
                        ELSE NULL
                    END AS gerente_id,
                    CASE 
                        WHEN est.cargo_id = :cargoGerente THEN est.nome
                        WHEN est.cargo_id = :cargoGerenteGestao THEN NULL
                        ELSE NULL
                    END AS gerente_nome,
                    CASE 
                        WHEN est.cargo_id = :cargoGerente THEN ggestao.funcional
                        WHEN est.cargo_id = :cargoGerenteGestao THEN est.funcional
                        ELSE NULL
                    END AS gerente_gestao_id,
                    CASE 
                        WHEN est.cargo_id = :cargoGerente THEN ggestao.nome
                        WHEN est.cargo_id = :cargoGerenteGestao THEN est.nome
                        ELSE NULL
                    END AS gerente_gestao_nome,
                    h.ranking AS rank,
                    h.realizado AS realizado_mensal,
                    NULL AS meta_mensal,
                    NULL AS pontos
                FROM {$rankingTable} AS h
                INNER JOIN {$estruturaTable} AS est
                    ON est.funcional = h.funcional
                LEFT JOIN {$segmentoTable} AS seg
                    ON seg.id = est.segmento_id
                LEFT JOIN {$diretoriaTable} AS dir
                    ON dir.id = est.diretoria_id
                LEFT JOIN {$regionalTable} AS reg
                    ON reg.id = est.regional_id
                LEFT JOIN {$agenciaTable} AS ag
                    ON ag.id = est.agencia_id
                LEFT JOIN (
                    SELECT 
                        g1.agencia_id,
                        g1.funcional,
                        g1.nome
                    FROM {$estruturaTable} AS g1
                    INNER JOIN (
                        SELECT agencia_id, MIN(id) AS min_id
                        FROM {$estruturaTable}
                        WHERE cargo_id = :cargoGerenteGestao
                        AND agencia_id IS NOT NULL
                        GROUP BY agencia_id
                    ) AS g2 ON g1.id = g2.min_id AND g1.agencia_id = g2.agencia_id
                    WHERE g1.cargo_id = :cargoGerenteGestao
                ) AS ggestao
                    ON ggestao.agencia_id = est.agencia_id
                WHERE 1=1 {$whereClause}
                ORDER BY h.ranking ASC, h.realizado DESC, h.data DESC";

        $conn = $this->getEntityManager()->getConnection();
        $result = $conn->executeQuery($sql, $params);
        $rows = $result->fetchAllAssociative();

        // Formata os dados para o formato esperado pelo frontend
        $formatted = [];
        foreach ($rows as $row) {
            $formatted[] = [
                'data' => $row['data'] ? (new \DateTime($row['data']))->format('Y-m-d') : null,
                'competencia' => $row['competencia'] ?? null,
                'segmento_id' => $row['segmento_id'] ?? null,
                'segmento' => $row['segmento'] ?? null,
                'diretoria_id' => $row['diretoria_id'] ?? null,
                'diretoria_nome' => $row['diretoria_nome'] ?? null,
                'gerencia_id' => $row['gerencia_id'] ?? null,
                'gerencia_nome' => $row['gerencia_nome'] ?? null,
                'agencia_id' => $row['agencia_id'] ?? null,
                'agencia_nome' => $row['agencia_nome'] ?? null,
                'gerente_gestao_id' => $row['gerente_gestao_id'] ?? null,
                'gerente_gestao_nome' => $row['gerente_gestao_nome'] ?? null,
                'gerente_id' => $row['gerente_id'] ?? null,
                'gerente_nome' => $row['gerente_nome'] ?? null,
                'rank' => $row['rank'] !== null ? (int)$row['rank'] : null,
                'realizado_mensal' => $row['realizado_mensal'] !== null ? (float)$row['realizado_mensal'] : null,
                'meta_mensal' => $row['meta_mensal'] !== null ? (float)$row['meta_mensal'] : null,
                'pontos' => $row['pontos'] !== null ? (float)$row['pontos'] : null,
            ];
        }

        return $formatted;
    }
}

