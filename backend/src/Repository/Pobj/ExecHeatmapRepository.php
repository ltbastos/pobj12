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
use App\Repository\Pobj\Helper\ExecFilterBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExecHeatmapRepository extends ServiceEntityRepository
{
    private $filterBuilder;

    public function __construct(ManagerRegistry $registry, ExecFilterBuilder $filterBuilder)
    {
        parent::__construct($registry, FRealizados::class);
        $this->filterBuilder = $filterBuilder;
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
        $whereClause = $this->filterBuilder->buildWhereClause($filters, $params, false);

        $months = $this->calculateLast12Months();
        
        // Buscar dados para modo "Seções"
        $sectionsData = $this->fetchSectionsData(
            $fRealizadosTable,
            $fMetaTable,
            $dCalendarioTable,
            $dEstruturaTable,
            $dProdutosTable,
            $regionalTable,
            $familiaTable,
            $indicadorTable,
            $whereClause,
            $params,
            $months,
            $filters
        );

        // Buscar dados para modo "Metas" (hierarquia)
        $hierarchyData = $this->fetchHierarchyData(
            $fMetaTable,
            $dEstruturaTable,
            $regionalTable,
            $diretoriaTable = $this->getTableName(Diretoria::class),
            $agenciaTable = $this->getTableName(Agencia::class),
            $whereClause,
            $params,
            $months,
            $filters
        );

        // Mesclar dados
        $allUnits = array_merge($sectionsData['units'], $hierarchyData['units']);
        $dataFamiliaMensal = array_merge($sectionsData['dataFamiliaMensal'], $hierarchyData['dataFamiliaMensal']);

        return [
            'units' => array_values($allUnits),
            'sectionsFamilia' => array_values($sectionsData['sectionsFamilia']),
            'sectionsIndicador' => array_values($sectionsData['sectionsIndicador']),
            'dataFamilia' => $sectionsData['dataFamilia'],
            'dataIndicador' => $sectionsData['dataIndicador'],
            'dataFamiliaMensal' => $dataFamiliaMensal,
            'dataIndicadorMensal' => $sectionsData['dataIndicadorMensal'],
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

    private function fetchSectionsData(
        string $fRealizadosTable,
        string $fMetaTable,
        string $dCalendarioTable,
        string $dEstruturaTable,
        string $dProdutosTable,
        string $regionalTable,
        string $familiaTable,
        string $indicadorTable,
        string $whereClause,
        array &$params,
        array $months,
        ?FilterDTO $filters
    ): array {
        $conn = $this->getEntityManager()->getConnection();
        $diretoriaTable = $this->getTableName(Diretoria::class);
        $agenciaTable = $this->getTableName(Agencia::class);

        // Buscar todas as famílias
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

        // Buscar dados realizados - agora incluindo informações de toda a hierarquia
        $monthKeys = array_column($months, 'key');
        $monthPlaceholders = [];
        foreach ($monthKeys as $index => $key) {
            $paramName = 'mes' . $index;
            $monthPlaceholders[] = ':' . $paramName;
            $params[$paramName] = $key;
        }
        $placeholders = implode(',', $monthPlaceholders);

        $sql = "SELECT
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
            CAST(fam.id AS CHAR) AS familia_id,
            fam.nm_familia AS familia_nome,
            CAST(ind.id AS CHAR) AS indicador_id,
            ind.nm_indicador AS indicador_nome,
            DATE_FORMAT(c.data, '%Y-%m') AS mes,
            COALESCE(SUM(r.realizado), 0) AS realizado,
            COALESCE(SUM(m.meta_mensal), 0) AS meta
        FROM {$dEstruturaTable} AS est
        INNER JOIN {$fRealizadosTable} AS r ON r.funcional = est.funcional
        INNER JOIN {$dCalendarioTable} AS c ON c.data = r.data_realizado
        INNER JOIN {$dProdutosTable} AS prod ON prod.id = r.produto_id
        INNER JOIN {$familiaTable} AS fam ON fam.id = prod.familia_id
        INNER JOIN {$indicadorTable} AS ind ON ind.id = prod.indicador_id
        LEFT JOIN {$fMetaTable} AS m ON m.produto_id = prod.id
            AND m.funcional = est.funcional
            AND m.data_meta = c.data
        LEFT JOIN {$diretoriaTable} AS dir ON dir.id = est.diretoria_id
        LEFT JOIN {$regionalTable} AS reg ON reg.id = est.regional_id
        LEFT JOIN {$agenciaTable} AS ag ON ag.id = est.agencia_id
        WHERE 1=1 {$expandedWhereClause}
            AND DATE_FORMAT(c.data, '%Y-%m') IN ({$placeholders})
        GROUP BY dir.id, dir.nome, reg.id, reg.nome, ag.id, ag.nome, 
                 est.id, est.nome, est.cargo_id,
                 fam.id, fam.nm_familia, ind.id, ind.nm_indicador, DATE_FORMAT(c.data, '%Y-%m')
        HAVING realizado > 0 OR meta > 0
        ORDER BY dir.nome, reg.nome, ag.nome, est.nome, fam.nm_familia, ind.nm_indicador, c.data";
        
        $sectionsParams = array_merge($params, [
            'cargoGerente' => Cargo::GERENTE,
            'cargoGerenteGestao' => Cargo::GERENTE_GESTAO,
            'tipoGerente' => 'gerente',
            'tipoGerenteGestao' => 'gerenteGestao'
        ]);

        $result = $conn->executeQuery($sql, $sectionsParams);

        $units = [];
        $sectionsFamilia = [];
        $sectionsIndicador = [];
        $dataFamilia = [];
        $dataIndicador = [];
        $dataFamiliaMensal = [];
        $dataIndicadorMensal = [];

        // Se um gerente está filtrado, criar unidades individuais da hierarquia
        $isGerenteFiltered = $filters && $filters->getGerente();
        $gerenteId = $isGerenteFiltered ? $filters->getGerente() : null;

        while ($row = $result->fetchAssociative()) {
            $diretoriaId = $row['diretoria_id'] ?? '';
            $diretoriaNome = $row['diretoria_nome'] ?? '';
            $regionalId = $row['regional_id'] ?? '';
            $regionalNome = $row['regional_nome'] ?? '';
            $agenciaId = $row['agencia_id'] ?? '';
            $agenciaNome = $row['agencia_nome'] ?? '';
            $gerenteIdRow = $row['gerente_id'] ?? '';
            $gerenteNome = $row['gerente_nome'] ?? '';
            $tipoGerente = $row['tipo_gerente'] ?? '';
            $familiaId = $row['familia_id'] ?? '';
            $familiaNome = $row['familia_nome'] ?? '';
            $indicadorId = $row['indicador_id'] ?? '';
            $indicadorNome = $row['indicador_nome'] ?? '';
            $mes = $row['mes'] ?? '';
            $realizado = (float)($row['realizado'] ?? 0);
            $meta = (float)($row['meta'] ?? 0);

            // Determinar qual unidade usar baseado no filtro
            $unitKey = null;
            $unitLabel = null;
            
            if ($isGerenteFiltered) {
                // Quando gerente está filtrado, criar unidades individuais da hierarquia
                if ($tipoGerente === 'gerente' && $gerenteIdRow && (string)$gerenteIdRow === (string)$gerenteId) {
                    // O gerente filtrado
                    $unitKey = "G_{$gerenteIdRow}";
                    $unitLabel = $gerenteNome;
                } elseif ($tipoGerente === 'gerenteGestao' && $gerenteIdRow) {
                    // Gerente de gestão responsável
                    $unitKey = "GG_{$gerenteIdRow}";
                    $unitLabel = $gerenteNome;
                } elseif ($agenciaId && $agenciaNome) {
                    // Agência do gerente
                    $unitKey = "AG_{$agenciaId}";
                    $unitLabel = $agenciaNome;
                } elseif ($regionalId && $regionalNome) {
                    // Regional do gerente
                    $unitKey = "REG_{$regionalId}";
                    $unitLabel = $regionalNome;
                }
            } else {
                // Comportamento padrão: usar regional
                if ($regionalId && $regionalNome) {
                    $unitKey = $regionalId;
                    $unitLabel = $regionalNome;
                }
            }

            if ($unitKey && $unitLabel) {
                if (!isset($units[$unitKey])) {
                    $units[$unitKey] = [
                        'value' => $unitKey,
                        'label' => $unitLabel
                    ];
                }
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

            // Usar unitKey para as chaves de dados
            if ($unitKey) {
                $keyFamilia = "{$unitKey}|{$familiaId}";
                if (!isset($dataFamilia[$keyFamilia])) {
                    $dataFamilia[$keyFamilia] = ['real' => 0, 'meta' => 0];
                }
                $dataFamilia[$keyFamilia]['real'] += $realizado;
                $dataFamilia[$keyFamilia]['meta'] += $meta;

                $keyIndicador = "{$unitKey}|{$indicadorId}";
                if (!isset($dataIndicador[$keyIndicador])) {
                    $dataIndicador[$keyIndicador] = ['real' => 0, 'meta' => 0];
                }
                $dataIndicador[$keyIndicador]['real'] += $realizado;
                $dataIndicador[$keyIndicador]['meta'] += $meta;

                $keyFamiliaMensal = "{$unitKey}|{$familiaId}|{$mes}";
                if (!isset($dataFamiliaMensal[$keyFamiliaMensal])) {
                    $dataFamiliaMensal[$keyFamiliaMensal] = ['real' => 0, 'meta' => 0];
                }
                $dataFamiliaMensal[$keyFamiliaMensal]['real'] += $realizado;
                $dataFamiliaMensal[$keyFamiliaMensal]['meta'] += $meta;

                $keyIndicadorMensal = "{$unitKey}|{$indicadorId}|{$mes}";
                if (!isset($dataIndicadorMensal[$keyIndicadorMensal])) {
                    $dataIndicadorMensal[$keyIndicadorMensal] = ['real' => 0, 'meta' => 0];
                }
                $dataIndicadorMensal[$keyIndicadorMensal]['real'] += $realizado;
                $dataIndicadorMensal[$keyIndicadorMensal]['meta'] += $meta;
            }
        }
        $result->free();

        // Se um gerente está filtrado, garantir que todas as unidades da hierarquia sejam criadas
        if ($isGerenteFiltered && $gerenteId) {
            // Buscar informações do gerente para construir a hierarquia completa
            $gerenteInfoSql = "SELECT segmento_id, diretoria_id, regional_id, agencia_id 
                              FROM {$dEstruturaTable} 
                              WHERE id = :gerenteId 
                              AND cargo_id = :cargoGerente 
                              LIMIT 1";
            $gerenteInfoResult = $conn->executeQuery($gerenteInfoSql, [
                'gerenteId' => $gerenteId,
                'cargoGerente' => Cargo::GERENTE
            ]);
            $gerenteInfo = $gerenteInfoResult->fetchAssociative();
            $gerenteInfoResult->free();
            
            if ($gerenteInfo) {
                $regionalId = $gerenteInfo['regional_id'] ?? null;
                $agenciaId = $gerenteInfo['agencia_id'] ?? null;
                
                // Buscar informações da regional
                if ($regionalId) {
                    $regInfoSql = "SELECT id, nome FROM {$regionalTable} WHERE id = :regionalId LIMIT 1";
                    $regInfoResult = $conn->executeQuery($regInfoSql, ['regionalId' => $regionalId]);
                    $regInfo = $regInfoResult->fetchAssociative();
                    $regInfoResult->free();
                    
                    if ($regInfo) {
                        $unitKey = "REG_{$regionalId}";
                        if (!isset($units[$unitKey])) {
                            $units[$unitKey] = [
                                'value' => $unitKey,
                                'label' => $regInfo['nome']
                            ];
                        }
                    }
                }
                
                // Buscar informações da agência
                if ($agenciaId) {
                    $agInfoSql = "SELECT id, nome FROM {$agenciaTable} WHERE id = :agenciaId LIMIT 1";
                    $agInfoResult = $conn->executeQuery($agInfoSql, ['agenciaId' => $agenciaId]);
                    $agInfo = $agInfoResult->fetchAssociative();
                    $agInfoResult->free();
                    
                    if ($agInfo) {
                        $unitKey = "AG_{$agenciaId}";
                        if (!isset($units[$unitKey])) {
                            $units[$unitKey] = [
                                'value' => $unitKey,
                                'label' => $agInfo['nome']
                            ];
                        }
                    }
                }
                
                // Buscar gerente de gestão responsável
                $ggInfoSql = "SELECT id, nome FROM {$dEstruturaTable} 
                             WHERE cargo_id = :cargoGerenteGestao
                             AND segmento_id = :segmentoId
                             AND diretoria_id = :diretoriaId
                             AND regional_id = :regionalId
                             AND agencia_id = :agenciaId
                             LIMIT 1";
                $ggInfoResult = $conn->executeQuery($ggInfoSql, [
                    'cargoGerenteGestao' => Cargo::GERENTE_GESTAO,
                    'segmentoId' => $gerenteInfo['segmento_id'],
                    'diretoriaId' => $gerenteInfo['diretoria_id'],
                    'regionalId' => $regionalId,
                    'agenciaId' => $agenciaId
                ]);
                $ggInfo = $ggInfoResult->fetchAssociative();
                $ggInfoResult->free();
                
                if ($ggInfo) {
                    $unitKey = "GG_{$ggInfo['id']}";
                    if (!isset($units[$unitKey])) {
                        $units[$unitKey] = [
                            'value' => $unitKey,
                            'label' => $ggInfo['nome']
                        ];
                    }
                }
                
                // Adicionar o gerente individual
                $gerenteInfoSql2 = "SELECT id, nome FROM {$dEstruturaTable} 
                                    WHERE id = :gerenteId 
                                    AND cargo_id = :cargoGerente 
                                    LIMIT 1";
                $gerenteInfoResult2 = $conn->executeQuery($gerenteInfoSql2, [
                    'gerenteId' => $gerenteId,
                    'cargoGerente' => Cargo::GERENTE
                ]);
                $gerenteInfo2 = $gerenteInfoResult2->fetchAssociative();
                $gerenteInfoResult2->free();
                
                if ($gerenteInfo2) {
                    $unitKey = "G_{$gerenteId}";
                    if (!isset($units[$unitKey])) {
                        $units[$unitKey] = [
                            'value' => $unitKey,
                            'label' => $gerenteInfo2['nome']
                        ];
                    }
                }
            }
        }

        // Mesclar todas as famílias
        foreach ($allFamilies as $familiaId => $familia) {
            if (!isset($sectionsFamilia[$familiaId])) {
                $sectionsFamilia[$familiaId] = $familia;
            }
        }

        return [
            'units' => array_values($units),
            'sectionsFamilia' => $sectionsFamilia,
            'sectionsIndicador' => $sectionsIndicador,
            'dataFamilia' => $dataFamilia,
            'dataIndicador' => $dataIndicador,
            'dataFamiliaMensal' => $dataFamiliaMensal,
            'dataIndicadorMensal' => $dataIndicadorMensal
        ];
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
