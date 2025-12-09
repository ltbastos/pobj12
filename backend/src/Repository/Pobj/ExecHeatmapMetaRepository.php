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

        // Sempre construir agregados primeiro (composição de hierarquia)
        $this->buildAggregatedUnits($tempData, $hierarchyUnits, $hierarchyDataMensal, $filteredLevel, $agencia, $gerentesGestaoInfo, $firstDiretoriaNome);
        
        // Se um gerente está filtrado, mostrar hierarquia acima + o gerente
        if ($filteredLevel === 'gerente' && $gerente) {
            $gerenteInfo = $gerentesInfo[$gerente] ?? null;
            if ($gerenteInfo) {
                $agenciaId = $gerenteInfo['agencia_id'] ?? null;
                $regionalId = $gerenteInfo['regional_id'] ?? null;
                $diretoriaId = $gerenteInfo['diretoria_id'] ?? null;
                
                // Construir unidades individuais da hierarquia acima (filtradas para a hierarquia dele)
                // Diretoria individual
                if ($diretoriaId) {
                    // Buscar nome da diretoria
                    $conn = $this->getEntityManager()->getConnection();
                    $diretoriaTable = $this->getTableName(Diretoria::class);
                    $dirSql = "SELECT nome FROM {$diretoriaTable} WHERE id = :diretoriaId LIMIT 1";
                    $dirResult = $conn->executeQuery($dirSql, ['diretoriaId' => $diretoriaId]);
                    $dirRow = $dirResult->fetchAssociative();
                    $dirResult->free();
                    
                    if ($dirRow) {
                        // Agregar todas as regionais dessa diretoria
                        foreach ($tempData['regionais'] as $key => $meta) {
                            $parts = explode('|', $key);
                            if (count($parts) === 2) {
                                $regKey = $parts[0];
                                $regId = str_replace('REG_', '', $regKey);
                                $regInfo = $regionaisInfo[$regId] ?? null;
                                if ($regInfo && (string)$regInfo['diretoria_id'] === (string)$diretoriaId) {
                                    $mesKey = $parts[1];
                                    $keyDir = "DIR_{$diretoriaId}|{$mesKey}";
                                    if (!isset($hierarchyDataMensal[$keyDir])) {
                                        $hierarchyDataMensal[$keyDir] = ['real' => 0, 'meta' => 0];
                                    }
                                    $hierarchyDataMensal[$keyDir]['meta'] += $meta;
                                }
                            }
                        }
                        $unitKey = "DIR_{$diretoriaId}";
                        $hierarchyUnits[$unitKey] = [
                            'value' => $unitKey,
                            'label' => $dirRow['nome']
                        ];
                    }
                }
                
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
                        break;
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
        } elseif ($filteredLevel === 'gerenteGestao' && $gerenteGestao) {
            // Se filtrar por gerente de gestão: mostrar hierarquia acima + ele + time abaixo (gerentes agregados)
            // O filtro pode vir como funcional ou ID, então precisamos buscar no banco se necessário
            $ggInfo = null;
            $ggIdNormalized = (string)$gerenteGestao;
            
            // Tentar encontrar no array primeiro
            foreach ($gerentesGestaoInfo as $ggId => $ggInfoTemp) {
                if ((string)$ggId === $ggIdNormalized) {
                    $ggInfo = $ggInfoTemp;
                    break;
                }
            }
            
            // Se não encontrou, pode ser que seja funcional - buscar no banco
            if (!$ggInfo) {
                $conn = $this->getEntityManager()->getConnection();
                $dEstruturaTable = $this->getTableName(DEstrutura::class);
                $diretoriaTable = $this->getTableName(Diretoria::class);
                $regionalTable = $this->getTableName(Regional::class);
                $agenciaTable = $this->getTableName(Agencia::class);
                
                // Tentar buscar por funcional ou ID
                $ggFuncional = $this->filterBuilder->getFuncionalFromIdOrFuncional($gerenteGestao, Cargo::GERENTE_GESTAO);
                if ($ggFuncional) {
                    $ggSql = "SELECT est.id, est.nome, est.segmento_id, est.diretoria_id, est.regional_id, est.agencia_id,
                             dir.nome AS diretoria_nome, reg.nome AS regional_nome, ag.nome AS agencia_nome
                             FROM {$dEstruturaTable} AS est
                             LEFT JOIN {$diretoriaTable} AS dir ON dir.id = est.diretoria_id
                             LEFT JOIN {$regionalTable} AS reg ON reg.id = est.regional_id
                             LEFT JOIN {$agenciaTable} AS ag ON ag.id = est.agencia_id
                             WHERE est.funcional = :ggFuncional 
                             AND est.cargo_id = :cargoGerenteGestao 
                             LIMIT 1";
                    $ggResult = $conn->executeQuery($ggSql, [
                        'ggFuncional' => $ggFuncional,
                        'cargoGerenteGestao' => Cargo::GERENTE_GESTAO
                    ]);
                    $ggRow = $ggResult->fetchAssociative();
                    $ggResult->free();
                    
                    if ($ggRow) {
                        $ggInfo = [
                            'id' => (string)$ggRow['id'],
                            'nome' => $ggRow['nome'],
                            'diretoria_id' => $ggRow['diretoria_id'],
                            'diretoria_nome' => $ggRow['diretoria_nome'],
                            'regional_id' => $ggRow['regional_id'],
                            'regional_nome' => $ggRow['regional_nome'],
                            'agencia_id' => $ggRow['agencia_id'],
                            'agencia_nome' => $ggRow['agencia_nome']
                        ];
                        // Adicionar ao array para uso posterior
                        $gerentesGestaoInfo[$ggRow['id']] = $ggInfo;
                    }
                } elseif (is_numeric($gerenteGestao)) {
                    // Tentar buscar por ID
                    $ggSql = "SELECT est.id, est.nome, est.segmento_id, est.diretoria_id, est.regional_id, est.agencia_id,
                             dir.nome AS diretoria_nome, reg.nome AS regional_nome, ag.nome AS agencia_nome
                             FROM {$dEstruturaTable} AS est
                             LEFT JOIN {$diretoriaTable} AS dir ON dir.id = est.diretoria_id
                             LEFT JOIN {$regionalTable} AS reg ON reg.id = est.regional_id
                             LEFT JOIN {$agenciaTable} AS ag ON ag.id = est.agencia_id
                             WHERE est.id = :ggId 
                             AND est.cargo_id = :cargoGerenteGestao 
                             LIMIT 1";
                    $ggResult = $conn->executeQuery($ggSql, [
                        'ggId' => (int)$gerenteGestao,
                        'cargoGerenteGestao' => Cargo::GERENTE_GESTAO
                    ]);
                    $ggRow = $ggResult->fetchAssociative();
                    $ggResult->free();
                    
                    if ($ggRow) {
                        $ggInfo = [
                            'id' => (string)$ggRow['id'],
                            'nome' => $ggRow['nome'],
                            'diretoria_id' => $ggRow['diretoria_id'],
                            'diretoria_nome' => $ggRow['diretoria_nome'],
                            'regional_id' => $ggRow['regional_id'],
                            'regional_nome' => $ggRow['regional_nome'],
                            'agencia_id' => $ggRow['agencia_id'],
                            'agencia_nome' => $ggRow['agencia_nome']
                        ];
                        // Adicionar ao array para uso posterior
                        $gerentesGestaoInfo[$ggRow['id']] = $ggInfo;
                    }
                }
            }
            
            if ($ggInfo) {
                $agenciaId = $ggInfo['agencia_id'] ?? null;
                $regionalId = $ggInfo['regional_id'] ?? null;
                $diretoriaId = $ggInfo['diretoria_id'] ?? null;
                
                // Hierarquia acima (diretoria, regional, agência)
                if ($diretoriaId) {
                    if (!isset($conn)) {
                        $conn = $this->getEntityManager()->getConnection();
                    }
                    $diretoriaTable = $this->getTableName(Diretoria::class);
                    $dirSql = "SELECT nome FROM {$diretoriaTable} WHERE id = :diretoriaId LIMIT 1";
                    $dirResult = $conn->executeQuery($dirSql, ['diretoriaId' => $diretoriaId]);
                    $dirRow = $dirResult->fetchAssociative();
                    $dirResult->free();
                    
                    if ($dirRow) {
                        $unitKey = "DIR_{$diretoriaId}";
                        $hierarchyUnits[$unitKey] = [
                            'value' => $unitKey,
                            'label' => $dirRow['nome']
                        ];
                        // Agregar dados da diretoria
                        foreach ($tempData['regionais'] as $key => $meta) {
                            $parts = explode('|', $key);
                            if (count($parts) === 2) {
                                $regKey = $parts[0];
                                $regId = str_replace('REG_', '', $regKey);
                                $regInfo = $regionaisInfo[$regId] ?? null;
                                if ($regInfo && (string)$regInfo['diretoria_id'] === (string)$diretoriaId) {
                                    $mesKey = $parts[1];
                                    $keyDir = "DIR_{$diretoriaId}|{$mesKey}";
                                    if (!isset($hierarchyDataMensal[$keyDir])) {
                                        $hierarchyDataMensal[$keyDir] = ['real' => 0, 'meta' => 0];
                                    }
                                    $hierarchyDataMensal[$keyDir]['meta'] += $meta;
                                }
                            }
                        }
                    }
                }
                
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
                
                // O gerente de gestão (usar o ID do $ggInfo, não do filtro)
                $ggId = $ggInfo['id'];
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
                
                // Time abaixo: agregar todos os gerentes deste gerente de gestão
                $unitKey = "G_ALL_GG_{$ggId}";
                $hierarchyUnits[$unitKey] = [
                    'value' => $unitKey,
                    'label' => 'Time do ' . $ggInfo['nome']
                ];
                // Agregar metas de todos os gerentes desta agência/regional/diretoria
                foreach ($tempData['gerentes'] as $key => $meta) {
                    $parts = explode('|', $key);
                    if (count($parts) === 2) {
                        $gKey = $parts[0];
                        $gerenteId = str_replace('G_', '', $gKey);
                        $gInfo = $gerentesInfo[$gerenteId] ?? null;
                        if ($gInfo && 
                            (string)$gInfo['agencia_id'] === (string)$agenciaId &&
                            (string)$gInfo['regional_id'] === (string)$regionalId &&
                            (string)$gInfo['diretoria_id'] === (string)$diretoriaId) {
                            $mesKey = $parts[1];
                            $keyTeam = "{$unitKey}|{$mesKey}";
                            if (!isset($hierarchyDataMensal[$keyTeam])) {
                                $hierarchyDataMensal[$keyTeam] = ['real' => 0, 'meta' => 0];
                            }
                            $hierarchyDataMensal[$keyTeam]['meta'] += $meta;
                        }
                    }
                }
            }
        } else {
            // Para outros níveis, construir unidades individuais normalmente
            $diretoria = $filters ? $filters->getDiretoria() : null;
            $this->buildIndividualUnits(
                $filteredLevel,
                $diretoria,
                $regional,
                $agencia,
                $gerenteGestao,
                $regionaisInfo,
                $agenciasInfo,
                $gerentesGestaoInfo,
                $gerentesInfo,
                $tempData,
                $hierarchyUnits,
                $hierarchyDataMensal
            );
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
                'label' => 'Todas Diretorias'
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
                'label' => 'Todas Regionais'
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
                'label' => 'Todas Agências'
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

        // GG_ALL - sempre mostrar quando houver dados de gerentes de gestão
        // (não mostrar quando filtrar por regional, pois regional não tem gerentes de gestão diretamente abaixo)
        // Mas mostrar sempre que houver dados, mesmo com outros filtros
        if (!empty($tempData['gerentesGestao'])) {
            $hierarchyUnits['GG_ALL'] = [
                'value' => 'GG_ALL',
                'label' => 'Todas Ger. de Gestão'
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
                    // Sempre agregar todos os gerentes de gestão para GG_ALL
                    // (a filtragem já foi feita na query, então todos os dados aqui são válidos)
                    $hierarchyDataMensal[$keyGG]['meta'] += $meta;
                }
            }
        }

        // G_ALL
        if ($filteredLevel !== 'regional' && $filteredLevel !== 'agencia' && $filteredLevel !== 'gerenteGestao' && !empty($tempData['gerentes'])) {
            $hierarchyUnits['G_ALL'] = [
                'value' => 'G_ALL',
                'label' => 'Todos Gerentes'
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
        ?string $diretoria,
        ?string $regional,
        ?string $agencia,
        ?string $gerenteGestao,
        array $regionaisInfo,
        array $agenciasInfo,
        array $gerentesGestaoInfo,
        array $gerentesInfo,
        array $tempData,
        array &$hierarchyUnits,
        array &$hierarchyDataMensal
    ): void {
        // Sem filtro: manter apenas agregados (já montados em buildAggregatedUnits)
        if ($filteredLevel === null) {
            return;
        }

        // Filtrou diretoria/segmento: apenas unidades dessa diretoria
        if (($filteredLevel === 'diretoria' || $filteredLevel === 'segmento') && empty($regional) && empty($agencia) && empty($gerenteGestao)) {
            foreach ($regionaisInfo as $regId => $regInfo) {
                if ($diretoria && (string)$regInfo['diretoria_id'] !== (string)$diretoria) {
                    continue;
                }
                $unitKey = "REG_{$regId}";
                if (!isset($hierarchyUnits[$unitKey])) {
                    $hierarchyUnits[$unitKey] = [
                        'value' => $unitKey,
                        'label' => $regInfo['nome']
                    ];
                }
                foreach ($tempData['regionais'] as $key => $meta) {
                    if (strpos($key, "REG_{$regId}|") === 0) {
                        if (!isset($hierarchyDataMensal[$key])) {
                            $hierarchyDataMensal[$key] = ['real' => 0, 'meta' => 0];
                        }
                        $hierarchyDataMensal[$key]['meta'] += $meta;
                    }
                }
            }

            foreach ($agenciasInfo as $agId => $agInfo) {
                if ($diretoria && (string)$agInfo['diretoria_id'] !== (string)$diretoria) {
                    continue;
                }
                $unitKey = "AG_{$agId}";
                if (!isset($hierarchyUnits[$unitKey])) {
                    $hierarchyUnits[$unitKey] = [
                        'value' => $unitKey,
                        'label' => $agInfo['nome']
                    ];
                }
                foreach ($tempData['agencias'] as $key => $meta) {
                    if (strpos($key, "AG_{$agId}|") === 0) {
                        if (!isset($hierarchyDataMensal[$key])) {
                            $hierarchyDataMensal[$key] = ['real' => 0, 'meta' => 0];
                        }
                        $hierarchyDataMensal[$key]['meta'] += $meta;
                    }
                }
            }

            foreach ($gerentesGestaoInfo as $ggId => $ggInfo) {
                if ($diretoria && (string)$ggInfo['diretoria_id'] !== (string)$diretoria) {
                    continue;
                }
                $unitKey = "GG_{$ggId}";
                if (!isset($hierarchyUnits[$unitKey])) {
                    $hierarchyUnits[$unitKey] = [
                        'value' => $unitKey,
                        'label' => $ggInfo['nome']
                    ];
                }
                foreach ($tempData['gerentesGestao'] as $key => $meta) {
                    if (strpos($key, "GG_{$ggId}|") === 0) {
                        if (!isset($hierarchyDataMensal[$key])) {
                            $hierarchyDataMensal[$key] = ['real' => 0, 'meta' => 0];
                        }
                        $hierarchyDataMensal[$key]['meta'] += $meta;
                    }
                }
            }

            foreach ($tempData['gerentes'] as $key => $meta) {
                [$gKey] = explode('|', $key);
                $gerenteId = str_replace('G_', '', $gKey);
                $gInfo = $gerentesInfo[$gerenteId] ?? null;
                if ($diretoria && $gInfo && (string)$gInfo['diretoria_id'] !== (string)$diretoria) {
                    continue;
                }
                if (!isset($hierarchyUnits[$gKey])) {
                    $hierarchyUnits[$gKey] = [
                        'value' => $gKey,
                        'label' => $gInfo['nome'] ?? $gerenteId
                    ];
                }
                if (!isset($hierarchyDataMensal[$key])) {
                    $hierarchyDataMensal[$key] = ['real' => 0, 'meta' => 0];
                }
                $hierarchyDataMensal[$key]['meta'] += $meta;
            }
            return;
        }

        if ($filteredLevel === 'regional' && $regional) {
            $regInfo = $regionaisInfo[$regional] ?? null;
            if ($regInfo) {
                $diretoriaId = $regInfo['diretoria_id'] ?? null;
                
                // Hierarquia acima (diretoria)
                if ($diretoriaId) {
                    if (!isset($conn)) {
                        $conn = $this->getEntityManager()->getConnection();
                    }
                    $diretoriaTable = $this->getTableName(Diretoria::class);
                    $dirSql = "SELECT nome FROM {$diretoriaTable} WHERE id = :diretoriaId LIMIT 1";
                    $dirResult = $conn->executeQuery($dirSql, ['diretoriaId' => $diretoriaId]);
                    $dirRow = $dirResult->fetchAssociative();
                    $dirResult->free();
                    
                    if ($dirRow) {
                        $unitKey = "DIR_{$diretoriaId}";
                        $hierarchyUnits[$unitKey] = [
                            'value' => $unitKey,
                            'label' => $dirRow['nome']
                        ];
                        // Agregar dados da diretoria
                        foreach ($tempData['regionais'] as $key => $meta) {
                            $parts = explode('|', $key);
                            if (count($parts) === 2) {
                                $regKey = $parts[0];
                                $regId = str_replace('REG_', '', $regKey);
                                $regInfoTemp = $regionaisInfo[$regId] ?? null;
                                if ($regInfoTemp && (string)$regInfoTemp['diretoria_id'] === (string)$diretoriaId) {
                                    $mesKey = $parts[1];
                                    $keyDir = "DIR_{$diretoriaId}|{$mesKey}";
                                    if (!isset($hierarchyDataMensal[$keyDir])) {
                                        $hierarchyDataMensal[$keyDir] = ['real' => 0, 'meta' => 0];
                                    }
                                    $hierarchyDataMensal[$keyDir]['meta'] += $meta;
                                }
                            }
                        }
                    }
                }
                
                // A regional
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
                
                // Time abaixo: agregar todas as agências desta regional
                $unitKey = "AG_ALL_REG_{$regional}";
                $hierarchyUnits[$unitKey] = [
                    'value' => $unitKey,
                    'label' => 'Todas Agências - ' . $regInfo['nome']
                ];
                foreach ($tempData['agencias'] as $key => $meta) {
                    $parts = explode('|', $key);
                    if (count($parts) === 2) {
                        $agKey = $parts[0];
                        $agId = str_replace('AG_', '', $agKey);
                        $agInfo = $agenciasInfo[$agId] ?? null;
                        if ($agInfo && (string)$agInfo['regional_id'] === (string)$regional) {
                            $mesKey = $parts[1];
                            $keyTeam = "{$unitKey}|{$mesKey}";
                            if (!isset($hierarchyDataMensal[$keyTeam])) {
                                $hierarchyDataMensal[$keyTeam] = ['real' => 0, 'meta' => 0];
                            }
                            $hierarchyDataMensal[$keyTeam]['meta'] += $meta;
                        }
                    }
                }
            }
        } elseif ($filteredLevel === 'agencia' && $agencia) {
            $agInfo = $agenciasInfo[$agencia] ?? null;
            if ($agInfo) {
                $regionalId = $agInfo['regional_id'] ?? null;
                $diretoriaId = $agInfo['diretoria_id'] ?? null;
                
                // Hierarquia acima
                if ($diretoriaId) {
                    if (!isset($conn)) {
                        $conn = $this->getEntityManager()->getConnection();
                    }
                    $diretoriaTable = $this->getTableName(Diretoria::class);
                    $dirSql = "SELECT nome FROM {$diretoriaTable} WHERE id = :diretoriaId LIMIT 1";
                    $dirResult = $conn->executeQuery($dirSql, ['diretoriaId' => $diretoriaId]);
                    $dirRow = $dirResult->fetchAssociative();
                    $dirResult->free();
                    
                    if ($dirRow) {
                        $unitKey = "DIR_{$diretoriaId}";
                        $hierarchyUnits[$unitKey] = [
                            'value' => $unitKey,
                            'label' => $dirRow['nome']
                        ];
                        foreach ($tempData['regionais'] as $key => $meta) {
                            $parts = explode('|', $key);
                            if (count($parts) === 2) {
                                $regKey = $parts[0];
                                $regId = str_replace('REG_', '', $regKey);
                                $regInfoTemp = $regionaisInfo[$regId] ?? null;
                                if ($regInfoTemp && (string)$regInfoTemp['diretoria_id'] === (string)$diretoriaId) {
                                    $mesKey = $parts[1];
                                    $keyDir = "DIR_{$diretoriaId}|{$mesKey}";
                                    if (!isset($hierarchyDataMensal[$keyDir])) {
                                        $hierarchyDataMensal[$keyDir] = ['real' => 0, 'meta' => 0];
                                    }
                                    $hierarchyDataMensal[$keyDir]['meta'] += $meta;
                                }
                            }
                        }
                    }
                }
                
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
                
                // A agência
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
                
                // Time abaixo: agregar todos os gerentes de gestão desta agência
                $unitKey = "GG_ALL_AG_{$agencia}";
                $hierarchyUnits[$unitKey] = [
                    'value' => $unitKey,
                    'label' => 'Time - ' . $agInfo['nome']
                ];
                foreach ($tempData['gerentesGestao'] as $key => $meta) {
                    $parts = explode('|', $key);
                    if (count($parts) === 2) {
                        $ggKey = $parts[0];
                        $ggId = str_replace('GG_', '', $ggKey);
                        $ggInfoTemp = $gerentesGestaoInfo[$ggId] ?? null;
                        if ($ggInfoTemp && (string)$ggInfoTemp['agencia_id'] === (string)$agencia) {
                            $mesKey = $parts[1];
                            $keyTeam = "{$unitKey}|{$mesKey}";
                            if (!isset($hierarchyDataMensal[$keyTeam])) {
                                $hierarchyDataMensal[$keyTeam] = ['real' => 0, 'meta' => 0];
                            }
                            $hierarchyDataMensal[$keyTeam]['meta'] += $meta;
                        }
                    }
                }
            }
        } elseif ($filteredLevel === 'gerenteGestao' && $gerenteGestao) {
            // Esta lógica já foi implementada acima no bloco principal
            foreach ($gerentesGestaoInfo as $ggId => $ggInfo) {
                if ((string)$ggId === (string)$gerenteGestao) {
                    // Já foi processado acima, apenas garantir que está presente
                    break;
                }
            }
        }
    }

    private function orderHierarchyUnits(array $hierarchyUnits): array
    {
        $ordered = [];
        
        // Primeiro agregados (sempre na mesma ordem)
        $aggregatedOrder = ['DIR_ALL', 'REG_ALL', 'AG_ALL', 'GG_ALL', 'G_ALL'];
        foreach ($aggregatedOrder as $unitKey) {
            if (isset($hierarchyUnits[$unitKey])) {
                $ordered[] = $hierarchyUnits[$unitKey];
            }
        }
        
        // Depois individuais na ordem hierárquica: DIR -> REG -> AG -> GG -> G
        // E também unidades especiais de time (G_ALL_GG_*, GG_ALL_AG_*, AG_ALL_REG_*)
        $dirUnits = [];
        $regUnits = [];
        $agUnits = [];
        $ggUnits = [];
        $gUnits = [];
        $teamUnits = []; // Unidades de time (agregações de nível abaixo)
        
        foreach ($hierarchyUnits as $unitKey => $unit) {
            if (strpos($unitKey, 'DIR_') === 0 && $unitKey !== 'DIR_ALL') {
                $dirUnits[$unitKey] = $unit;
            } elseif (strpos($unitKey, 'REG_') === 0 && $unitKey !== 'REG_ALL') {
                $regUnits[$unitKey] = $unit;
            } elseif (strpos($unitKey, 'AG_') === 0 && $unitKey !== 'AG_ALL') {
                $agUnits[$unitKey] = $unit;
            } elseif (strpos($unitKey, 'GG_') === 0 && $unitKey !== 'GG_ALL') {
                $ggUnits[$unitKey] = $unit;
            } elseif (strpos($unitKey, 'G_') === 0 && strpos($unitKey, 'GG_') !== 0 && $unitKey !== 'G_ALL') {
                // Verificar se é unidade de time
                if (strpos($unitKey, 'G_ALL_GG_') === 0 || strpos($unitKey, 'GG_ALL_AG_') === 0 || strpos($unitKey, 'AG_ALL_REG_') === 0) {
                    $teamUnits[$unitKey] = $unit;
                } else {
                    $gUnits[$unitKey] = $unit;
                }
            }
        }
        
        $sortFn = function($a, $b) {
            return strcmp($a['label'], $b['label']);
        };
        
        uasort($dirUnits, $sortFn);
        uasort($regUnits, $sortFn);
        uasort($agUnits, $sortFn);
        uasort($ggUnits, $sortFn);
        uasort($gUnits, $sortFn);
        uasort($teamUnits, $sortFn);
        
        // Ordem: DIR -> REG -> AG -> GG -> G -> Teams (agregações de time)
        $ordered = array_merge(
            $ordered, 
            array_values($dirUnits), 
            array_values($regUnits), 
            array_values($agUnits), 
            array_values($ggUnits), 
            array_values($gUnits),
            array_values($teamUnits)
        );
        
        return $ordered;
    }

    private function getTableName(string $entityClass): string
    {
        return $this->getEntityManager()
            ->getClassMetadata($entityClass)
            ->getTableName();
    }
}
