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
use App\Entity\Pobj\Indicador;
use App\Entity\Pobj\Regional;
use App\Entity\Pobj\Diretoria;
use App\Entity\Pobj\Agencia;
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
        $diretoriaTable = $this->getTableName(Diretoria::class);
        $agenciaTable = $this->getTableName(Agencia::class);

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
            // O whereClause já filtra pelo gerente de gestão, então apenas agrupamos os gerentes abaixo dele
            $groupByLevel = 'gerente';
            $selectClause = "CAST(est.id AS CHAR) AS `key`, est.nome AS label";
            $groupByClause = "est.id, est.nome";
            $fromClause = "{$dEstruturaTable} AS est";
            $whereClause .= " AND est.cargo_id = :cargoGerenteRanking";
            $params['cargoGerenteRanking'] = Cargo::GERENTE;
        } elseif ($agencia !== null && $agencia !== '') {
            // Agrupar por gerente de gestão (nível abaixo da agência)
            // O whereClause já filtra pela agência, então apenas agrupamos os gerentes de gestão abaixo dela
            $groupByLevel = 'gerenteGestao';
            $selectClause = "CAST(est.id AS CHAR) AS `key`, est.nome AS label";
            $groupByClause = "est.id, est.nome";
            $fromClause = "{$dEstruturaTable} AS est";
            $whereClause .= " AND est.cargo_id = :cargoGerenteGestaoRanking";
            $params['cargoGerenteGestaoRanking'] = Cargo::GERENTE_GESTAO;
        } elseif ($regional !== null && $regional !== '') {
            // Agrupar por agência (nível abaixo da regional)
            // O whereClause já filtra pela regional, então apenas agrupamos as agências abaixo dela
            $groupByLevel = 'agencia';
            $selectClause = "CAST(ag.id AS CHAR) AS `key`, ag.nome AS label";
            $groupByClause = "ag.id, ag.nome";
            $fromClause = "{$agenciaTable} AS ag
                INNER JOIN {$dEstruturaTable} AS est ON est.agencia_id = ag.id";
        } elseif ($diretoria !== null && $diretoria !== '') {
            // Agrupar por regional (nível abaixo da diretoria)
            // O whereClause já filtra pela diretoria, então apenas agrupamos as regionais abaixo dela
            $groupByLevel = 'regional';
            $selectClause = "CAST(reg.id AS CHAR) AS `key`, reg.nome AS label";
            $groupByClause = "reg.id, reg.nome";
            $fromClause = "{$regionalTable} AS reg
                INNER JOIN {$dEstruturaTable} AS est ON est.regional_id = reg.id";
        } elseif ($segmento !== null && $segmento !== '') {
            // Agrupar por diretoria (nível abaixo do segmento)
            // O whereClause já filtra pelo segmento, então apenas agrupamos as diretorias abaixo dele
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

    public function findHeatmap(?FilterDTO $filters = null): array
    {
        $fRealizadosTable = $this->getTableName(FRealizados::class);
        $fMetaTable = $this->getTableName(FMeta::class);
        $dCalendarioTable = $this->getTableName(DCalendario::class);
        $dEstruturaTable = $this->getTableName(DEstrutura::class);
        $dProdutosTable = $this->getTableName(DProduto::class);
        $regionalTable = $this->getTableName(Regional::class);
        $familiaTable = $this->getTableName(Familia::class);
        $indicadorTable = $this->getTableName(Indicador::class);

        $params = [];
        $whereClause = $this->buildWhereClause($filters, $params, false);

        $today = new \DateTime();
        $dataInicio = $filters ? $filters->getDataInicio() : null;
        $dataFim = $filters ? $filters->getDataFim() : null;

        // Função para formatar mês em português
        $formatMonthPT = function(\DateTime $date): string {
            $meses = [
                1 => 'jan', 2 => 'fev', 3 => 'mar', 4 => 'abr',
                5 => 'mai', 6 => 'jun', 7 => 'jul', 8 => 'ago',
                9 => 'set', 10 => 'out', 11 => 'nov', 12 => 'dez'
            ];
            $mes = (int)$date->format('m');
            $ano = $date->format('Y');
            return $meses[$mes] . ' de ' . $ano;
        };

        // Para o heatmap, sempre mostrar os últimos 12 meses: do mesmo mês do ano passado até o mês atual
        // Exemplo: se estamos em dezembro de 2025, mostrar de dezembro de 2024 até dezembro de 2025
        // Isso é independente dos filtros de data (que são usados apenas para buscar os dados)
        $currentMonth = clone $today;
        $currentMonth->modify('first day of this month');
        $currentMonth->setTime(0, 0, 0);
        
        // Começar 11 meses antes do mês atual (totalizando 12 meses)
        $startMonth = clone $currentMonth;
        $startMonth->modify('-11 months');
        
        $months = [];
        $date = clone $startMonth;
        while ($date <= $currentMonth) {
            $months[] = [
                'key' => $date->format('Y-m'),
                'label' => $formatMonthPT($date),
                'year' => (int)$date->format('Y'),
                'month' => (int)$date->format('m')
            ];
            $date->modify('+1 month');
        }

                $sql = "SELECT
                    CAST(reg.id AS CHAR) AS regional_id,
                    reg.nome AS regional_nome,
                    CAST(fam.id AS CHAR) AS familia_id,
                    fam.nm_familia AS familia_nome,
                    CAST(ind.id AS CHAR) AS indicador_id,
                    ind.nm_indicador AS indicador_nome,
                    DATE_FORMAT(c.data, '%Y-%m') AS mes,
                    COALESCE(SUM(r.realizado), 0) AS realizado,
                    COALESCE(SUM(m.meta_mensal), 0) AS meta
                FROM {$regionalTable} AS reg
                INNER JOIN {$dEstruturaTable} AS est ON est.regional_id = reg.id
                INNER JOIN {$fRealizadosTable} AS r ON r.funcional = est.funcional
                INNER JOIN {$dCalendarioTable} AS c ON c.data = r.data_realizado
                INNER JOIN {$dProdutosTable} AS prod ON prod.id = r.produto_id
                INNER JOIN {$familiaTable} AS fam ON fam.id = prod.familia_id
                INNER JOIN {$indicadorTable} AS ind ON ind.id = prod.indicador_id
                LEFT JOIN {$fMetaTable} AS m ON m.produto_id = prod.id
                    AND m.funcional = est.funcional
                    AND m.data_meta = c.data
                WHERE reg.id IS NOT NULL {$whereClause}";

        // Para o heatmap, sempre usar os últimos 12 meses (não aplicar filtros de data)
        // Os meses já foram calculados acima como os últimos 12 meses
        $monthKeys = array_column($months, 'key');
        $monthPlaceholders = [];
        foreach ($monthKeys as $index => $key) {
            $paramName = 'mes' . $index;
            $monthPlaceholders[] = ':' . $paramName;
            $params[$paramName] = $key;
        }
        $placeholders = implode(',', $monthPlaceholders);
        $sql .= " AND DATE_FORMAT(c.data, '%Y-%m') IN ({$placeholders})";

        $sql .= " GROUP BY reg.id, reg.nome, fam.id, fam.nm_familia, ind.id, ind.nm_indicador, DATE_FORMAT(c.data, '%Y-%m')
                HAVING realizado > 0 OR meta > 0
                ORDER BY reg.nome, fam.nm_familia, ind.nm_indicador, c.data";

        $conn = $this->getEntityManager()->getConnection();
        $result = $conn->executeQuery($sql, $params);
        
        // Buscar TODAS as famílias para o modo seções (independente de ter dados ou não)
        $allFamiliesSql = "SELECT DISTINCT 
            CAST(fam.id AS CHAR) AS familia_id,
            fam.nm_familia AS familia_nome
        FROM {$familiaTable} AS fam
        ORDER BY fam.nm_familia";
        
        $allFamiliesResult = $conn->executeQuery($allFamiliesSql);
        $allFamilies = [];
        while ($famRow = $allFamiliesResult->fetchAssociative()) {
            $allFamilies[$famRow['familia_id']] = [
                'id' => $famRow['familia_id'],
                'label' => $famRow['familia_nome']
            ];
        }
        $allFamiliesResult->free();
        
        $units = [];
        $sectionsFamilia = [];
        $sectionsIndicador = [];
        $dataFamilia = [];
        $dataIndicador = [];
        $dataFamiliaMensal = [];
        $dataIndicadorMensal = [];

        while ($row = $result->fetchAssociative()) {
            $regionalId = $row['regional_id'] ?? '';
            $regionalNome = $row['regional_nome'] ?? '';
            $familiaId = $row['familia_id'] ?? '';
            $familiaNome = $row['familia_nome'] ?? '';
            $indicadorId = $row['indicador_id'] ?? '';
            $indicadorNome = $row['indicador_nome'] ?? '';
            $mes = $row['mes'] ?? '';
            $realizado = (float)($row['realizado'] ?? 0);
            $meta = (float)($row['meta'] ?? 0);

            if (!isset($units[$regionalId])) {
                $units[$regionalId] = [
                    'value' => $regionalId,
                    'label' => $regionalNome
                ];
            }

                        if (!isset($sectionsFamilia[$familiaId])) {
                $sectionsFamilia[$familiaId] = [
                    'id' => $familiaId,
                    'label' => $familiaNome
                ];
            }

                        if (!isset($sectionsIndicador[$indicadorId])) {
                $sectionsIndicador[$indicadorId] = [
                    'id' => $indicadorId,
                    'label' => $indicadorNome
                ];
            }

                        $keyFamilia = "{$regionalId}|{$familiaId}";
            if (!isset($dataFamilia[$keyFamilia])) {
                $dataFamilia[$keyFamilia] = [
                    'real' => 0,
                    'meta' => 0
                ];
            }
            $dataFamilia[$keyFamilia]['real'] += $realizado;
            $dataFamilia[$keyFamilia]['meta'] += $meta;

                        $keyIndicador = "{$regionalId}|{$indicadorId}";
            if (!isset($dataIndicador[$keyIndicador])) {
                $dataIndicador[$keyIndicador] = [
                    'real' => 0,
                    'meta' => 0
                ];
            }
            $dataIndicador[$keyIndicador]['real'] += $realizado;
            $dataIndicador[$keyIndicador]['meta'] += $meta;

                        $keyFamiliaMensal = "{$regionalId}|{$familiaId}|{$mes}";
            if (!isset($dataFamiliaMensal[$keyFamiliaMensal])) {
                $dataFamiliaMensal[$keyFamiliaMensal] = [
                    'real' => 0,
                    'meta' => 0
                ];
            }
            $dataFamiliaMensal[$keyFamiliaMensal]['real'] += $realizado;
            $dataFamiliaMensal[$keyFamiliaMensal]['meta'] += $meta;

                        $keyIndicadorMensal = "{$regionalId}|{$indicadorId}|{$mes}";
            if (!isset($dataIndicadorMensal[$keyIndicadorMensal])) {
                $dataIndicadorMensal[$keyIndicadorMensal] = [
                    'real' => 0,
                    'meta' => 0
                ];
            }
            $dataIndicadorMensal[$keyIndicadorMensal]['real'] += $realizado;
            $dataIndicadorMensal[$keyIndicadorMensal]['meta'] += $meta;
        }
        $result->free();
        
        // Mesclar todas as famílias encontradas nos dados com todas as famílias disponíveis
        // Isso garante que o modo "Seções" mostre todas as famílias, mesmo que não tenham dados
        foreach ($allFamilies as $familiaId => $familia) {
            if (!isset($sectionsFamilia[$familiaId])) {
                $sectionsFamilia[$familiaId] = $familia;
            }
        }

        // Buscar hierarquia completa para o modo de metas
        // Retornar unidades agregadas: Diretoria, Todas as Regionais, Todas as Agências, Todas as Ger. de Gestão, Todas as Gerentes
        $hierarchyUnits = [];
        $hierarchyDataMensal = [];
        
        $diretoriaTable = $this->getTableName(Diretoria::class);
        $agenciaTable = $this->getTableName(Agencia::class);
        
        // Query para agregar metas por nível hierárquico e mês
        // Buscar diretamente da tabela de metas, não precisa de realizados
        $hierarchySql = "SELECT
            CAST(dir.id AS CHAR) AS diretoria_id,
            dir.nome AS diretoria_nome,
            CAST(reg.id AS CHAR) AS regional_id,
            reg.nome AS regional_nome,
            CAST(ag.id AS CHAR) AS agencia_id,
            ag.nome AS agencia_nome,
            CASE 
                WHEN est.cargo_id = :cargoGerente THEN CAST(est.id AS CHAR)
                WHEN est.cargo_id = :cargoGerenteGestao THEN CAST(est.id AS CHAR)
                ELSE NULL
            END AS gerente_id,
            CASE 
                WHEN est.cargo_id = :cargoGerente THEN est.nome
                WHEN est.cargo_id = :cargoGerenteGestao THEN est.nome
                ELSE NULL
            END AS gerente_nome,
            CASE 
                WHEN est.cargo_id = :cargoGerente THEN :tipoGerente
                WHEN est.cargo_id = :cargoGerenteGestao THEN :tipoGerenteGestao
                ELSE NULL
            END AS tipo_gerente,
            DATE_FORMAT(m.data_meta, '%Y-%m') AS mes,
            COALESCE(SUM(m.meta_mensal), 0) AS meta
        FROM {$dEstruturaTable} AS est
        INNER JOIN {$fMetaTable} AS m ON m.funcional = est.funcional
        LEFT JOIN {$diretoriaTable} AS dir ON dir.id = est.diretoria_id
        LEFT JOIN {$regionalTable} AS reg ON reg.id = est.regional_id
        LEFT JOIN {$agenciaTable} AS ag ON ag.id = est.agencia_id
        WHERE 1=1 {$whereClause}
            AND m.meta_mensal IS NOT NULL
            AND m.meta_mensal > 0";
        
        $hierarchyParams = array_merge($params, [
            'cargoGerente' => Cargo::GERENTE,
            'cargoGerenteGestao' => Cargo::GERENTE_GESTAO,
            'tipoGerente' => 'gerente',
            'tipoGerenteGestao' => 'gerenteGestao'
        ]);
        
        // Para o heatmap, sempre usar os últimos 12 meses (não aplicar filtros de data)
        // Os meses já foram calculados acima como os últimos 12 meses
        $monthKeys = array_column($months, 'key');
        $monthPlaceholders = [];
        foreach ($monthKeys as $index => $key) {
            $paramName = 'mesHierarchy' . $index;
            $monthPlaceholders[] = ':' . $paramName;
            $hierarchyParams[$paramName] = $key;
        }
        $placeholders = implode(',', $monthPlaceholders);
        $hierarchySql .= " AND DATE_FORMAT(m.data_meta, '%Y-%m') IN ({$placeholders})";
        
        $hierarchySql .= " GROUP BY 
            dir.id, dir.nome, 
            reg.id, reg.nome, 
            ag.id, ag.nome, 
            est.id, est.nome, est.cargo_id,
            DATE_FORMAT(m.data_meta, '%Y-%m')
        HAVING meta > 0
        ORDER BY dir.nome, reg.nome, ag.nome, est.nome, m.data_meta";
        
        $hierarchyResult = $conn->executeQuery($hierarchySql, $hierarchyParams);
        
        // Armazenar dados por nível hierárquico INDIVIDUAL
        // Cada nível mostra a meta agregada das pessoas que pertencem a ele
        $tempData = [
            'regionais' => [], // Regionais individuais - meta agregada de todas as pessoas da regional
            'agencias' => [], // Agências individuais - meta agregada de todas as pessoas da agência
            'gerentesGestao' => [], // Gerentes de gestão individuais - meta própria
            'gerentes' => [] // Gerentes individuais - meta própria
        ];
        
        $firstDiretoriaNome = null;
        $regionaisInfo = []; // Armazenar informações das regionais (id, nome)
        $agenciasInfo = []; // Armazenar informações das agências (id, nome)
        $gerentesGestaoInfo = []; // Armazenar informações dos gerentes de gestão (id, nome)
        $gerentesInfo = []; // Armazenar informações dos gerentes (id, nome)
        
        while ($hRow = $hierarchyResult->fetchAssociative()) {
            $diretoriaId = $hRow['diretoria_id'] ?? '';
            $diretoriaNome = $hRow['diretoria_nome'] ?? '';
            $regionalId = $hRow['regional_id'] ?? '';
            $regionalNome = $hRow['regional_nome'] ?? '';
            $agenciaId = $hRow['agencia_id'] ?? '';
            $agenciaNome = $hRow['agencia_nome'] ?? '';
            $gerenteId = $hRow['gerente_id'] ?? '';
            $gerenteNome = $hRow['gerente_nome'] ?? '';
            $tipoGerente = $hRow['tipo_gerente'] ?? '';
            $mes = $hRow['mes'] ?? '';
            $meta = (float)($hRow['meta'] ?? 0);
            
            if ($diretoriaNome && !$firstDiretoriaNome) {
                $firstDiretoriaNome = $diretoriaNome;
            }
            
            // Armazenar meta por REGIONAL (agregada de todas as pessoas da regional)
            if ($regionalId && $regionalNome) {
                $key = "REG_{$regionalId}|{$mes}";
                if (!isset($tempData['regionais'][$key])) {
                    $tempData['regionais'][$key] = 0;
                }
                $tempData['regionais'][$key] += $meta;
                
                // Armazenar informações da regional
                if (!isset($regionaisInfo[$regionalId])) {
                    $regionaisInfo[$regionalId] = [
                        'id' => $regionalId,
                        'nome' => $regionalNome,
                        'diretoria_id' => $diretoriaId,
                        'diretoria_nome' => $diretoriaNome
                    ];
                }
            }
            
            // Armazenar meta por AGÊNCIA (agregada de todas as pessoas da agência)
            if ($agenciaId && $agenciaNome) {
                $key = "AG_{$agenciaId}|{$mes}";
                if (!isset($tempData['agencias'][$key])) {
                    $tempData['agencias'][$key] = 0;
                }
                $tempData['agencias'][$key] += $meta;
                
                // Armazenar informações da agência
                if (!isset($agenciasInfo[$agenciaId])) {
                    $agenciasInfo[$agenciaId] = [
                        'id' => $agenciaId,
                        'nome' => $agenciaNome,
                        'diretoria_id' => $diretoriaId,
                        'diretoria_nome' => $diretoriaNome,
                        'regional_id' => $regionalId,
                        'regional_nome' => $regionalNome
                    ];
                }
            }
            
            // Armazenar gerentes de gestão INDIVIDUAIS (meta própria)
            if ($tipoGerente === 'gerenteGestao' && $gerenteId) {
                $key = "GG_{$gerenteId}|{$mes}";
                if (!isset($tempData['gerentesGestao'][$key])) {
                    $tempData['gerentesGestao'][$key] = 0;
                }
                $tempData['gerentesGestao'][$key] += $meta;
                
                // Armazenar informações do gerente de gestão
                if (!isset($gerentesGestaoInfo[$gerenteId])) {
                    $gerentesGestaoInfo[$gerenteId] = [
                        'id' => $gerenteId,
                        'nome' => $gerenteNome,
                        'diretoria_id' => $diretoriaId,
                        'diretoria_nome' => $diretoriaNome,
                        'regional_id' => $regionalId,
                        'regional_nome' => $regionalNome,
                        'agencia_id' => $agenciaId,
                        'agencia_nome' => $agenciaNome
                    ];
                }
            }
            
            // Armazenar gerentes INDIVIDUAIS (meta própria)
            if ($tipoGerente === 'gerente' && $gerenteId) {
                $key = "G_{$gerenteId}|{$mes}";
                if (!isset($tempData['gerentes'][$key])) {
                    $tempData['gerentes'][$key] = 0;
                }
                $tempData['gerentes'][$key] += $meta;
                
                // Armazenar informações do gerente
                if (!isset($gerentesInfo[$gerenteId])) {
                    $gerentesInfo[$gerenteId] = [
                        'id' => $gerenteId,
                        'nome' => $gerenteNome,
                        'diretoria_id' => $diretoriaId,
                        'diretoria_nome' => $diretoriaNome,
                        'regional_id' => $regionalId,
                        'regional_nome' => $regionalNome,
                        'agencia_id' => $agenciaId,
                        'agencia_nome' => $agenciaNome
                    ];
                }
            }
        }
        $hierarchyResult->free();
        
        // Determinar quais níveis hierárquicos devem ser exibidos baseado nos filtros
        $gerente = $filters ? $filters->getGerente() : null;
        $gerenteGestao = $filters ? $filters->getGerenteGestao() : null;
        $agencia = $filters ? $filters->getAgencia() : null;
        $regional = $filters ? $filters->getRegional() : null;
        $diretoria = $filters ? $filters->getDiretoria() : null;
        $segmento = $filters ? $filters->getSegmento() : null;
        
        // Determinar o nível mais específico filtrado
        $filteredLevel = null;
        if ($gerente !== null && $gerente !== '') {
            $filteredLevel = 'gerente';
        } elseif ($gerenteGestao !== null && $gerenteGestao !== '') {
            $filteredLevel = 'gerenteGestao';
        } elseif ($agencia !== null && $agencia !== '') {
            $filteredLevel = 'agencia';
        } elseif ($regional !== null && $regional !== '') {
            $filteredLevel = 'regional';
        } elseif ($diretoria !== null && $diretoria !== '') {
            $filteredLevel = 'diretoria';
        } elseif ($segmento !== null && $segmento !== '') {
            $filteredLevel = 'segmento';
        }
        
        // Criar unidades INDIVIDUAIS baseado no nível filtrado
        // Cada nível mostra apenas sua própria meta, não a soma dos níveis inferiores
        // Se gerente está filtrado, não mostra hierarquia (apenas dados dele)
        // Se gerenteGestao está filtrado, mostra apenas gerentes abaixo dele
        // Se agencia está filtrada, mostra gerentesGestao e gerentes abaixo
        // Se regional está filtrada, mostra gerentesGestao e gerentes abaixo
        // Se diretoria está filtrada, mostra gerentesGestao e gerentes abaixo
        // Se segmento está filtrado ou nenhum filtro, mostra gerentesGestao e gerentes
        
        if ($filteredLevel !== 'gerente') {
            // Criar agregados primeiro (sempre mostrar os agregados acima do nível filtrado)
            // Agregar por diretoria (soma de todas as regionais - representa a diretoria completa)
            if (!empty($tempData['regionais'])) {
                $hierarchyUnits['DIR_ALL'] = [
                    'value' => 'DIR_ALL',
                    'label' => $firstDiretoriaNome ?? 'D.R. VAREJO DIGITAL'
                ];
                // Agregar todas as regionais para criar DIR_ALL
                foreach ($tempData['regionais'] as $key => $meta) {
                    $parts = explode('|', $key);
                    if (count($parts) === 2) {
                        $mesKey = $parts[1];
                        $keyDir = "DIR_ALL|{$mesKey}";
                        if (!isset($hierarchyDataMensal[$keyDir])) {
                            $hierarchyDataMensal[$keyDir] = ['real' => 0, 'meta' => 0];
                        }
                        $hierarchyDataMensal[$keyDir]['meta'] += $meta;
                    }
                }
            }
            
            // Agregar por todas as regionais (sempre mostrar)
            if (!empty($tempData['regionais'])) {
                $hierarchyUnits['REG_ALL'] = [
                    'value' => 'REG_ALL',
                    'label' => 'Todas as Regionais'
                ];
                // Agregar todas as regionais
                foreach ($tempData['regionais'] as $key => $meta) {
                    $parts = explode('|', $key);
                    if (count($parts) === 2) {
                        $mesKey = $parts[1];
                        $keyReg = "REG_ALL|{$mesKey}";
                        if (!isset($hierarchyDataMensal[$keyReg])) {
                            $hierarchyDataMensal[$keyReg] = ['real' => 0, 'meta' => 0];
                        }
                        $hierarchyDataMensal[$keyReg]['meta'] += $meta;
                    }
                }
            }
            
            // Agregar por todas as agências (sempre mostrar, exceto quando filtrando por regional)
            if ($filteredLevel !== 'regional' && !empty($tempData['agencias'])) {
                $hierarchyUnits['AG_ALL'] = [
                    'value' => 'AG_ALL',
                    'label' => 'Todas as Agências'
                ];
                // Agregar todas as agências
                foreach ($tempData['agencias'] as $key => $meta) {
                    $parts = explode('|', $key);
                    if (count($parts) === 2) {
                        $mesKey = $parts[1];
                        $keyAg = "AG_ALL|{$mesKey}";
                        if (!isset($hierarchyDataMensal[$keyAg])) {
                            $hierarchyDataMensal[$keyAg] = ['real' => 0, 'meta' => 0];
                        }
                        $hierarchyDataMensal[$keyAg]['meta'] += $meta;
                    }
                }
            }
            
            // Agregar por todas as gerentes de gestão (sempre mostrar, exceto quando filtrando por regional)
            // Quando filtrar por agência, mostrar GG_ALL agregado da agência
            if ($filteredLevel !== 'regional' && !empty($tempData['gerentesGestao'])) {
                $hierarchyUnits['GG_ALL'] = [
                    'value' => 'GG_ALL',
                    'label' => 'Todas as Ger. de Gestão'
                ];
                // Agregar todas as gerentes de gestão
                // Se filtrar por agência, agregar apenas as da agência; caso contrário, todas
                foreach ($tempData['gerentesGestao'] as $key => $meta) {
                    $parts = explode('|', $key);
                    if (count($parts) === 2) {
                        $unitPart = $parts[0]; // "GG_{$ggId}"
                        $mesKey = $parts[1];
                        $keyGG = "GG_ALL|{$mesKey}";
                        if (!isset($hierarchyDataMensal[$keyGG])) {
                            $hierarchyDataMensal[$keyGG] = ['real' => 0, 'meta' => 0];
                        }
                        // Se filtrar por agência, verificar se o gerente de gestão pertence à agência
                        if ($filteredLevel === 'agencia' && $agencia) {
                            $ggId = str_replace("GG_", "", $unitPart);
                            $ggInfo = $gerentesGestaoInfo[$ggId] ?? null;
                            if ($ggInfo && (string)$ggInfo['agencia_id'] === (string)$agencia) {
                                $hierarchyDataMensal[$keyGG]['meta'] += $meta;
                            }
                        } else {
                            // Sem filtro de agência, agregar todas
                            $hierarchyDataMensal[$keyGG]['meta'] += $meta;
                        }
                    }
                }
            }
            
            // Agregar por todas as gerentes (sempre mostrar, exceto quando filtrando por regional, agência ou gerente de gestão)
            if ($filteredLevel !== 'regional' && $filteredLevel !== 'agencia' && $filteredLevel !== 'gerenteGestao' && !empty($tempData['gerentes'])) {
                $hierarchyUnits['G_ALL'] = [
                    'value' => 'G_ALL',
                    'label' => 'Todas as Gerentes'
                ];
                // Agregar todas as gerentes
                foreach ($tempData['gerentes'] as $key => $meta) {
                    $parts = explode('|', $key);
                    if (count($parts) === 2) {
                        $mesKey = $parts[1];
                        $keyG = "G_ALL|{$mesKey}";
                        if (!isset($hierarchyDataMensal[$keyG])) {
                            $hierarchyDataMensal[$keyG] = ['real' => 0, 'meta' => 0];
                        }
                        $hierarchyDataMensal[$keyG]['meta'] += $meta;
                    }
                }
            }
            
            // Agora adicionar o item filtrado (individual)
            if ($filteredLevel === 'regional' && $regional) {
                // Mostrar apenas a regional filtrada
                $regInfo = $regionaisInfo[$regional] ?? null;
                if ($regInfo) {
                    $unitKey = "REG_{$regional}";
                    $hierarchyUnits[$unitKey] = [
                        'value' => $unitKey,
                        'label' => $regInfo['nome']
                    ];
                    
                    foreach ($tempData['regionais'] as $key => $meta) {
                        if (strpos($key, "REG_{$regional}|") === 0) {
                            if (!isset($hierarchyDataMensal[$key])) {
                                $hierarchyDataMensal[$key] = ['real' => 0, 'meta' => 0];
                            }
                            $hierarchyDataMensal[$key]['meta'] += $meta;
                        }
                    }
                }
            } elseif ($filteredLevel === 'agencia' && $agencia) {
                // Mostrar apenas a agência filtrada
                $agInfo = $agenciasInfo[$agencia] ?? null;
                if ($agInfo) {
                    $unitKey = "AG_{$agencia}";
                    $hierarchyUnits[$unitKey] = [
                        'value' => $unitKey,
                        'label' => $agInfo['nome']
                    ];
                    
                    foreach ($tempData['agencias'] as $key => $meta) {
                        if (strpos($key, "AG_{$agencia}|") === 0) {
                            if (!isset($hierarchyDataMensal[$key])) {
                                $hierarchyDataMensal[$key] = ['real' => 0, 'meta' => 0];
                            }
                            $hierarchyDataMensal[$key]['meta'] += $meta;
                        }
                    }
                }
            } elseif ($filteredLevel === 'gerenteGestao' && $gerenteGestao) {
                // Quando filtrar por gerente de gestão, GG_ALL já foi criado acima
                // Agora mostrar apenas o gerente de gestão filtrado
                foreach ($gerentesGestaoInfo as $ggId => $ggInfo) {
                    if ((string)$ggId === (string)$gerenteGestao) {
                        $unitKey = "GG_{$ggId}";
                        $hierarchyUnits[$unitKey] = [
                            'value' => $unitKey,
                            'label' => $ggInfo['nome']
                        ];
                        
                        foreach ($tempData['gerentesGestao'] as $key => $meta) {
                            if (strpos($key, "GG_{$ggId}|") === 0) {
                                if (!isset($hierarchyDataMensal[$key])) {
                                    $hierarchyDataMensal[$key] = ['real' => 0, 'meta' => 0];
                                }
                                $hierarchyDataMensal[$key]['meta'] += $meta;
                            }
                        }
                        break;
                    }
                }
            }
        }
        
        // Para o modo de metas, usar apenas as unidades da hierarquia (não mesclar com units de regionais)
        // Ordenar unidades: primeiro agregados (DIR_ALL, REG_ALL, etc.), depois itens individuais
        $orderedHierarchyUnits = [];
        
        // Primeiro adicionar agregados na ordem hierárquica
        $order = ['DIR_ALL', 'REG_ALL', 'AG_ALL', 'GG_ALL', 'G_ALL'];
        foreach ($order as $unitKey) {
            if (isset($hierarchyUnits[$unitKey])) {
                $orderedHierarchyUnits[] = $hierarchyUnits[$unitKey];
            }
        }
        
        // Depois adicionar itens individuais (se houver filtro)
        // Regionais individuais
        $regUnits = [];
        foreach ($hierarchyUnits as $unitKey => $unit) {
            if (strpos($unitKey, 'REG_') === 0 && $unitKey !== 'REG_ALL') {
                $regUnits[$unitKey] = $unit;
            }
        }
        if (!empty($regUnits)) {
            uasort($regUnits, function($a, $b) {
                return strcmp($a['label'], $b['label']);
            });
            $orderedHierarchyUnits = array_merge($orderedHierarchyUnits, array_values($regUnits));
        }
        
        // Agências individuais
        $agUnits = [];
        foreach ($hierarchyUnits as $unitKey => $unit) {
            if (strpos($unitKey, 'AG_') === 0 && $unitKey !== 'AG_ALL') {
                $agUnits[$unitKey] = $unit;
            }
        }
        if (!empty($agUnits)) {
            uasort($agUnits, function($a, $b) {
                return strcmp($a['label'], $b['label']);
            });
            $orderedHierarchyUnits = array_merge($orderedHierarchyUnits, array_values($agUnits));
        }
        
        // Gerentes de gestão individuais
        $ggUnits = [];
        foreach ($hierarchyUnits as $unitKey => $unit) {
            if (strpos($unitKey, 'GG_') === 0 && $unitKey !== 'GG_ALL') {
                $ggUnits[$unitKey] = $unit;
            }
        }
        if (!empty($ggUnits)) {
            uasort($ggUnits, function($a, $b) {
                return strcmp($a['label'], $b['label']);
            });
            $orderedHierarchyUnits = array_merge($orderedHierarchyUnits, array_values($ggUnits));
        }
        
        // Gerentes individuais
        $gUnits = [];
        foreach ($hierarchyUnits as $unitKey => $unit) {
            if (strpos($unitKey, 'G_') === 0 && strpos($unitKey, 'GG_') !== 0 && $unitKey !== 'G_ALL') {
                $gUnits[$unitKey] = $unit;
            }
        }
        if (!empty($gUnits)) {
            uasort($gUnits, function($a, $b) {
                return strcmp($a['label'], $b['label']);
            });
            $orderedHierarchyUnits = array_merge($orderedHierarchyUnits, array_values($gUnits));
        }
        
        // Sempre mesclar units normais com units agregadas da hierarquia
        // O frontend vai filtrar qual usar baseado no modo (seções ou metas)
        $allUnits = array_merge($units, $orderedHierarchyUnits);
        
        // Mesclar dados mensais da hierarquia
        // Para o modo de metas, os dados são agregados por unit e mês (sem seções)
        // Usar uma seção especial "META" para identificar dados agregados
        $metaSectionId = 'META';
        if (!isset($sectionsFamilia[$metaSectionId])) {
            $sectionsFamilia[$metaSectionId] = [
                'id' => $metaSectionId,
                'label' => 'Meta Total'
            ];
        }
        
        foreach ($hierarchyDataMensal as $key => $data) {
            $parts = explode('|', $key);
            if (count($parts) === 2) {
                $unitKey = $parts[0];
                $mesKey = $parts[1];
                
                // Criar chave com seção especial META
                $keyMetaMensal = "{$unitKey}|{$metaSectionId}|{$mesKey}";
                if (!isset($dataFamiliaMensal[$keyMetaMensal])) {
                    $dataFamiliaMensal[$keyMetaMensal] = ['real' => 0, 'meta' => 0];
                }
                $dataFamiliaMensal[$keyMetaMensal]['meta'] += $data['meta'];
            }
        }

        return [
            'units' => array_values($allUnits),
            'sectionsFamilia' => array_values($sectionsFamilia),
            'sectionsIndicador' => array_values($sectionsIndicador),
            'dataFamilia' => $dataFamilia,
            'dataIndicador' => $dataIndicador,
            'dataFamiliaMensal' => $dataFamiliaMensal,
            'dataIndicadorMensal' => $dataIndicadorMensal,
            'months' => $months
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

                if ($gerente !== null && $gerente !== '') {
                        $gerenteFuncional = $this->getFuncionalFromIdOrFuncional($gerente, Cargo::GERENTE);
            if ($gerenteFuncional) {
                $whereClause .= " AND est.funcional = :gerenteFuncional";
                $params['gerenteFuncional'] = $gerenteFuncional;
            }
        } elseif ($gerenteGestao !== null && $gerenteGestao !== '') {
                        $gerenteGestaoFuncional = $this->getFuncionalFromIdOrFuncional($gerenteGestao, Cargo::GERENTE_GESTAO);
            if ($gerenteGestaoFuncional) {
                $whereClause .= " AND EXISTS (
                    SELECT 1 FROM {$estruturaTable} AS ggestao 
                    WHERE ggestao.funcional = :gerenteGestaoFuncional
                    AND ggestao.cargo_id = :cargoGerenteGestao
                    AND ggestao.segmento_id = est.segmento_id
                    AND ggestao.diretoria_id = est.diretoria_id
                    AND ggestao.regional_id = est.regional_id
                    AND ggestao.agencia_id = est.agencia_id
                )";
                $params['gerenteGestaoFuncional'] = $gerenteGestaoFuncional;
                $params['cargoGerenteGestao'] = Cargo::GERENTE_GESTAO;
            }
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

    
    private function getFuncionalFromIdOrFuncional($idOrFuncional, int $cargoId): ?string
    {
        if ($idOrFuncional === null || $idOrFuncional === '') {
            return null;
        }

                if (!is_numeric($idOrFuncional)) {
            return (string)$idOrFuncional;
        }

                $dEstruturaTable = $this->getTableName(DEstrutura::class);
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = "SELECT funcional FROM {$dEstruturaTable} 
                WHERE id = :id AND cargo_id = :cargoId 
                LIMIT 1";
        
        $result = $conn->executeQuery($sql, [
            'id' => (int)$idOrFuncional,
            'cargoId' => $cargoId
        ]);
        
        $row = $result->fetchAssociative();
        $result->free();
        
        return $row ? ($row['funcional'] ?? null) : null;
    }
}

