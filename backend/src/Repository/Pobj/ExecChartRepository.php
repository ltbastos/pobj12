<?php

namespace App\Repository\Pobj;

use App\Domain\DTO\FilterDTO;
use App\Entity\Pobj\FRealizados;
use App\Entity\Pobj\FMeta;
use App\Entity\Pobj\DCalendario;
use App\Entity\Pobj\DEstrutura;
use App\Entity\Pobj\DProduto;
use App\Entity\Pobj\Familia;
use App\Repository\Pobj\Helper\ExecFilterBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExecChartRepository extends ServiceEntityRepository
{
    private $filterBuilder;

    public function __construct(ManagerRegistry $registry, ExecFilterBuilder $filterBuilder)
    {
        parent::__construct($registry, FRealizados::class);
        $this->filterBuilder = $filterBuilder;
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
        $whereClause = $this->filterBuilder->buildWhereClause(
            $filters,
            $params,
            false,
            [
                'produto' => 'prod',
                'familia' => 'fam'
            ]
        );

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
        $monthPlaceholders = [];
        foreach ($monthKeys as $index => $key) {
            $paramName = 'mes' . $index;
            $monthPlaceholders[] = ':' . $paramName;
            $params[$paramName] = $key;
        }
        $placeholders = implode(',', $monthPlaceholders);
        
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

        $conn = $this->getEntityManager()->getConnection();
        $result = $conn->executeQuery($sql, $params);
        
        $seriesMap = [];
        $families = [];

        while ($row = $result->fetchAssociative()) {
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
        $result->free();

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

    private function getTableName(string $entityClass): string
    {
        return $this->getEntityManager()
            ->getClassMetadata($entityClass)
            ->getTableName();
    }
}
