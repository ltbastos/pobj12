<?php

namespace App\Repository\Pobj;

use App\Domain\DTO\FilterDTO;
use App\Domain\Enum\Cargo;
use App\Entity\Pobj\FRealizados;
use App\Entity\Pobj\FMeta;
use App\Entity\Pobj\FPontos;
use App\Entity\Pobj\FVariavel;
use App\Entity\Pobj\DCalendario;
use App\Entity\Pobj\DEstrutura;
use App\Entity\Pobj\DProduto;
use App\Entity\Pobj\Familia;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class SimuladorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DProduto::class);
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
     * Busca produtos com dados agregados para o simulador
     */
    public function findProdutosForSimulador(?FilterDTO $filters = null): array
    {
        $dProdutosTable = $this->getTableName(DProduto::class);
        $fMetaTable = $this->getTableName(FMeta::class);
        $fRealizadosTable = $this->getTableName(FRealizados::class);
        $fPontosTable = $this->getTableName(FPontos::class);
        $fVariavelTable = $this->getTableName(FVariavel::class);
        $dEstruturaTable = $this->getTableName(DEstrutura::class);
        $familiaTable = $this->getTableName(Familia::class);
        $indicadorTable = $this->getTableName(\App\Entity\Pobj\Indicador::class);
        $subindicadorTable = $this->getTableName(\App\Entity\Pobj\Subindicador::class);

        $params = [];
        $whereClause = $this->buildWhereClause($filters, $params);

        // Filtros de data removidos - simulador não usa filtro de data

        // Usa subconsultas para agregar corretamente
        $sql = "SELECT
                    CAST(dp.id AS CHAR) AS id,
                    CAST(f.id AS CHAR) AS sectionId,
                    f.nm_familia AS sectionLabel,
                    dp.metrica AS metric,
                    COALESCE(meta_agg.total_meta, 0) AS meta,
                    COALESCE(realizado_agg.total_realizado, 0) AS realizado,
                    COALESCE(pontos_agg.total_meta_pontos, 0) AS pontosMeta,
                    COALESCE(pontos_agg.total_pontos, 0) AS pontosBrutos,
                    COALESCE(pontos_agg.total_pontos, 0) AS pontos,
                    COALESCE(variavel_agg.total_variavel_meta, 0) AS variavelMeta,
                    COALESCE(variavel_agg.total_variavel_real, 0) AS variavelReal,
                    realizado_agg.ultima_atualizacao AS ultimaAtualizacao,
                    CONCAT(
                        COALESCE(f.nm_familia, ''), 
                        CASE WHEN i.nm_indicador IS NOT NULL THEN CONCAT(' - ', i.nm_indicador) ELSE '' END,
                        CASE WHEN s.nm_subindicador IS NOT NULL THEN CONCAT(' - ', s.nm_subindicador) ELSE '' END
                    ) AS label
                FROM {$dProdutosTable} AS dp
                INNER JOIN {$familiaTable} AS f ON f.id = dp.familia_id
                LEFT JOIN {$indicadorTable} AS i ON i.id = dp.indicador_id
                LEFT JOIN {$subindicadorTable} AS s ON s.id = dp.subindicador_id
                LEFT JOIN (
                    SELECT 
                        m.produto_id,
                        SUM(m.meta_mensal) AS total_meta
                    FROM {$fMetaTable} AS m
                    LEFT JOIN {$dEstruturaTable} AS est ON est.funcional = m.funcional
                    WHERE 1=1 {$whereClause}
                    GROUP BY m.produto_id
                ) AS meta_agg ON meta_agg.produto_id = dp.id
                LEFT JOIN (
                    SELECT 
                        r.produto_id,
                        SUM(r.realizado) AS total_realizado,
                        MAX(r.data_realizado) AS ultima_atualizacao
                    FROM {$fRealizadosTable} AS r
                    LEFT JOIN {$dEstruturaTable} AS est ON est.funcional = r.funcional
                    WHERE r.produto_id IS NOT NULL {$whereClause}
                    GROUP BY r.produto_id
                ) AS realizado_agg ON realizado_agg.produto_id = dp.id
                LEFT JOIN (
                    SELECT 
                        p.produto_id,
                        SUM(p.meta) AS total_meta_pontos,
                        SUM(p.realizado) AS total_pontos
                    FROM {$fPontosTable} AS p
                    LEFT JOIN {$dEstruturaTable} AS est ON est.funcional = p.funcional
                    WHERE 1=1 {$whereClause}
                    GROUP BY p.produto_id
                ) AS pontos_agg ON pontos_agg.produto_id = dp.id
                LEFT JOIN (
                    SELECT 
                        r.produto_id,
                        SUM(v.meta) AS total_variavel_meta,
                        SUM(v.variavel) AS total_variavel_real
                    FROM {$fVariavelTable} AS v
                    LEFT JOIN {$dEstruturaTable} AS est ON est.funcional = v.funcional
                    LEFT JOIN {$fRealizadosTable} AS r ON r.funcional = est.funcional
                    WHERE 1=1 {$whereClause}
                    GROUP BY r.produto_id
                ) AS variavel_agg ON variavel_agg.produto_id = dp.id
                WHERE dp.id IS NOT NULL
                    AND dp.metrica IN ('valor', 'qtd')
                HAVING meta > 0
                ORDER BY f.nm_familia, i.nm_indicador, s.nm_subindicador";

        $conn = $this->getEntityManager()->getConnection();
        $result = $conn->executeQuery($sql, $params);
        $rows = $result->fetchAllAssociative();

        $produtos = [];
        foreach ($rows as $row) {
            $ultimaAtualizacao = $row['ultimaAtualizacao'] ?? null;
            if ($ultimaAtualizacao instanceof \DateTimeInterface) {
                $ultimaAtualizacao = $ultimaAtualizacao->format('d/m/Y H:i');
            } elseif (is_string($ultimaAtualizacao)) {
                try {
                    $date = new \DateTime($ultimaAtualizacao);
                    $ultimaAtualizacao = $date->format('d/m/Y H:i');
                } catch (\Exception $e) {
                    $ultimaAtualizacao = null;
                }
            }

            $produtos[] = [
                'id' => $row['id'] ?? '',
                'label' => $row['label'] ?? '',
                'sectionId' => $row['sectionId'] ?? '',
                'sectionLabel' => $row['sectionLabel'] ?? '',
                'metric' => $row['metric'] ?? 'valor',
                'meta' => (float)($row['meta'] ?? 0),
                'realizado' => (float)($row['realizado'] ?? 0),
                'variavelMeta' => (float)($row['variavelMeta'] ?? 0),
                'variavelReal' => (float)($row['variavelReal'] ?? 0),
                'pontosMeta' => (float)($row['pontosMeta'] ?? 0),
                'pontosBrutos' => (float)($row['pontosBrutos'] ?? 0),
                'pontos' => (float)($row['pontos'] ?? 0),
                'ultimaAtualizacao' => $ultimaAtualizacao
            ];
        }

        return $produtos;
    }

    /**
     * Constrói a cláusula WHERE baseada nos filtros
     */
    private function buildWhereClause(?FilterDTO $filters, array &$params): string
    {
        $whereClause = '';

        if (!$filters) {
            return $whereClause;
        }

        $estruturaTable = $this->getTableName(DEstrutura::class);
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

        return $whereClause;
    }
}

