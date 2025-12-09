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

class ExecHeatmapSectionsRepository extends ServiceEntityRepository
{
    private $filterBuilder;

    public function __construct(ManagerRegistry $registry, ExecFilterBuilder $filterBuilder)
    {
        parent::__construct($registry, FRealizados::class);
        $this->filterBuilder = $filterBuilder;
    }

    public function findHeatmapSections(?FilterDTO $filters = null): array
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
        $whereClause = $this->filterBuilder->buildWhereClause(
            $filters,
            $params,
            false,
            [
                'produto' => 'prod',
                'familia' => 'fam',
                'indicador' => 'ind'
            ]
        );

        $months = $this->calculateLast12Months();
        
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

        return [
            'units' => array_values($sectionsData['units']),
            'sectionsFamilia' => array_values($sectionsData['sectionsFamilia']),
            'sectionsIndicador' => array_values($sectionsData['sectionsIndicador']),
            'dataFamilia' => $sectionsData['dataFamilia'],
            'dataIndicador' => $sectionsData['dataIndicador'],
            'dataFamiliaMensal' => $sectionsData['dataFamiliaMensal'],
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

            // Determinar qual unidade usar baseado no filtro aplicado
            $unitKey = null;
            $unitLabel = null;
            
            // Determinar o nível de filtro aplicado
            $hasGerenteFilter = $filters && $filters->getGerente();
            $hasGerenteGestaoFilter = $filters && $filters->getGerenteGestao();
            $hasAgenciaFilter = $filters && $filters->getAgencia();
            $hasRegionalFilter = $filters && $filters->getRegional();
            $hasDiretoriaFilter = $filters && $filters->getDiretoria();
            
            if ($hasGerenteFilter) {
                // Filtro por gerente: mostrar apenas o gerente filtrado (ou indicadores serão usados no frontend)
                if ($tipoGerente === 'gerente' && $gerenteIdRow && (string)$gerenteIdRow === (string)$gerenteId) {
                    $unitKey = "G_{$gerenteIdRow}";
                    $unitLabel = $gerenteNome;
                }
            } elseif ($hasGerenteGestaoFilter) {
                // Filtro por gerente de gestão: mostrar gerentes (G_*)
                if ($tipoGerente === 'gerente' && $gerenteIdRow) {
                    $unitKey = "G_{$gerenteIdRow}";
                    $unitLabel = $gerenteNome;
                }
            } elseif ($hasAgenciaFilter) {
                // Filtro por agência: mostrar gerentes de gestão (GG_*)
                if ($tipoGerente === 'gerenteGestao' && $gerenteIdRow) {
                    $unitKey = "GG_{$gerenteIdRow}";
                    $unitLabel = $gerenteNome;
                }
            } elseif ($hasRegionalFilter) {
                // Filtro por regional: mostrar agências (AG_*)
                if ($agenciaId && $agenciaNome) {
                    $unitKey = "AG_{$agenciaId}";
                    $unitLabel = $agenciaNome;
                }
            } else {
                // Sem filtro ou filtro por diretoria: mostrar regionais (REG_*)
                if ($regionalId && $regionalNome) {
                    $unitKey = "REG_{$regionalId}";
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

        // Garantir que as unidades corretas sejam criadas baseado nos filtros aplicados
        $hasGerenteFilter = $filters && $filters->getGerente();
        $hasGerenteGestaoFilter = $filters && $filters->getGerenteGestao();
        $hasAgenciaFilter = $filters && $filters->getAgencia();
        $hasRegionalFilter = $filters && $filters->getRegional();
        
        if ($hasGerenteFilter && $gerenteId) {
            // Quando filtrar por gerente, garantir que o gerente esteja nas unidades
            $gerenteInfoSql = "SELECT id, nome FROM {$dEstruturaTable} 
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
                $unitKey = "G_{$gerenteId}";
                if (!isset($units[$unitKey])) {
                    $units[$unitKey] = [
                        'value' => $unitKey,
                        'label' => $gerenteInfo['nome']
                    ];
                }
            }
        } elseif ($hasGerenteGestaoFilter) {
            // Quando filtrar por gerente de gestão, buscar todos os gerentes dessa agência
            $ggId = $filters->getGerenteGestao();
            $ggFuncional = $this->filterBuilder->getFuncionalFromIdOrFuncional($ggId, Cargo::GERENTE_GESTAO);
            
            if ($ggFuncional) {
                // Buscar informações do gerente de gestão
                $ggInfoSql = "SELECT segmento_id, diretoria_id, regional_id, agencia_id, id, nome
                             FROM {$dEstruturaTable} 
                             WHERE funcional = :ggFuncional 
                             AND cargo_id = :cargoGerenteGestao 
                             LIMIT 1";
                $ggInfoResult = $conn->executeQuery($ggInfoSql, [
                    'ggFuncional' => $ggFuncional,
                    'cargoGerenteGestao' => Cargo::GERENTE_GESTAO
                ]);
                $ggInfo = $ggInfoResult->fetchAssociative();
                $ggInfoResult->free();
                
                if ($ggInfo) {
                    // Buscar todos os gerentes dessa agência
                    $gerentesSql = "SELECT id, nome FROM {$dEstruturaTable} 
                                   WHERE cargo_id = :cargoGerente
                                   AND segmento_id = :segmentoId
                                   AND diretoria_id = :diretoriaId
                                   AND regional_id = :regionalId
                                   AND agencia_id = :agenciaId";
                    $gerentesResult = $conn->executeQuery($gerentesSql, [
                        'cargoGerente' => Cargo::GERENTE,
                        'segmentoId' => $ggInfo['segmento_id'],
                        'diretoriaId' => $ggInfo['diretoria_id'],
                        'regionalId' => $ggInfo['regional_id'],
                        'agenciaId' => $ggInfo['agencia_id']
                    ]);
                    
                    while ($gerenteRow = $gerentesResult->fetchAssociative()) {
                        $unitKey = "G_{$gerenteRow['id']}";
                        if (!isset($units[$unitKey])) {
                            $units[$unitKey] = [
                                'value' => $unitKey,
                                'label' => $gerenteRow['nome']
                            ];
                        }
                    }
                    $gerentesResult->free();
                }
            } elseif (is_numeric($ggId)) {
                // Se não encontrou por funcional, tentar por ID
                $ggInfoSql = "SELECT segmento_id, diretoria_id, regional_id, agencia_id, id, nome
                             FROM {$dEstruturaTable} 
                             WHERE id = :ggId 
                             AND cargo_id = :cargoGerenteGestao 
                             LIMIT 1";
                $ggInfoResult = $conn->executeQuery($ggInfoSql, [
                    'ggId' => (int)$ggId,
                    'cargoGerenteGestao' => Cargo::GERENTE_GESTAO
                ]);
                $ggInfo = $ggInfoResult->fetchAssociative();
                $ggInfoResult->free();
                
                if ($ggInfo) {
                    // Buscar todos os gerentes dessa agência
                    $gerentesSql = "SELECT id, nome FROM {$dEstruturaTable} 
                                   WHERE cargo_id = :cargoGerente
                                   AND segmento_id = :segmentoId
                                   AND diretoria_id = :diretoriaId
                                   AND regional_id = :regionalId
                                   AND agencia_id = :agenciaId";
                    $gerentesResult = $conn->executeQuery($gerentesSql, [
                        'cargoGerente' => Cargo::GERENTE,
                        'segmentoId' => $ggInfo['segmento_id'],
                        'diretoriaId' => $ggInfo['diretoria_id'],
                        'regionalId' => $ggInfo['regional_id'],
                        'agenciaId' => $ggInfo['agencia_id']
                    ]);
                    
                    while ($gerenteRow = $gerentesResult->fetchAssociative()) {
                        $unitKey = "G_{$gerenteRow['id']}";
                        if (!isset($units[$unitKey])) {
                            $units[$unitKey] = [
                                'value' => $unitKey,
                                'label' => $gerenteRow['nome']
                            ];
                        }
                    }
                    $gerentesResult->free();
                }
            }
        } elseif ($hasAgenciaFilter) {
            // Quando filtrar por agência, buscar todos os gerentes de gestão dessa agência
            $agenciaId = $filters->getAgencia();
            $ggsSql = "SELECT est.id, est.nome FROM {$dEstruturaTable} AS est
                      INNER JOIN {$agenciaTable} AS ag ON ag.id = est.agencia_id
                      WHERE est.cargo_id = :cargoGerenteGestao
                      AND ag.id = :agenciaId";
            $ggsResult = $conn->executeQuery($ggsSql, [
                'cargoGerenteGestao' => Cargo::GERENTE_GESTAO,
                'agenciaId' => $agenciaId
            ]);
            
            while ($ggRow = $ggsResult->fetchAssociative()) {
                $unitKey = "GG_{$ggRow['id']}";
                if (!isset($units[$unitKey])) {
                    $units[$unitKey] = [
                        'value' => $unitKey,
                        'label' => $ggRow['nome']
                    ];
                }
            }
            $ggsResult->free();
        } elseif ($hasRegionalFilter) {
            // Quando filtrar por regional, buscar todas as agências dessa regional
            $regionalId = $filters->getRegional();
            $agenciasSql = "SELECT id, nome FROM {$agenciaTable} WHERE regional_id = :regionalId";
            $agenciasResult = $conn->executeQuery($agenciasSql, ['regionalId' => $regionalId]);
            
            while ($agRow = $agenciasResult->fetchAssociative()) {
                $unitKey = "AG_{$agRow['id']}";
                if (!isset($units[$unitKey])) {
                    $units[$unitKey] = [
                        'value' => $unitKey,
                        'label' => $agRow['nome']
                    ];
                }
            }
            $agenciasResult->free();
        } else {
            // Sem filtro ou filtro por diretoria: buscar todas as regionais
            $diretoriasFilter = $filters && $filters->getDiretoria() ? $filters->getDiretoria() : null;
            
            if ($diretoriasFilter) {
                // Se há filtro de diretoria, buscar apenas regionais dessa diretoria
                $regionaisSql = "SELECT id, nome FROM {$regionalTable} WHERE diretoria_id = :diretoriaId";
                $regionaisResult = $conn->executeQuery($regionaisSql, ['diretoriaId' => $diretoriasFilter]);
            } else {
                // Sem filtro, buscar todas as regionais
                $regionaisSql = "SELECT id, nome FROM {$regionalTable}";
                $regionaisResult = $conn->executeQuery($regionaisSql);
            }
            
            while ($regRow = $regionaisResult->fetchAssociative()) {
                $unitKey = "REG_{$regRow['id']}";
                if (!isset($units[$unitKey])) {
                    $units[$unitKey] = [
                        'value' => $unitKey,
                        'label' => $regRow['nome']
                    ];
                }
            }
            $regionaisResult->free();
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

    private function getTableName(string $entityClass): string
    {
        return $this->getEntityManager()
            ->getClassMetadata($entityClass)
            ->getTableName();
    }
}
