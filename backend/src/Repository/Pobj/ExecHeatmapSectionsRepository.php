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

    private function getTableName(string $entityClass): string
    {
        return $this->getEntityManager()
            ->getClassMetadata($entityClass)
            ->getTableName();
    }
}
