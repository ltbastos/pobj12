<?php

namespace App\Repository\Pobj;

use App\Domain\DTO\FilterDTO;
use App\Domain\Enum\Cargo;
use App\Entity\Pobj\FRealizados;
use App\Entity\Pobj\FMeta;
use App\Entity\Pobj\DCalendario;
use App\Entity\Pobj\DEstrutura;
use App\Entity\Pobj\DProduto;
use App\Entity\Pobj\Familia;
use App\Entity\Pobj\Regional;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExecRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DProduto::class);
    }

    private function getTableName(string $entityClass): string
    {
        return $this->getEntityManager()
            ->getClassMetadata($entityClass)
            ->getTableName();
    }

    public function findKPIs(?FilterDTO $filters = null): array
    {
        $fRealizadosTable = $this->getTableName(FRealizados::class);
        $fMetaTable = $this->getTableName(FMeta::class);
        $dCalendarioTable = $this->getTableName(DCalendario::class);
        $dEstruturaTable = $this->getTableName(DEstrutura::class);

        $params = [];
        $whereClause = $this->buildWhereClause($filters, $params, false);

        $today = new \DateTime();
        $dataInicio = $filters ? $filters->getDataInicio() : null;
        $dataFim = $filters ? $filters->getDataFim() : null;

        if ($dataInicio && $dataFim) {
            $startDate = new \DateTime($dataInicio);
            $endDate = new \DateTime($dataFim);
            $startYear = (int)$startDate->format('Y');
            $startMonth = (int)$startDate->format('m');
            $endYear = (int)$endDate->format('Y');
            $endMonth = (int)$endDate->format('m');
            
            $dateFilterRealizados = " AND r.data_realizado >= :dataInicio AND r.data_realizado <= :dataFim";
            $dateFilterMeta = " AND m.data_meta >= :dataInicio AND m.data_meta <= :dataFim";
            $params['dataInicio'] = $dataInicio;
            $params['dataFim'] = $dataFim;
        } else {
            $currentYear = $today->format('Y');
            $currentMonthNum = (int)$today->format('m');
            $dateFilterRealizados = " AND YEAR(r.data_realizado) = :ano AND MONTH(r.data_realizado) = :mes";
            $dateFilterMeta = " AND YEAR(m.data_meta) = :ano AND MONTH(m.data_meta) = :mes";
            $params['ano'] = $currentYear;
            $params['mes'] = $currentMonthNum;
        }

        $sqlRealMens = "SELECT COALESCE(SUM(r.realizado), 0) as total
            FROM {$fRealizadosTable} AS r
            INNER JOIN {$dEstruturaTable} AS est ON est.funcional = r.funcional
            WHERE 1=1 {$dateFilterRealizados} {$whereClause}";

        $sqlMetaMens = "SELECT COALESCE(SUM(m.meta_mensal), 0) as total
            FROM {$fMetaTable} AS m
            INNER JOIN {$dEstruturaTable} AS est ON est.funcional = m.funcional
            WHERE 1=1 {$dateFilterMeta} {$whereClause}";

        if ($dataInicio && $dataFim) {
            $dateFilterAcum = " AND r.data_realizado >= :dataInicio AND r.data_realizado <= :dataFim";
            $dateFilterMetaAcum = " AND m.data_meta >= :dataInicio AND m.data_meta <= :dataFim";
        } else {
            $dateFilterAcum = " AND YEAR(r.data_realizado) = :ano AND MONTH(r.data_realizado) <= :mes";
            $dateFilterMetaAcum = " AND YEAR(m.data_meta) = :ano AND MONTH(m.data_meta) <= :mes";
        }

        $sqlRealAcum = "SELECT COALESCE(SUM(r.realizado), 0) as total
            FROM {$fRealizadosTable} AS r
            INNER JOIN {$dEstruturaTable} AS est ON est.funcional = r.funcional
            WHERE 1=1 {$dateFilterAcum} {$whereClause}";

        $sqlMetaAcum = "SELECT COALESCE(SUM(m.meta_mensal), 0) as total
            FROM {$fMetaTable} AS m
            INNER JOIN {$dEstruturaTable} AS est ON est.funcional = m.funcional
            WHERE 1=1 {$dateFilterMetaAcum} {$whereClause}";

        $conn = $this->getEntityManager()->getConnection();

        $realMens = $conn->executeQuery($sqlRealMens, $params)->fetchOne();
        $metaMens = $conn->executeQuery($sqlMetaMens, $params)->fetchOne();
        $realAcum = $conn->executeQuery($sqlRealAcum, $params)->fetchOne();
        $metaAcum = $conn->executeQuery($sqlMetaAcum, $params)->fetchOne();

        return [
            'real_mens' => (float)($realMens ?? 0),
            'meta_mens' => (float)($metaMens ?? 0),
            'real_acum' => (float)($realAcum ?? 0),
            'meta_acum' => (float)($metaAcum ?? 0)
        ];
    }

    public function findRankingByRegional(?FilterDTO $filters = null): array
    {
        $fRealizadosTable = $this->getTableName(FRealizados::class);
        $fMetaTable = $this->getTableName(FMeta::class);
        $dCalendarioTable = $this->getTableName(DCalendario::class);
        $dEstruturaTable = $this->getTableName(DEstrutura::class);
        $regionalTable = $this->getTableName(Regional::class);

        $params = [];
        $whereClause = $this->buildWhereClause($filters, $params, false);

        $today = new \DateTime();
        $dataInicio = $filters ? $filters->getDataInicio() : null;
        $dataFim = $filters ? $filters->getDataFim() : null;

        if ($dataInicio && $dataFim) {
            $dateFilter = " AND (cr.data IS NULL OR (cr.data >= :dataInicio AND cr.data <= :dataFim)) 
                            AND (cm.data IS NULL OR (cm.data >= :dataInicio AND cm.data <= :dataFim))";
            $params['dataInicio'] = $dataInicio;
            $params['dataFim'] = $dataFim;
        } else {
            $currentYear = $today->format('Y');
            $currentMonthNum = (int)$today->format('m');
            $dateFilter = " AND (cr.data IS NULL OR (YEAR(cr.data) = :ano AND MONTH(cr.data) = :mes)) 
                            AND (cm.data IS NULL OR (YEAR(cm.data) = :ano AND MONTH(cm.data) = :mes))";
            $params['ano'] = $currentYear;
            $params['mes'] = $currentMonthNum;
        }

        $sql = "SELECT
                    CAST(reg.id AS CHAR) AS `key`,
                    reg.nome AS label,
                    COALESCE(SUM(r.realizado), 0) AS real_mens,
                    COALESCE(SUM(m.meta_mensal), 0) AS meta_mens,
                    CASE 
                        WHEN COALESCE(SUM(m.meta_mensal), 0) > 0 
                        THEN (COALESCE(SUM(r.realizado), 0) / COALESCE(SUM(m.meta_mensal), 0)) * 100
                        ELSE 0
                    END AS p_mens
                FROM {$regionalTable} AS reg
                INNER JOIN {$dEstruturaTable} AS est ON est.regional_id = reg.id
                LEFT JOIN {$fRealizadosTable} AS r ON r.funcional = est.funcional
                    " . ($dataInicio && $dataFim ? "AND r.data_realizado >= :dataInicio AND r.data_realizado <= :dataFim" : ($dataInicio ? "AND YEAR(r.data_realizado) = :ano AND MONTH(r.data_realizado) = :mes" : "")) . "
                LEFT JOIN {$fMetaTable} AS m ON m.funcional = est.funcional
                    " . ($dataInicio && $dataFim ? "AND m.data_meta >= :dataInicio AND m.data_meta <= :dataFim" : ($dataInicio ? "AND YEAR(m.data_meta) = :ano AND MONTH(m.data_meta) = :mes" : "")) . "
                WHERE reg.id IS NOT NULL {$whereClause}
                GROUP BY reg.id, reg.nome
                HAVING real_mens > 0 OR meta_mens > 0
                ORDER BY p_mens DESC";

        $conn = $this->getEntityManager()->getConnection();
        $result = $conn->executeQuery($sql, $params);
        $rows = $result->fetchAllAssociative();

        $ranking = [];
        foreach ($rows as $row) {
            $ranking[] = [
                'key' => $row['key'] ?? '',
                'label' => $row['label'] ?? '',
                'real_mens' => (float)($row['real_mens'] ?? 0),
                'meta_mens' => (float)($row['meta_mens'] ?? 0),
                'p_mens' => (float)($row['p_mens'] ?? 0)
            ];
        }

        return $ranking;
    }

    public function findStatusByRegional(?FilterDTO $filters = null): array
    {
        $ranking = $this->findRankingByRegional($filters);

        $hit = [];
        $quase = [];
        $longe = [];

        foreach ($ranking as $item) {
            $pct = $item['p_mens'];
            $gap = $item['real_mens'] - $item['meta_mens'];

            if ($pct >= 100) {
                $hit[] = [
                    'key' => $item['key'],
                    'label' => $item['label'],
                    'p_mens' => $pct
                ];
            } elseif ($pct >= 90) {
                $quase[] = [
                    'key' => $item['key'],
                    'label' => $item['label'],
                    'p_mens' => $pct
                ];
            } else {
                $longe[] = [
                    'key' => $item['key'],
                    'label' => $item['label'],
                    'gap' => $gap
                ];
            }
        }

        usort($longe, function($a, $b) {
            return $a['gap'] <=> $b['gap'];
        });

        return [
            'hit' => $hit,
            'quase' => $quase,
            'longe' => array_slice($longe, 0, 3)
        ];
    }

    public function findChartData(?FilterDTO $filters = null): array
    {
        $fRealizadosTable = $this->getTableName(FRealizados::class);
        $fMetaTable = $this->getTableName(FMeta::class);
        $dCalendarioTable = $this->getTableName(DCalendario::class);
        $dEstruturaTable = $this->getTableName(DEstrutura::class);
        $dProdutosTable = $this->getTableName(DProduto::class);
        $familiaTable = $this->getTableName(Familia::class);

        $params = [];
        $whereClause = $this->buildWhereClause($filters, $params, false);

        $today = new \DateTime();
        $dataInicio = $filters ? $filters->getDataInicio() : null;
        $dataFim = $filters ? $filters->getDataFim() : null;

        $months = [];
        if ($dataInicio && $dataFim) {
            $start = new \DateTime($dataInicio);
            $end = new \DateTime($dataFim);
            $current = clone $start;
            while ($current <= $end) {
                $months[] = [
                    'key' => $current->format('Y-m'),
                    'label' => $current->format('M Y'),
                    'year' => (int)$current->format('Y'),
                    'month' => (int)$current->format('m')
                ];
                $current->modify('+1 month');
            }
            if (count($months) > 6) {
                $months = array_slice($months, -6);
            }
        } else {
            for ($i = 5; $i >= 0; $i--) {
                $date = clone $today;
                $date->modify("-{$i} months");
                $months[] = [
                    'key' => $date->format('Y-m'),
                    'label' => $date->format('M Y'),
                    'year' => (int)$date->format('Y'),
                    'month' => (int)$date->format('m')
                ];
            }
        }

        $monthKeys = array_column($months, 'key');
        $placeholders = implode(',', array_fill(0, count($monthKeys), '?'));
        
        $sql = "SELECT
                    DATE_FORMAT(c.data, '%Y-%m') AS mes,
                    CAST(fam.id AS CHAR) AS familia_id,
                    fam.nm_familia AS familia_nome,
                    COALESCE(SUM(r.realizado), 0) AS realizado,
                    COALESCE(SUM(m.meta_mensal), 0) AS meta,
                    CASE 
                        WHEN COALESCE(SUM(m.meta_mensal), 0) > 0 
                        THEN (COALESCE(SUM(r.realizado), 0) / COALESCE(SUM(m.meta_mensal), 0)) * 100
                        ELSE 0
                    END AS pct
                FROM {$dCalendarioTable} AS c
                INNER JOIN {$fRealizadosTable} AS r ON r.data_realizado = c.data
                INNER JOIN {$dEstruturaTable} AS est ON est.funcional = r.funcional
                INNER JOIN {$dProdutosTable} AS prod ON prod.id = r.produto_id
                INNER JOIN {$familiaTable} AS fam ON fam.id = prod.familia_id
                LEFT JOIN {$fMetaTable} AS m ON m.produto_id = prod.id
                    AND m.funcional = est.funcional
                    AND m.data_meta = c.data
                WHERE DATE_FORMAT(c.data, '%Y-%m') IN ({$placeholders})
                    {$whereClause}
                GROUP BY DATE_FORMAT(c.data, '%Y-%m'), fam.id, fam.nm_familia
                ORDER BY fam.nm_familia, c.data";

        foreach ($monthKeys as $key) {
            $params[] = $key;
        }

        $conn = $this->getEntityManager()->getConnection();
        $result = $conn->executeQuery($sql, $params);
        $rows = $result->fetchAllAssociative();

        // Organiza por família
        $seriesMap = [];
        $families = [];

        foreach ($rows as $row) {
            $familiaId = $row['familia_id'] ?? '';
            $familiaNome = $row['familia_nome'] ?? '';
            $mes = $row['mes'] ?? '';
            $pct = (float)($row['pct'] ?? 0);

            if (!isset($seriesMap[$familiaId])) {
                $seriesMap[$familiaId] = [
                    'id' => $familiaId,
                    'label' => $familiaNome,
                    'values' => []
                ];
                $families[$familiaId] = $familiaNome;
            }

            $seriesMap[$familiaId]['values'][$mes] = $pct;
        }

        $series = [];
        $colors = ['#2563eb', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4'];
        $colorIndex = 0;

        foreach ($seriesMap as $familiaId => $serie) {
            $values = [];
            foreach ($months as $month) {
                $values[] = $serie['values'][$month['key']] ?? null;
            }
            
            $series[] = [
                'id' => $serie['id'],
                'label' => $serie['label'],
                'values' => $values,
                'color' => $colors[$colorIndex % count($colors)]
            ];
            $colorIndex++;
        }

        return [
            'keys' => array_column($months, 'key'),
            'labels' => array_column($months, 'label'),
            'series' => $series
        ];
    }

    public function findHeatmap(?FilterDTO $filters = null): array
    {
        $fRealizadosTable = $this->getTableName(FRealizados::class);
        $fMetaTable = $this->getTableName(FMeta::class);
        $dCalendarioTable = $this->getTableName(DCalendario::class);
        $dEstruturaTable = $this->getTableName(DEstrutura::class);
        $dProdutosTable = $this->getTableName(DProduto::class);
        $regionalTable = $this->getTableName(Regional::class);
        $familiaTable = $this->getTableName(Familia::class);

        $params = [];
        $whereClause = $this->buildWhereClause($filters, $params, false);

        $today = new \DateTime();
        $dataInicio = $filters ? $filters->getDataInicio() : null;
        $dataFim = $filters ? $filters->getDataFim() : null;

        $dateFilterRealizados = '';
        $dateFilterMeta = '';
        
        if ($dataInicio && $dataFim) {
            $dateFilterRealizados = " AND r.data_realizado >= :dataInicio AND r.data_realizado <= :dataFim";
            $dateFilterMeta = " AND m.data_meta >= :dataInicio AND m.data_meta <= :dataFim";
            $params['dataInicio'] = $dataInicio;
            $params['dataFim'] = $dataFim;
        } else {
            $currentYear = $today->format('Y');
            $currentMonthNum = (int)$today->format('m');
            $dateFilterRealizados = " AND YEAR(r.data_realizado) = :ano AND MONTH(r.data_realizado) = :mes";
            $dateFilterMeta = " AND YEAR(m.data_meta) = :ano AND MONTH(m.data_meta) = :mes";
            $params['ano'] = $currentYear;
            $params['mes'] = $currentMonthNum;
        }

        $sql = "SELECT
                    CAST(reg.id AS CHAR) AS regional_id,
                    reg.nome AS regional_nome,
                    CAST(fam.id AS CHAR) AS familia_id,
                    fam.nm_familia AS familia_nome,
                    COALESCE(SUM(r.realizado), 0) AS realizado,
                    COALESCE(SUM(m.meta_mensal), 0) AS meta
                FROM {$regionalTable} AS reg
                INNER JOIN {$dEstruturaTable} AS est ON est.regional_id = reg.id
                INNER JOIN {$fRealizadosTable} AS r ON r.funcional = est.funcional
                INNER JOIN {$dProdutosTable} AS prod ON prod.id = r.produto_id
                INNER JOIN {$familiaTable} AS fam ON fam.id = prod.familia_id
                LEFT JOIN {$fMetaTable} AS m ON m.produto_id = prod.id
                    AND m.funcional = est.funcional
                WHERE reg.id IS NOT NULL {$whereClause}
                GROUP BY reg.id, reg.nome, fam.id, fam.nm_familia
                HAVING realizado > 0 OR meta > 0
                ORDER BY reg.nome, fam.nm_familia";

        $conn = $this->getEntityManager()->getConnection();
        $result = $conn->executeQuery($sql, $params);
        $rows = $result->fetchAllAssociative();

        $units = [];
        $sections = [];
        $data = [];

        foreach ($rows as $row) {
            $regionalId = $row['regional_id'] ?? '';
            $regionalNome = $row['regional_nome'] ?? '';
            $familiaId = $row['familia_id'] ?? '';
            $familiaNome = $row['familia_nome'] ?? '';
            $realizado = (float)($row['realizado'] ?? 0);
            $meta = (float)($row['meta'] ?? 0);

            if (!isset($units[$regionalId])) {
                $units[$regionalId] = [
                    'value' => $regionalId,
                    'label' => $regionalNome
                ];
            }

            if (!isset($sections[$familiaId])) {
                $sections[$familiaId] = [
                    'id' => $familiaId,
                    'label' => $familiaNome
                ];
            }

            $key = "{$regionalId}|{$familiaId}";
            $data[$key] = [
                'real' => $realizado,
                'meta' => $meta
            ];
        }

        return [
            'units' => array_values($units),
            'sections' => array_values($sections),
            'data' => $data
        ];
    }

    private function buildWhereClause(?FilterDTO $filters, array &$params, bool $includeDateFilters = true): string
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

        if ($includeDateFilters) {
            $dataInicio = $filters->getDataInicio();
            $dataFim = $filters->getDataFim();

            if ($dataInicio !== null && $dataInicio !== '') {
                $whereClause .= " AND c.data >= :dataInicio";
                $params['dataInicio'] = $dataInicio;
            }

            if ($dataFim !== null && $dataFim !== '') {
                $whereClause .= " AND c.data <= :dataFim";
                $params['dataFim'] = $dataFim;
            }
        }

        return $whereClause;
    }
}

