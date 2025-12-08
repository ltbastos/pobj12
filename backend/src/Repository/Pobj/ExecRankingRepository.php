<?php

namespace App\Repository\Pobj;

use App\Domain\DTO\FilterDTO;
use App\Domain\Enum\Cargo;
use App\Entity\Pobj\FRealizados;
use App\Entity\Pobj\FMeta;
use App\Entity\Pobj\DEstrutura;
use App\Entity\Pobj\Regional;
use App\Entity\Pobj\Diretoria;
use App\Entity\Pobj\Agencia;
use App\Repository\Pobj\Helper\ExecFilterBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExecRankingRepository extends ServiceEntityRepository
{
    private $filterBuilder;

    public function __construct(ManagerRegistry $registry, ExecFilterBuilder $filterBuilder)
    {
        parent::__construct($registry, FRealizados::class);
        $this->filterBuilder = $filterBuilder;
    }

    public function findRankingByRegional(?FilterDTO $filters = null): array
    {
        $fRealizadosTable = $this->getTableName(FRealizados::class);
        $fMetaTable = $this->getTableName(FMeta::class);
        $dEstruturaTable = $this->getTableName(DEstrutura::class);
        $regionalTable = $this->getTableName(Regional::class);
        $diretoriaTable = $this->getTableName(Diretoria::class);
        $agenciaTable = $this->getTableName(Agencia::class);

        $params = [];
        $whereClause = $this->filterBuilder->buildWhereClause(
            $filters,
            $params,
            false,
            [
                'realizado' => 'r',
                'meta' => 'm'
            ]
        );

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

        // Determinar o nível hierárquico para agrupar baseado nos filtros
        $gerente = $filters ? $filters->getGerente() : null;
        $gerenteGestao = $filters ? $filters->getGerenteGestao() : null;
        $agencia = $filters ? $filters->getAgencia() : null;
        $regional = $filters ? $filters->getRegional() : null;
        $diretoria = $filters ? $filters->getDiretoria() : null;
        $segmento = $filters ? $filters->getSegmento() : null;

        // Determinar qual nível agrupar (sempre o nível imediatamente abaixo do filtro)
        $groupByLevel = 'regional'; // padrão
        $groupByClause = "reg.id, reg.nome";
        $selectClause = "CAST(reg.id AS CHAR) AS `key`, reg.nome AS label";
        $fromClause = "{$regionalTable} AS reg
                INNER JOIN {$dEstruturaTable} AS est ON est.regional_id = reg.id";

        if ($gerente !== null && $gerente !== '') {
            // Se gerente está filtrado, não faz sentido agrupar (apenas dados dele)
            return [];
        } elseif ($gerenteGestao !== null && $gerenteGestao !== '') {
            // Agrupar por gerente (nível abaixo do gerente de gestão)
            $groupByLevel = 'gerente';
            $selectClause = "CAST(est.id AS CHAR) AS `key`, est.nome AS label";
            $groupByClause = "est.id, est.nome";
            $fromClause = "{$dEstruturaTable} AS est";
            $whereClause .= " AND est.cargo_id = :cargoGerenteRanking";
            $params['cargoGerenteRanking'] = Cargo::GERENTE;
        } elseif ($agencia !== null && $agencia !== '') {
            // Agrupar por gerente de gestão (nível abaixo da agência)
            $groupByLevel = 'gerenteGestao';
            $selectClause = "CAST(est.id AS CHAR) AS `key`, est.nome AS label";
            $groupByClause = "est.id, est.nome";
            $fromClause = "{$dEstruturaTable} AS est";
            $whereClause .= " AND est.cargo_id = :cargoGerenteGestaoRanking";
            $params['cargoGerenteGestaoRanking'] = Cargo::GERENTE_GESTAO;
        } elseif ($regional !== null && $regional !== '') {
            // Agrupar por agência (nível abaixo da regional)
            $groupByLevel = 'agencia';
            $selectClause = "CAST(ag.id AS CHAR) AS `key`, ag.nome AS label";
            $groupByClause = "ag.id, ag.nome";
            $fromClause = "{$agenciaTable} AS ag
                INNER JOIN {$dEstruturaTable} AS est ON est.agencia_id = ag.id";
        } elseif ($diretoria !== null && $diretoria !== '') {
            // Agrupar por regional (nível abaixo da diretoria)
            $groupByLevel = 'regional';
            $selectClause = "CAST(reg.id AS CHAR) AS `key`, reg.nome AS label";
            $groupByClause = "reg.id, reg.nome";
            $fromClause = "{$regionalTable} AS reg
                INNER JOIN {$dEstruturaTable} AS est ON est.regional_id = reg.id";
        } elseif ($segmento !== null && $segmento !== '') {
            // Agrupar por diretoria (nível abaixo do segmento)
            $groupByLevel = 'diretoria';
            $selectClause = "CAST(dir.id AS CHAR) AS `key`, dir.nome AS label";
            $groupByClause = "dir.id, dir.nome";
            $fromClause = "{$diretoriaTable} AS dir
                INNER JOIN {$dEstruturaTable} AS est ON est.diretoria_id = dir.id";
        }

        // Construir condições de data para os JOINs
        $realizadoDateCondition = "";
        $metaDateCondition = "";
        
        if ($dataInicio && $dataFim) {
            $realizadoDateCondition = " AND r.data_realizado >= :dataInicio AND r.data_realizado <= :dataFim";
            $metaDateCondition = " AND m.data_meta >= :dataInicio AND m.data_meta <= :dataFim";
        } else {
            $realizadoDateCondition = " AND YEAR(r.data_realizado) = :ano AND MONTH(r.data_realizado) = :mes";
            $metaDateCondition = " AND YEAR(m.data_meta) = :ano AND MONTH(m.data_meta) = :mes";
        }

        $sql = "SELECT
                    {$selectClause},
                    COALESCE(SUM(r.realizado), 0) AS real_mens,
                    COALESCE(SUM(m.meta_mensal), 0) AS meta_mens,
                    CASE 
                        WHEN COALESCE(SUM(m.meta_mensal), 0) > 0 
                        THEN (COALESCE(SUM(r.realizado), 0) / COALESCE(SUM(m.meta_mensal), 0)) * 100
                        ELSE 0
                    END AS p_mens
                FROM {$fromClause}
                LEFT JOIN {$fRealizadosTable} AS r ON r.funcional = est.funcional{$realizadoDateCondition}
                LEFT JOIN {$fMetaTable} AS m ON m.funcional = est.funcional 
                    AND m.produto_id = r.produto_id 
                    AND m.data_meta = r.data_realizado{$metaDateCondition}
                WHERE 1=1 {$whereClause}
                GROUP BY {$groupByClause}
                HAVING real_mens > 0 OR meta_mens > 0
                ORDER BY p_mens DESC";

        $conn = $this->getEntityManager()->getConnection();
        $result = $conn->executeQuery($sql, $params);
        
        $ranking = [];
        while ($row = $result->fetchAssociative()) {
            $ranking[] = [
                'key' => $row['key'] ?? '',
                'label' => $row['label'] ?? '',
                'real_mens' => (float)($row['real_mens'] ?? 0),
                'meta_mens' => (float)($row['meta_mens'] ?? 0),
                'p_mens' => (float)($row['p_mens'] ?? 0)
            ];
        }
        $result->free();

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

    private function getTableName(string $entityClass): string
    {
        return $this->getEntityManager()
            ->getClassMetadata($entityClass)
            ->getTableName();
    }
}
