<?php

namespace App\Repository\Pobj;

use App\Domain\DTO\FilterDTO;
use App\Domain\Enum\Cargo;
use App\Entity\Pobj\FMeta;
use App\Entity\Pobj\DEstrutura;
use App\Entity\Pobj\Regional;
use App\Entity\Pobj\Diretoria;
use App\Entity\Pobj\Agencia;
use App\Repository\Pobj\Helper\ExecFilterBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExecHeatmapMetaRepository extends ServiceEntityRepository
{
    private $filterBuilder;

    public function __construct(ManagerRegistry $registry, ExecFilterBuilder $filterBuilder)
    {
        parent::__construct($registry, FMeta::class);
        $this->filterBuilder = $filterBuilder;
    }

    public function findHeatmapMeta(?FilterDTO $filters = null): array
    {
        $fMetaTable = $this->getTableName(FMeta::class);
        $dEstruturaTable = $this->getTableName(DEstrutura::class);
        $regionalTable = $this->getTableName(Regional::class);
        $diretoriaTable = $this->getTableName(Diretoria::class);
        $agenciaTable = $this->getTableName(Agencia::class);

        $params = [];
        $whereClause = $this->filterBuilder->buildWhereClause($filters, $params, false);

        $months = $this->calculateLast12Months();

        $hierarchyData = $this->fetchHierarchyData(
            $fMetaTable,
            $dEstruturaTable,
            $regionalTable,
            $diretoriaTable,
            $agenciaTable,
            $whereClause,
            $params,
            $months,
            $filters
        );

        return [
            'units' => $hierarchyData['units'],
            'dataFamiliaMensal' => $hierarchyData['dataFamiliaMensal'],
            'months' => $months
        ];
    }

    private function calculateLast12Months(): array
    {
        $today = new \DateTime();
        $currentMonth = clone $today;
        $currentMonth->modify('first day of this month');
        $currentMonth->setTime(0, 0, 0);
        
        $startMonth = clone $currentMonth;
        $startMonth->modify('-11 months');
        
        $months = [];
        $date = clone $startMonth;
        while ($date <= $currentMonth) {
            $months[] = [
                'key' => $date->format('Y-m'),
                'label' => $this->formatMonthPT($date),
                'year' => (int)$date->format('Y'),
                'month' => (int)$date->format('m')
            ];
            $date->modify('+1 month');
        }

        return $months;
    }

    private function formatMonthPT(\DateTime $date): string
    {
        $meses = [
            1 => 'jan', 2 => 'fev', 3 => 'mar', 4 => 'abr',
            5 => 'mai', 6 => 'jun', 7 => 'jul', 8 => 'ago',
            9 => 'set', 10 => 'out', 11 => 'nov', 12 => 'dez'
        ];
        $mes = (int)$date->format('m');
        $ano = $date->format('Y');
        return $meses[$mes] . ' de ' . $ano;
    }

    private function fetchHierarchyData(
        string $fMetaTable,
        string $dEstruturaTable,
        string $regionalTable,
        string $diretoriaTable,
        string $agenciaTable,
        string $whereClause,
        array &$params,
        array $months,
        ?FilterDTO $filters
    ): array {
        $conn = $this->getEntityManager()->getConnection();

        // Se um gerente está filtrado, expandir o filtro para incluir toda a hierarquia acima
        $expandedWhereClause = $whereClause;
        if ($filters && $filters->getGerente()) {
            $gerenteId = $filters->getGerente();
            $gerenteFuncional = $this->filterBuilder->getFuncionalFromIdOrFuncional($gerenteId, Cargo::GERENTE);
            
            if ($gerenteFuncional) {
                // Buscar informações do gerente para expandir o filtro
                $gerenteInfoSql = "SELECT segmento_id, diretoria_id, regional_id, agencia_id 
                                   FROM {$dEstruturaTable} 
                                   WHERE funcional = :gerenteFuncional 
                                   AND cargo_id = :cargoGerente 
                                   LIMIT 1";
                $gerenteInfoResult = $conn->executeQuery($gerenteInfoSql, [
                    'gerenteFuncional' => $gerenteFuncional,
                    'cargoGerente' => Cargo::GERENTE
                ]);
                $gerenteInfo = $gerenteInfoResult->fetchAssociative();
                $gerenteInfoResult->free();
                
                if ($gerenteInfo) {
                    // Expandir o filtro para incluir todos na mesma hierarquia
                    $expandedWhereClause = " AND est.segmento_id = :segmentoId
                                             AND est.diretoria_id = :diretoriaId
                                             AND est.regional_id = :regionalId
                                             AND est.agencia_id = :agenciaId";
                    $params['segmentoId'] = $gerenteInfo['segmento_id'];
                    $params['diretoriaId'] = $gerenteInfo['diretoria_id'];
                    $params['regionalId'] = $gerenteInfo['regional_id'];
                    $params['agenciaId'] = $gerenteInfo['agencia_id'];
                }
            }
        }

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
        WHERE 1=1 {$expandedWhereClause}
            AND m.meta_mensal IS NOT NULL
            AND m.meta_mensal > 0";

        $hierarchyParams = array_merge($params, [
            'cargoGerente' => Cargo::GERENTE,
            'cargoGerenteGestao' => Cargo::GERENTE_GESTAO,
            'tipoGerente' => 'gerente',
            'tipoGerenteGestao' => 'gerenteGestao'
        ]);

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

        $tempData = $this->processHierarchyData($hierarchyResult);
        $hierarchyResult->free();

        return $this->buildHierarchyUnits($tempData, $filters);
    }

    private function processHierarchyData($result): array
    {
        $tempData = [
            'regionais' => [],
            'agencias' => [],
            'gerentesGestao' => [],
            'gerentes' => []
        ];

        $firstDiretoriaNome = null;
        $regionaisInfo = [];
        $agenciasInfo = [];
        $gerentesGestaoInfo = [];
        $gerentesInfo = [];

        while ($hRow = $result->fetchAssociative()) {
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

            if ($regionalId && $regionalNome) {
                $key = "REG_{$regionalId}|{$mes}";
                if (!isset($tempData['regionais'][$key])) {
                    $tempData['regionais'][$key] = 0;
                }
                $tempData['regionais'][$key] += $meta;

                if (!isset($regionaisInfo[$regionalId])) {
                    $regionaisInfo[$regionalId] = [
                        'id' => $regionalId,
                        'nome' => $regionalNome,
                        'diretoria_id' => $diretoriaId,
                        'diretoria_nome' => $diretoriaNome
                    ];
                }
            }

            if ($agenciaId && $agenciaNome) {
                $key = "AG_{$agenciaId}|{$mes}";
                if (!isset($tempData['agencias'][$key])) {
                    $tempData['agencias'][$key] = 0;
                }
                $tempData['agencias'][$key] += $meta;

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

            if ($tipoGerente === 'gerenteGestao' && $gerenteId) {
                $key = "GG_{$gerenteId}|{$mes}";
                if (!isset($tempData['gerentesGestao'][$key])) {
                    $tempData['gerentesGestao'][$key] = 0;
                }
                $tempData['gerentesGestao'][$key] += $meta;

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

            if ($tipoGerente === 'gerente' && $gerenteId) {
                $key = "G_{$gerenteId}|{$mes}";
                if (!isset($tempData['gerentes'][$key])) {
                    $tempData['gerentes'][$key] = 0;
                }
                $tempData['gerentes'][$key] += $meta;

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

        return [
            'tempData' => $tempData,
            'firstDiretoriaNome' => $firstDiretoriaNome,
            'regionaisInfo' => $regionaisInfo,
            'agenciasInfo' => $agenciasInfo,
            'gerentesGestaoInfo' => $gerentesGestaoInfo,
            'gerentesInfo' => $gerentesInfo
        ];
    }

    private function buildHierarchyUnits(array $processedData, ?FilterDTO $filters): array
    {
        $tempData = $processedData['tempData'];
        $firstDiretoriaNome = $processedData['firstDiretoriaNome'];
        $regionaisInfo = $processedData['regionaisInfo'];
        $agenciasInfo = $processedData['agenciasInfo'];
        $gerentesGestaoInfo = $processedData['gerentesGestaoInfo'];
        $gerentesInfo = $processedData['gerentesInfo'];

        $gerente = $filters ? $filters->getGerente() : null;
        $gerenteGestao = $filters ? $filters->getGerenteGestao() : null;
        $agencia = $filters ? $filters->getAgencia() : null;
        $regional = $filters ? $filters->getRegional() : null;

        $filteredLevel = $this->determineFilteredLevel($filters);

        $hierarchyUnits = [];
        $hierarchyDataMensal = [];

        // Se um gerente está filtrado, mostrar apenas as unidades individuais da hierarquia acima dele + o gerente
        // NÃO mostrar os agregados "TODOS"
        if ($filteredLevel === 'gerente' && $gerente) {
            $gerenteInfo = $gerentesInfo[$gerente] ?? null;
            if ($gerenteInfo) {
                $agenciaId = $gerenteInfo['agencia_id'] ?? null;
                $regionalId = $gerenteInfo['regional_id'] ?? null;
                $diretoriaId = $gerenteInfo['diretoria_id'] ?? null;
                
                // Construir apenas unidades individuais da hierarquia acima (sem agregados)
                // Regional individual
                if ($regionalId) {
                    $regInfo = $regionaisInfo[$regionalId] ?? null;
                    if ($regInfo) {
                        $unitKey = "REG_{$regionalId}";
                        $hierarchyUnits[$unitKey] = [
                            'value' => $unitKey,
                            'label' => $regInfo['nome']
                        ];
                        foreach ($tempData['regionais'] as $key => $meta) {
                            if (strpos($key, "REG_{$regionalId}|") === 0) {
                                if (!isset($hierarchyDataMensal[$key])) {
                                    $hierarchyDataMensal[$key] = ['real' => 0, 'meta' => 0];
                                }
                                $hierarchyDataMensal[$key]['meta'] += $meta;
                            }
                        }
                    }
                }
                
                // Agência individual
                if ($agenciaId) {
                    $agInfo = $agenciasInfo[$agenciaId] ?? null;
                    if ($agInfo) {
                        $unitKey = "AG_{$agenciaId}";
                        $hierarchyUnits[$unitKey] = [
                            'value' => $unitKey,
                            'label' => $agInfo['nome']
                        ];
                        foreach ($tempData['agencias'] as $key => $meta) {
                            if (strpos($key, "AG_{$agenciaId}|") === 0) {
                                if (!isset($hierarchyDataMensal[$key])) {
                                    $hierarchyDataMensal[$key] = ['real' => 0, 'meta' => 0];
                                }
                                $hierarchyDataMensal[$key]['meta'] += $meta;
                            }
                        }
                    }
                }
                
                // Gerente de gestão individual responsável por este gerente
                // Ele deve estar na mesma agência, regional, diretoria e segmento
                foreach ($gerentesGestaoInfo as $ggId => $ggInfo) {
                    if ((string)$ggInfo['agencia_id'] === (string)$agenciaId &&
                        (string)$ggInfo['regional_id'] === (string)$regionalId &&
                        (string)$ggInfo['diretoria_id'] === (string)$diretoriaId) {
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
                        break; // Apenas o primeiro gerente de gestão encontrado
                    }
                }
                
                // Adicionar o gerente individual
                $unitKey = "G_{$gerente}";
                $hierarchyUnits[$unitKey] = [
                    'value' => $unitKey,
                    'label' => $gerenteInfo['nome']
                ];
                foreach ($tempData['gerentes'] as $key => $meta) {
                    if (strpos($key, "G_{$gerente}|") === 0) {
                        if (!isset($hierarchyDataMensal[$key])) {
                            $hierarchyDataMensal[$key] = ['real' => 0, 'meta' => 0];
                        }
                        $hierarchyDataMensal[$key]['meta'] += $meta;
                    }
                }
            }
        } else {
            $this->buildAggregatedUnits($tempData, $hierarchyUnits, $hierarchyDataMensal, $filteredLevel, $agencia, $gerentesGestaoInfo, $firstDiretoriaNome);
            $this->buildIndividualUnits($filteredLevel, $regional, $agencia, $gerenteGestao, $regionaisInfo, $agenciasInfo, $gerentesGestaoInfo, $tempData, $hierarchyUnits, $hierarchyDataMensal);
        }

        $orderedHierarchyUnits = $this->orderHierarchyUnits($hierarchyUnits);
        
        // Mesclar dados mensais com seção META
        $dataFamiliaMensal = [];
        $metaSectionId = 'META';
        foreach ($hierarchyDataMensal as $key => $data) {
            $parts = explode('|', $key);
            if (count($parts) === 2) {
                $unitKey = $parts[0];
                $mesKey = $parts[1];
                $keyMetaMensal = "{$unitKey}|{$metaSectionId}|{$mesKey}";
                if (!isset($dataFamiliaMensal[$keyMetaMensal])) {
                    $dataFamiliaMensal[$keyMetaMensal] = ['real' => 0, 'meta' => 0];
                }
                $dataFamiliaMensal[$keyMetaMensal]['meta'] += $data['meta'];
            }
        }

        return [
            'units' => $orderedHierarchyUnits,
            'dataFamiliaMensal' => $dataFamiliaMensal
        ];
    }

    private function determineFilteredLevel(?FilterDTO $filters): ?string
    {
        if (!$filters) {
            return null;
        }

        if ($filters->getGerente()) {
            return 'gerente';
        } elseif ($filters->getGerenteGestao()) {
            return 'gerenteGestao';
        } elseif ($filters->getAgencia()) {
            return 'agencia';
        } elseif ($filters->getRegional()) {
            return 'regional';
        } elseif ($filters->getDiretoria()) {
            return 'diretoria';
        } elseif ($filters->getSegmento()) {
            return 'segmento';
        }

        return null;
    }

    private function buildAggregatedUnits(
        array $tempData,
        array &$hierarchyUnits,
        array &$hierarchyDataMensal,
        ?string $filteredLevel,
        ?string $agencia,
        array $gerentesGestaoInfo,
        ?string $firstDiretoriaNome
    ): void {
        // DIR_ALL
        if (!empty($tempData['regionais'])) {
            $hierarchyUnits['DIR_ALL'] = [
                'value' => 'DIR_ALL',
                'label' => $firstDiretoriaNome ?? 'D.R. VAREJO DIGITAL'
            ];
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

        // REG_ALL
        if (!empty($tempData['regionais'])) {
            $hierarchyUnits['REG_ALL'] = [
                'value' => 'REG_ALL',
                'label' => 'Todas as Regionais'
            ];
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

        // AG_ALL
        if ($filteredLevel !== 'regional' && !empty($tempData['agencias'])) {
            $hierarchyUnits['AG_ALL'] = [
                'value' => 'AG_ALL',
                'label' => 'Todas as Agências'
            ];
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

        // GG_ALL
        if ($filteredLevel !== 'regional' && !empty($tempData['gerentesGestao'])) {
            $hierarchyUnits['GG_ALL'] = [
                'value' => 'GG_ALL',
                'label' => 'Todas as Ger. de Gestão'
            ];
            foreach ($tempData['gerentesGestao'] as $key => $meta) {
                $parts = explode('|', $key);
                if (count($parts) === 2) {
                    $unitPart = $parts[0];
                    $mesKey = $parts[1];
                    $keyGG = "GG_ALL|{$mesKey}";
                    if (!isset($hierarchyDataMensal[$keyGG])) {
                        $hierarchyDataMensal[$keyGG] = ['real' => 0, 'meta' => 0];
                    }
                    if ($filteredLevel === 'agencia' && $agencia) {
                        $ggId = str_replace("GG_", "", $unitPart);
                        $ggInfo = $gerentesGestaoInfo[$ggId] ?? null;
                        if ($ggInfo && (string)$ggInfo['agencia_id'] === (string)$agencia) {
                            $hierarchyDataMensal[$keyGG]['meta'] += $meta;
                        }
                    } else {
                        $hierarchyDataMensal[$keyGG]['meta'] += $meta;
                    }
                }
            }
        }

        // G_ALL
        if ($filteredLevel !== 'regional' && $filteredLevel !== 'agencia' && $filteredLevel !== 'gerenteGestao' && !empty($tempData['gerentes'])) {
            $hierarchyUnits['G_ALL'] = [
                'value' => 'G_ALL',
                'label' => 'Todas as Gerentes'
            ];
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
    }

    private function buildIndividualUnits(
        ?string $filteredLevel,
        ?string $regional,
        ?string $agencia,
        ?string $gerenteGestao,
        array $regionaisInfo,
        array $agenciasInfo,
        array $gerentesGestaoInfo,
        array $tempData,
        array &$hierarchyUnits,
        array &$hierarchyDataMensal
    ): void {
        if ($filteredLevel === 'regional' && $regional) {
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

    private function orderHierarchyUnits(array $hierarchyUnits): array
    {
        $ordered = [];
        
        // Primeiro agregados
        $order = ['DIR_ALL', 'REG_ALL', 'AG_ALL', 'GG_ALL', 'G_ALL'];
        foreach ($order as $unitKey) {
            if (isset($hierarchyUnits[$unitKey])) {
                $ordered[] = $hierarchyUnits[$unitKey];
            }
        }
        
        // Depois individuais
        $regUnits = [];
        $agUnits = [];
        $ggUnits = [];
        $gUnits = [];
        
        foreach ($hierarchyUnits as $unitKey => $unit) {
            if (strpos($unitKey, 'REG_') === 0 && $unitKey !== 'REG_ALL') {
                $regUnits[$unitKey] = $unit;
            } elseif (strpos($unitKey, 'AG_') === 0 && $unitKey !== 'AG_ALL') {
                $agUnits[$unitKey] = $unit;
            } elseif (strpos($unitKey, 'GG_') === 0 && $unitKey !== 'GG_ALL') {
                $ggUnits[$unitKey] = $unit;
            } elseif (strpos($unitKey, 'G_') === 0 && strpos($unitKey, 'GG_') !== 0 && $unitKey !== 'G_ALL') {
                $gUnits[$unitKey] = $unit;
            }
        }
        
        $sortFn = function($a, $b) {
            return strcmp($a['label'], $b['label']);
        };
        
        uasort($regUnits, $sortFn);
        uasort($agUnits, $sortFn);
        uasort($ggUnits, $sortFn);
        uasort($gUnits, $sortFn);
        
        $ordered = array_merge($ordered, array_values($regUnits), array_values($agUnits), array_values($ggUnits), array_values($gUnits));
        
        return $ordered;
    }

    private function getTableName(string $entityClass): string
    {
        return $this->getEntityManager()
            ->getClassMetadata($entityClass)
            ->getTableName();
    }
}
