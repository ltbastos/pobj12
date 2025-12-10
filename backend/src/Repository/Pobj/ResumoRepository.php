<?php

namespace App\Repository\Pobj;

use App\Domain\DTO\FilterDTO;
use App\Domain\Enum\Cargo;
use App\Entity\Pobj\DProduto;
use App\Entity\Pobj\FVariavel;
use App\Entity\Pobj\DCalendario;
use App\Entity\Pobj\FRealizados;
use App\Entity\Pobj\FMeta;
use App\Entity\Pobj\FPontos;
use App\Entity\Pobj\Familia;
use App\Entity\Pobj\Indicador;
use App\Entity\Pobj\Subindicador;
use App\Entity\Pobj\DEstrutura;
use App\Entity\Pobj\Segmento;
use App\Entity\Pobj\Diretoria;
use App\Entity\Pobj\Regional;
use App\Entity\Pobj\Agencia;
use App\Repository\Contract\ResumoRepositoryInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

class ResumoRepository extends ServiceEntityRepository implements ResumoRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DProduto::class);
    }


    
    private function buildMetaSubquery(string $fMetaTable, string $dEstruturaTable, string $metaFilter, ?FilterDTO $filters): string
    {
        $estruturaJoin = $this->needsEstruturaJoin($filters) ? "INNER JOIN {$dEstruturaTable} AS e1 ON e1.funcional = m.funcional" : "";
        return "SELECT 
                    m.produto_id, 
                    SUM(m.meta_mensal) AS total_meta
                FROM {$fMetaTable} AS m
                {$estruturaJoin}
                WHERE 1=1 {$metaFilter}
                GROUP BY m.produto_id";
    }

    
    private function buildRealizadosSubquery(string $fRealizadosTable, string $dEstruturaTable, string $realizadosFilter, ?FilterDTO $filters): string
    {
        $estruturaJoin = $this->needsEstruturaJoin($filters) ? "INNER JOIN {$dEstruturaTable} AS e2 ON e2.funcional = r.funcional" : "";
        return "SELECT 
                    r.produto_id, 
                    SUM(r.realizado) AS total_realizado,
                    MAX(r.data_realizado) AS ultima_atualizacao
                FROM {$fRealizadosTable} AS r
                {$estruturaJoin}
                WHERE r.produto_id IS NOT NULL {$realizadosFilter}
                GROUP BY r.produto_id";
    }

    
    private function buildPontosSubquery(string $fPontosTable, string $dEstruturaTable, string $pontosFilter, ?FilterDTO $filters): string
    {
        $estruturaJoin = $this->needsEstruturaJoin($filters) ? "INNER JOIN {$dEstruturaTable} AS e3 ON e3.funcional = p.funcional" : "";
        return "SELECT 
                    p.produto_id, 
                    SUM(p.realizado) AS total_pontos,
                    SUM(p.meta) AS total_meta_pontos
                FROM {$fPontosTable} AS p
                {$estruturaJoin}
                WHERE 1=1 {$pontosFilter}
                GROUP BY p.produto_id";
    }

    
    public function findProdutos(?FilterDTO $filters = null): array
    {
        $dProdutosTable = $this->getTableName(DProduto::class);
        $fMetaTable = $this->getTableName(FMeta::class);
        $fRealizadosTable = $this->getTableName(FRealizados::class);
        $fPontosTable = $this->getTableName(FPontos::class);
        $dCalendarioTable = $this->getTableName(DCalendario::class);
        $dEstruturaTable = $this->getTableName(DEstrutura::class);
        $familiaTable = $this->getTableName(Familia::class);
        $indicadorTable = $this->getTableName(Indicador::class);
        $subindicadorTable = $this->getTableName(Subindicador::class);

        $params = [];
        $types = [];
        $metaFilter = '';
        $realizadosFilter = '';
        $pontosFilter = '';
        $produtoFilter = '';
        
        if ($filters) {
            $dataInicio = $filters->getDataInicio();
            $dataFim = $filters->getDataFim();
            
            if ($dataInicio) {
                                $metaFilter .= " AND m.data_meta >= :metaDataInicio";
                $realizadosFilter .= " AND r.data_realizado >= :realizadosDataInicio";
                $pontosFilter .= " AND p.data_realizado >= :pontosDataInicio";
                $params['metaDataInicio'] = $dataInicio;
                $params['realizadosDataInicio'] = $dataInicio;
                $params['pontosDataInicio'] = $dataInicio;
            }
            
            if ($dataFim) {
                                $metaFilter .= " AND m.data_meta <= :metaDataFim";
                $realizadosFilter .= " AND r.data_realizado <= :realizadosDataFim";
                $pontosFilter .= " AND p.data_realizado <= :pontosDataFim";
                $params['metaDataFim'] = $dataFim;
                $params['realizadosDataFim'] = $dataFim;
                $params['pontosDataFim'] = $dataFim;
            }
            
                        $gerente = $filters->getGerente();
            $gerenteGestao = $filters->getGerenteGestao();
            
            if ($gerente !== null && $gerente !== '') {
                                $gerenteFuncional = $this->getFuncionalFromIdOrFuncional($gerente, Cargo::GERENTE);
                if ($gerenteFuncional) {
                                        $metaFilter .= " AND m.funcional = :gerenteFuncionalMeta";
                    $realizadosFilter .= " AND r.funcional = :gerenteFuncionalRealizados";
                    $pontosFilter .= " AND p.funcional = :gerenteFuncionalPontos";
                    $params['gerenteFuncionalMeta'] = $gerenteFuncional;
                    $params['gerenteFuncionalRealizados'] = $gerenteFuncional;
                    $params['gerenteFuncionalPontos'] = $gerenteFuncional;
                }
            } elseif ($gerenteGestao !== null && $gerenteGestao !== '') {
                                $gerentesFuncionais = $this->getGerentesFuncionaisByGerenteGestao($gerenteGestao);
                if ($gerentesFuncionais) {
                                        $metaFilter .= " AND m.funcional IN (:gerenteGestaoFuncionaisMeta)";
                    $realizadosFilter .= " AND r.funcional IN (:gerenteGestaoFuncionaisRealizados)";
                    $pontosFilter .= " AND p.funcional IN (:gerenteGestaoFuncionaisPontos)";
                    $params['gerenteGestaoFuncionaisMeta'] = $gerentesFuncionais;
                    $params['gerenteGestaoFuncionaisRealizados'] = $gerentesFuncionais;
                    $params['gerenteGestaoFuncionaisPontos'] = $gerentesFuncionais;
                    $types['gerenteGestaoFuncionaisMeta'] = Connection::PARAM_STR_ARRAY;
                    $types['gerenteGestaoFuncionaisRealizados'] = Connection::PARAM_STR_ARRAY;
                    $types['gerenteGestaoFuncionaisPontos'] = Connection::PARAM_STR_ARRAY;
                }
            } else {
                                                $estruturaFiltersMeta = $this->buildEstruturaFilters($filters, $params, 'e1', 'meta');
                $estruturaFiltersRealizados = $this->buildEstruturaFilters($filters, $params, 'e2', 'realizados');
                $estruturaFiltersPontos = $this->buildEstruturaFilters($filters, $params, 'e3', 'pontos');
                $metaFilter .= $estruturaFiltersMeta;
                $realizadosFilter .= $estruturaFiltersRealizados;
                $pontosFilter .= $estruturaFiltersPontos;
            }
            
                                    $subindicador = $filters->getSubindicador();
            $indicador = $filters->getIndicador();
            $familia = $filters->getFamilia();
            
            if ($subindicador !== null && $subindicador !== '') {
                                $produtoFilter .= " AND dp.subindicador_id = :subindicadorId";
                $params['subindicadorId'] = $subindicador;
            } elseif ($indicador !== null && $indicador !== '') {
                                $produtoFilter .= " AND dp.indicador_id = :indicadorId";
                $params['indicadorId'] = $indicador;
            } elseif ($familia !== null && $familia !== '') {
                                $produtoFilter .= " AND dp.familia_id = :familiaId";
                $params['familiaId'] = $familia;
            }
            
                                    $status = $filters->getStatus();
            if ($status !== null && $status !== '' && $status !== '03') {
                                if ($status === '01') {
                    $produtoFilter .= " AND (CASE 
                        WHEN COALESCE(fm.total_meta, 0) > 0 
                        THEN CASE 
                            WHEN COALESCE(fr.total_realizado, 0) / fm.total_meta >= 1 
                            THEN 1 
                            ELSE 0 
                        END
                        ELSE 0
                    END) = 1";
                } elseif ($status === '02') {
                    $produtoFilter .= " AND (CASE 
                        WHEN COALESCE(fm.total_meta, 0) > 0 
                        THEN CASE 
                            WHEN COALESCE(fr.total_realizado, 0) / fm.total_meta >= 1 
                            THEN 1 
                            ELSE 0 
                        END
                        ELSE 0
                    END) = 0";
                }
            }
        }

        $sql = "SELECT 
                    dp.id,
                    dp.familia_id as id_familia,
                    f.nm_familia as familia,
                    dp.indicador_id as id_indicador,
                    i.nm_indicador as indicador,
                    dp.subindicador_id as id_subindicador,
                    s.nm_subindicador as subindicador,
                    dp.metrica,
                    dp.peso,
                    COALESCE(fm.total_meta, 0) AS meta,
                    COALESCE(fr.total_realizado, 0) AS realizado,
                    COALESCE(fp.total_pontos, 0) AS pontos,
                    COALESCE(fp.total_meta_pontos, 0) AS pontos_meta,
                    CASE
                        WHEN LOWER(dp.metrica) IN ('perc', 'percentual', 'percent')
                        THEN COALESCE(fr.total_realizado / NULLIF(fm.total_meta, 0), 0) * 100
                        ELSE COALESCE(fr.total_realizado / NULLIF(fm.total_meta, 0), 0)
                    END AS ating,
                    CASE 
                        WHEN COALESCE(fm.total_meta, 0) > 0 
                        THEN CASE 
                            WHEN COALESCE(fr.total_realizado, 0) / fm.total_meta >= 1 
                            THEN 1 
                            ELSE 0 
                        END
                        ELSE 0
                    END AS atingido,
                    fr.ultima_atualizacao
                FROM {$dProdutosTable} AS dp
                LEFT JOIN {$familiaTable} AS f ON f.id = dp.familia_id
                LEFT JOIN {$indicadorTable} AS i ON i.id = dp.indicador_id
                LEFT JOIN {$subindicadorTable} AS s ON s.id = dp.subindicador_id
                LEFT JOIN (
                    " . $this->buildMetaSubquery($fMetaTable, $dEstruturaTable, $metaFilter, $filters) . "
                ) AS fm ON fm.produto_id = dp.id
                LEFT JOIN (
                    " . $this->buildRealizadosSubquery($fRealizadosTable, $dEstruturaTable, $realizadosFilter, $filters) . "
                ) AS fr ON fr.produto_id = dp.id
                LEFT JOIN (
                    " . $this->buildPontosSubquery($fPontosTable, $dEstruturaTable, $pontosFilter, $filters) . "
                ) AS fp ON fp.produto_id = dp.id
                WHERE 1=1 {$produtoFilter}
                ORDER BY f.nm_familia ASC, i.nm_indicador ASC, s.nm_subindicador ASC";

        $connection = $this->getEntityManager()->getConnection();
        $result = $connection->executeQuery($sql, $params, $types);
        
                $rows = [];
        while ($row = $result->fetchAssociative()) {
            $rows[] = $row;
        }
        $result->free();
        
        return $rows;
    }

    
    public function findProdutosMensais(?FilterDTO $filters = null): array
    {
        $dProdutosTable = $this->getTableName(DProduto::class);
        $fMetaTable = $this->getTableName(FMeta::class);
        $fRealizadosTable = $this->getTableName(FRealizados::class);
        $fPontosTable = $this->getTableName(FPontos::class);
        $dCalendarioTable = $this->getTableName(DCalendario::class);
        $dEstruturaTable = $this->getTableName(DEstrutura::class);
        $familiaTable = $this->getTableName(Familia::class);
        $indicadorTable = $this->getTableName(Indicador::class);
        $subindicadorTable = $this->getTableName(Subindicador::class);

        $params = [];
        $types = [];
        $metaFilter = '';
        $realizadosFilter = '';
        $pontosFilter = '';
        $produtoFilter = '';
        
        if ($filters) {
            $dataInicio = $filters->getDataInicio();
            $dataFim = $filters->getDataFim();
            
            if ($dataInicio) {
                                $metaFilter .= " AND m.data_meta >= :metaDataInicio";
                $realizadosFilter .= " AND r.data_realizado >= :realizadosDataInicio";
                $pontosFilter .= " AND p.data_realizado >= :pontosDataInicio";
                $params['metaDataInicio'] = $dataInicio;
                $params['realizadosDataInicio'] = $dataInicio;
                $params['pontosDataInicio'] = $dataInicio;
            }
            
            if ($dataFim) {
                                $metaFilter .= " AND m.data_meta <= :metaDataFim";
                $realizadosFilter .= " AND r.data_realizado <= :realizadosDataFim";
                $pontosFilter .= " AND p.data_realizado <= :pontosDataFim";
                $params['metaDataFim'] = $dataFim;
                $params['realizadosDataFim'] = $dataFim;
                $params['pontosDataFim'] = $dataFim;
            }
            
                        $gerente = $filters->getGerente();
            $gerenteGestao = $filters->getGerenteGestao();
            
            if ($gerente !== null && $gerente !== '') {
                                $gerenteFuncional = $this->getFuncionalFromIdOrFuncional($gerente, Cargo::GERENTE);
                if ($gerenteFuncional) {
                                        $metaFilter .= " AND m.funcional = :gerenteFuncionalMeta";
                    $realizadosFilter .= " AND r.funcional = :gerenteFuncionalRealizados";
                    $pontosFilter .= " AND p.funcional = :gerenteFuncionalPontos";
                    $params['gerenteFuncionalMeta'] = $gerenteFuncional;
                    $params['gerenteFuncionalRealizados'] = $gerenteFuncional;
                    $params['gerenteFuncionalPontos'] = $gerenteFuncional;
                }
            } elseif ($gerenteGestao !== null && $gerenteGestao !== '') {
                                $gerentesFuncionais = $this->getGerentesFuncionaisByGerenteGestao($gerenteGestao);
                if ($gerentesFuncionais) {
                                        $metaFilter .= " AND m.funcional IN (:gerenteGestaoFuncionaisMeta)";
                    $realizadosFilter .= " AND r.funcional IN (:gerenteGestaoFuncionaisRealizados)";
                    $pontosFilter .= " AND p.funcional IN (:gerenteGestaoFuncionaisPontos)";
                    $params['gerenteGestaoFuncionaisMeta'] = $gerentesFuncionais;
                    $params['gerenteGestaoFuncionaisRealizados'] = $gerentesFuncionais;
                    $params['gerenteGestaoFuncionaisPontos'] = $gerentesFuncionais;
                    $types['gerenteGestaoFuncionaisMeta'] = Connection::PARAM_STR_ARRAY;
                    $types['gerenteGestaoFuncionaisRealizados'] = Connection::PARAM_STR_ARRAY;
                    $types['gerenteGestaoFuncionaisPontos'] = Connection::PARAM_STR_ARRAY;
                }
            } else {
                                                $estruturaFiltersMeta = $this->buildEstruturaFilters($filters, $params, 'e1', 'meta');
                $estruturaFiltersRealizados = $this->buildEstruturaFilters($filters, $params, 'e2', 'realizados');
                $estruturaFiltersPontos = $this->buildEstruturaFilters($filters, $params, 'e3', 'pontos');
                $metaFilter .= $estruturaFiltersMeta;
                $realizadosFilter .= $estruturaFiltersRealizados;
                $pontosFilter .= $estruturaFiltersPontos;
            }
            
                                    $subindicador = $filters->getSubindicador();
            $indicador = $filters->getIndicador();
            $familia = $filters->getFamilia();
            
            if ($subindicador !== null && $subindicador !== '') {
                                $produtoFilter .= " AND dp.subindicador_id = :subindicadorId";
                $params['subindicadorId'] = $subindicador;
            } elseif ($indicador !== null && $indicador !== '') {
                                $produtoFilter .= " AND dp.indicador_id = :indicadorId";
                $params['indicadorId'] = $indicador;
            } elseif ($familia !== null && $familia !== '') {
                                $produtoFilter .= " AND dp.familia_id = :familiaId";
                $params['familiaId'] = $familia;
            }
            
                                    $status = $filters->getStatus();
            if ($status !== null && $status !== '' && $status !== '03') {
                                if ($status === '01') {
                    $produtoFilter .= " AND (CASE 
                        WHEN COALESCE(fm.total_meta, 0) > 0 
                        THEN CASE 
                            WHEN COALESCE(fr.total_realizado, 0) / fm.total_meta >= 1 
                            THEN 1 
                            ELSE 0 
                        END
                        ELSE 0
                    END) = 1";
                } elseif ($status === '02') {
                    $produtoFilter .= " AND (CASE 
                        WHEN COALESCE(fm.total_meta, 0) > 0 
                        THEN CASE 
                            WHEN COALESCE(fr.total_realizado, 0) / fm.total_meta >= 1 
                            THEN 1 
                            ELSE 0 
                        END
                        ELSE 0
                    END) = 0";
                }
            }
        }

        $sql = "SELECT 
                    dp.id,
                    dp.familia_id as id_familia,
                    f.nm_familia as familia,
                    dp.indicador_id as id_indicador,
                    i.nm_indicador as indicador,
                    dp.subindicador_id as id_subindicador,
                    s.nm_subindicador as subindicador,
                    dp.metrica,
                    dp.peso,
                    COALESCE(fm.total_meta, 0) AS meta,
                    COALESCE(fr.total_realizado, 0) AS realizado,
                    COALESCE(fp.total_pontos, 0) AS pontos,
                    COALESCE(fp.total_meta_pontos, 0) AS pontos_meta,
                    COALESCE(fr.total_realizado / NULLIF(fm.total_meta, 0), 0) AS ating,
                    CASE 
                        WHEN COALESCE(fm.total_meta, 0) > 0 
                        THEN CASE 
                            WHEN COALESCE(fr.total_realizado, 0) / fm.total_meta >= 1 
                            THEN 1 
                            ELSE 0 
                        END
                        ELSE 0
                    END AS atingido,
                    fr.ultima_atualizacao
                FROM {$dProdutosTable} AS dp
                LEFT JOIN {$familiaTable} AS f ON f.id = dp.familia_id
                LEFT JOIN {$indicadorTable} AS i ON i.id = dp.indicador_id
                LEFT JOIN {$subindicadorTable} AS s ON s.id = dp.subindicador_id
                LEFT JOIN (
                    " . $this->buildMetaSubquery($fMetaTable, $dEstruturaTable, $metaFilter, $filters) . "
                ) AS fm ON fm.produto_id = dp.id
                LEFT JOIN (
                    " . $this->buildRealizadosSubquery($fRealizadosTable, $dEstruturaTable, $realizadosFilter, $filters) . "
                ) AS fr ON fr.produto_id = dp.id
                LEFT JOIN (
                    " . $this->buildPontosSubquery($fPontosTable, $dEstruturaTable, $pontosFilter, $filters) . "
                ) AS fp ON fp.produto_id = dp.id
                WHERE 1=1 {$produtoFilter}
                ORDER BY f.nm_familia ASC, i.nm_indicador ASC, s.nm_subindicador ASC";

        $connection = $this->getEntityManager()->getConnection();
        $result = $connection->executeQuery($sql, $params, $types);
        
                $produtos = [];
        while ($row = $result->fetchAssociative()) {
            $produtos[] = $row;
        }
        $result->free();

        if (empty($produtos)) {
            return [];
        }

                $produtoIds = array_unique(array_filter(array_map(function($produto) {
            return $produto['id'] ?? null;
        }, $produtos)));

        if (empty($produtoIds)) {
            return [];
        }

                $realizadosMensais = $this->getRealizadosMensais($produtoIds, $filters);
        $metasMensais = $this->getMetasMensais($produtoIds, $filters);

                return $this->combineProdutosDataMonthly($produtos, $realizadosMensais, $metasMensais);
    }

    
    public function findVariavel(?FilterDTO $filters = null): array
    {
        $fVariavelTable = $this->getTableName(FVariavel::class);
        $dCalendarioTable = $this->getTableName(DCalendario::class);
        $dEstruturaTable = $this->getTableName(DEstrutura::class);
        $segmentoTable = $this->getTableName(Segmento::class);
        $diretoriaTable = $this->getTableName(Diretoria::class);
        $regionalTable = $this->getTableName(Regional::class);
        $agenciaTable = $this->getTableName(Agencia::class);

        $params = [];
        $types = [];
        $whereClause = '';

        if ($filters) {
                                    $gerente = $filters->getGerente();
            $gerenteGestao = $filters->getGerenteGestao();
            
            if ($gerente === null || $gerente === '') {
                if ($gerenteGestao === null || $gerenteGestao === '') {
                                        $estruturaFilters = $this->buildEstruturaFilters($filters, $params, 'e');
                    if ($estruturaFilters) {
                        $whereClause .= $estruturaFilters;
                    }
                }
            }

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

            if ($gerente !== null && $gerente !== '') {
                                $gerenteFuncional = $this->getFuncionalFromIdOrFuncional($gerente, Cargo::GERENTE);
                if ($gerenteFuncional) {
                    $whereClause .= " AND v.funcional = :gerenteFuncional";
                    $params['gerenteFuncional'] = $gerenteFuncional;
                }
            } elseif ($gerenteGestao !== null && $gerenteGestao !== '') {
                                $gerentesFuncionais = $this->getGerentesFuncionaisByGerenteGestao($gerenteGestao);
                if ($gerentesFuncionais) {
                                                            $whereClause .= " AND v.funcional IN (:gerenteGestaoFuncionais)";
                    $params['gerenteGestaoFuncionais'] = $gerentesFuncionais;
                    $types['gerenteGestaoFuncionais'] = Connection::PARAM_STR_ARRAY;
                }
            }
        }

        $sql = "SELECT 
                    v.id as registro_id,
                    v.funcional,
                    v.meta as variavel_meta,
                    v.variavel as variavel_real,
                    c.data as dt_atualizacao,
                    c.data as data,
                    c.data as competencia,
                    e.nome as nome_funcional,
                    seg.nome as segmento,
                    CAST(e.segmento_id AS CHAR) as segmento_id,
                    d.nome as diretoria_nome,
                    CAST(e.diretoria_id AS CHAR) as diretoria_id,
                    r.nome as regional_nome,
                    CAST(e.regional_id AS CHAR) as gerencia_id,
                    a.nome as agencia_nome,
                    CAST(e.agencia_id AS CHAR) as agencia_id
                FROM {$fVariavelTable} AS v
                LEFT JOIN {$dCalendarioTable} AS c ON c.data = v.dt_atualizacao
                LEFT JOIN {$dEstruturaTable} AS e ON e.funcional = v.funcional
                LEFT JOIN {$segmentoTable} AS seg ON seg.id = e.segmento_id
                LEFT JOIN {$diretoriaTable} AS d ON d.id = e.diretoria_id
                LEFT JOIN {$regionalTable} AS r ON r.id = e.regional_id
                LEFT JOIN {$agenciaTable} AS a ON a.id = e.agencia_id
                WHERE 1=1 {$whereClause}
                ORDER BY c.data DESC";

        $connection = $this->getEntityManager()->getConnection();
        $result = $connection->executeQuery($sql, $params, $types);
        
                $rows = [];
        while ($row = $result->fetchAssociative()) {
            $rows[] = $row;
        }
        $result->free();

                        $variaveis = [];
        foreach ($rows as $row) {
            $data = $row['data'] ?? null;
            if ($data instanceof \DateTimeInterface) {
                $data = $data->format('Y-m-d');
            } elseif (is_string($data)) {
                            } else {
                $data = null;
            }

            $variaveis[] = array_merge($row, [
                'data' => $data,
                'competencia' => $data,
            ]);
                                }

        return $variaveis;
    }

    
    public function findCalendario(): array
    {
        $calendarios = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('c.data, c.ano, c.mes, c.mesNome, c.dia, c.diaDaSemana, c.semana, c.trimestre, c.semestre, c.ehDiaUtil')
            ->from(DCalendario::class, 'c')
            ->orderBy('c.data', 'ASC')
            ->getQuery()
            ->getArrayResult();
        
        $result = [];
        
        foreach ($calendarios as $row) {
            $data = $row['data'];
            if ($data instanceof \DateTimeInterface) {
                $data = $data->format('Y-m-d');
            } elseif (!is_string($data)) {
                $data = null;
            }
            
            $result[] = [
                'data' => $data,
                'ano' => $row['ano'] ?? null,
                'mes' => $row['mes'] ?? null,
                'mesNome' => $row['mesNome'] ?? null,
                'dia' => $row['dia'] ?? null,
                'diaDaSemana' => $row['diaDaSemana'] ?? null,
                'semana' => $row['semana'] ?? null,
                'trimestre' => $row['trimestre'] ?? null,
                'semestre' => $row['semestre'] ?? null,
                'ehDiaUtil' => isset($row['ehDiaUtil']) ? ($row['ehDiaUtil'] ? 'Sim' : 'Não') : null,
            ];
        }
        
        return $result;
    }

    
    private function getRealizadosAgregados(array $produtoIds, ?FilterDTO $filters): array
    {
        if (empty($produtoIds)) {
            return [];
        }
        
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('IDENTITY(r.produto) as produto_id')
            ->addSelect('SUM(r.realizado) as realizado_total')
            ->addSelect('MAX(c.data) as ultima_atualizacao')
            ->from(FRealizados::class, 'r')
            ->leftJoin('r.dataRealizado', 'c')
            ->where('r.produto IN (:produtoIds)')
            ->andWhere('r.produto IS NOT NULL')
            ->setParameter('produtoIds', $produtoIds)
            ->groupBy('r.produto');
        
                $dataInicio = $filters ? $filters->getDataInicio() : null;
        $dataFim = $filters ? $filters->getDataFim() : null;
        
        if ($dataInicio) {
            $qb->andWhere('c.data >= :dataInicio')
               ->setParameter('dataInicio', $dataInicio);
        }
        
        if ($dataFim) {
            $qb->andWhere('c.data <= :dataFim')
               ->setParameter('dataFim', $dataFim);
        }
        
        $rows = $qb->getQuery()->getArrayResult();
        
        $result = [];
        foreach ($rows as $row) {
            $produtoId = (string)$row['produto_id'];
            $result[$produtoId] = [
                'realizado' => (float)($row['realizado_total'] ?? 0),
                'ultima_atualizacao' => $row['ultima_atualizacao'] ?? null
            ];
        }
        
        return $result;
    }

    
    private function getMetasAgregadas(array $produtoIds, ?FilterDTO $filters): array
    {
        if (empty($produtoIds)) {
            return [];
        }
        
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('IDENTITY(m.produto) as produto_id')
            ->addSelect('SUM(m.metaMensal) as meta_total')
            ->from(FMeta::class, 'm')
            ->where('m.produto IN (:produtoIds)')
            ->setParameter('produtoIds', $produtoIds)
            ->groupBy('m.produto');
        
                $dataInicio = $filters ? $filters->getDataInicio() : null;
        $dataFim = $filters ? $filters->getDataFim() : null;
        
        if ($dataInicio) {
            $qb->andWhere('m.dataMeta >= :dataInicio')
               ->setParameter('dataInicio', $dataInicio);
        }
        
        if ($dataFim) {
            $qb->andWhere('m.dataMeta <= :dataFim')
               ->setParameter('dataFim', $dataFim);
        }
        
        $rows = $qb->getQuery()->getArrayResult();
        
        $result = [];
        foreach ($rows as $row) {
            $produtoId = (string)$row['produto_id'];
            $result[$produtoId] = (float)($row['meta_total'] ?? 0);
        }
        
        return $result;
    }

    
    private function getPontosAgregados(array $produtoIds, ?FilterDTO $filters): array
    {
        if (empty($produtoIds)) {
            return [];
        }
        
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->select('IDENTITY(p.produto) as produto_id')
            ->addSelect('SUM(p.realizado) as pontos_realizado')
            ->addSelect('SUM(p.meta) as pontos_meta')
            ->from(FPontos::class, 'p')
            ->where('p.produto IN (:produtoIds)')
            ->setParameter('produtoIds', $produtoIds)
            ->groupBy('p.produto');
        
                $dataInicio = $filters ? $filters->getDataInicio() : null;
        $dataFim = $filters ? $filters->getDataFim() : null;
        
        if ($dataInicio) {
            $qb->andWhere('p.dataRealizado >= :dataInicio')
               ->setParameter('dataInicio', $dataInicio);
        }
        
        if ($dataFim) {
            $qb->andWhere('p.dataRealizado <= :dataFim')
               ->setParameter('dataFim', $dataFim);
        }
        
        $rows = $qb->getQuery()->getArrayResult();
        
        $result = [];
        foreach ($rows as $row) {
            $produtoId = (string)$row['produto_id'];
            $result[$produtoId] = [
                'pontos' => (float)($row['pontos_realizado'] ?? 0),
                'pontos_meta' => (float)($row['pontos_meta'] ?? 0)
            ];
        }
        
        return $result;
    }

    
    private function getVariavelAgregada(array $produtoIds, ?FilterDTO $filters): array
    {
                        return [];
    }

    
    private function combineProdutosData(
        array $produtos,
        array $realizados,
        array $metas,
        array $pontos,
        array $variavel
    ): array {
        $result = [];
        
        foreach ($produtos as $produto) {
            $produtoId = (string)($produto['id'] ?? '');

            $realizadoData = $realizados[$produtoId] ?? null;
            $metaTotal = $metas[$produtoId] ?? 0;
            $pontosData = $pontos[$produtoId] ?? null;
            $variavelData = $variavel[$produtoId] ?? null;

            $realizadoTotal = $realizadoData['realizado'] ?? 0;
            $pontosRealizado = $pontosData['pontos'] ?? 0;
            $pontosMeta = $pontosData['pontos_meta'] ?? ($produto['peso'] ?? 0);

                        $percentualMetric = $this->isPercentualMetric($produto['metrica'] ?? '');
            $atingRatio = $metaTotal > 0 ? ($realizadoTotal / $metaTotal) : 0;
            $ating = $percentualMetric ? $atingRatio * 100 : $atingRatio;
            $atingido = $atingRatio >= 1 || ($pontosMeta > 0 && ($pontosRealizado / $pontosMeta) >= 1);
            
            $result[] = [
                'id' => $produtoId,
                'id_indicador' => (string)($produto['id_indicador'] ?? ''),
                'indicador' => $produto['indicador'] ?? '',
                'id_familia' => (string)($produto['id_familia'] ?? ''),
                'familia' => $produto['familia'] ?? '',
                'id_subindicador' => $produto['id_subindicador'] ? (string)$produto['id_subindicador'] : null,
                'subindicador' => $produto['subindicador'] ?? null,
                'metrica' => $produto['metrica'] ?? 'valor',
                'peso' => (float)($produto['peso'] ?? 0),
                'meta' => $metaTotal,
                'realizado' => $realizadoTotal,
                'pontos' => $pontosRealizado,
                'pontos_meta' => $pontosMeta,
                'variavel_meta' => $variavelData['variavel_meta'] ?? 0,
                'variavel_realizado' => $variavelData['variavel_realizado'] ?? 0,
                'ating' => $ating,
                'atingido' => $atingido,
                'ultima_atualizacao' => $realizadoData['ultima_atualizacao'] ?? null
            ];
        }
        
        return $result;
    }

    
    private function formatProdutosForCards(array $produtos): array
    {
        return array_map(function($produto) {
            return [
                'id' => (string)($produto['id'] ?? ''),
                'id_indicador' => (string)($produto['id_indicador'] ?? ''),
                'indicador' => $produto['indicador'] ?? '',
                'id_familia' => (string)($produto['id_familia'] ?? ''),
                'familia' => $produto['familia'] ?? '',
                'id_subindicador' => $produto['id_subindicador'] ? (string)$produto['id_subindicador'] : null,
                'subindicador' => $produto['subindicador'] ?? null,
                'metrica' => $produto['metrica'] ?? 'valor',
                'peso' => (float)($produto['peso'] ?? 0),
                'meta' => 0,
                'realizado' => 0,
                'pontos' => 0,
                'pontos_meta' => (float)($produto['peso'] ?? 0),
                'variavel_meta' => 0,
                'variavel_realizado' => 0,
                'ating' => 0,
                'atingido' => false,
                'ultima_atualizacao' => null
            ];
        }, $produtos);
    }

    
    private function getRealizadosMensais(array $produtoIds, ?FilterDTO $filters): array
    {
        if (empty($produtoIds)) {
            return [];
        }
        
        $fRealizadosTable = $this->getTableName(FRealizados::class);
        $dCalendarioTable = $this->getTableName(DCalendario::class);
        $dEstruturaTable = $this->getTableName(DEstrutura::class);
        $placeholders = implode(',', array_fill(0, count($produtoIds), '?'));
        $sql = "SELECT 
                    r.produto_id,
                    DATE_FORMAT(c.data, '%Y-%m') as mes,
                    SUM(r.realizado) as realizado_total,
                    MAX(c.data) as ultima_atualizacao
                FROM {$fRealizadosTable} as r
                LEFT JOIN {$dCalendarioTable} as c ON c.data = r.data_realizado
                LEFT JOIN {$dEstruturaTable} as e ON e.funcional = r.funcional
                WHERE r.produto_id IN ({$placeholders})
                AND r.produto_id IS NOT NULL";
        
        $params = $produtoIds;
        
                $dataInicio = $filters ? $filters->getDataInicio() : null;
        $dataFim = $filters ? $filters->getDataFim() : null;
        
        if ($dataInicio) {
            $sql .= " AND c.data >= ?";
            $params[] = $dataInicio;
        }
        
        if ($dataFim) {
            $sql .= " AND c.data <= ?";
            $params[] = $dataFim;
        }
        
                if ($filters) {
            $estruturaParams = [];
            $estruturaFilters = $this->buildEstruturaFilters($filters, $estruturaParams, 'e');
            if ($estruturaFilters) {
                                $estruturaSql = $estruturaFilters;
                foreach ($estruturaParams as $key => $value) {
                    $estruturaSql = str_replace(":{$key}", '?', $estruturaSql);
                    $params[] = $value;
                }
                $sql .= $estruturaSql;
            }
        }
        
        $sql .= " GROUP BY r.produto_id, DATE_FORMAT(c.data, '%Y-%m')";
        
        $connection = $this->getEntityManager()->getConnection();
        $result = $connection->executeQuery($sql, $params);
        
                $rows = [];
        while ($row = $result->fetchAssociative()) {
            $rows[] = $row;
        }
        $result->free();
        
        $result = [];
        $ultimaAtualizacaoMap = [];
        foreach ($rows as $row) {
            $produtoId = (string)$row['produto_id'];
            $mes = $row['mes'] ?? '';
            if (!isset($result[$produtoId])) {
                $result[$produtoId] = [];
            }
            $result[$produtoId][$mes] = (float)($row['realizado_total'] ?? 0);
            
                        if ($row['ultima_atualizacao']) {
                if (!isset($ultimaAtualizacaoMap[$produtoId]) || 
                    $row['ultima_atualizacao'] > $ultimaAtualizacaoMap[$produtoId]) {
                    $ultimaAtualizacaoMap[$produtoId] = $row['ultima_atualizacao'];
                }
            }
        }
        
                foreach ($ultimaAtualizacaoMap as $produtoId => $ultimaAtualizacao) {
            if (!isset($result[$produtoId]['_ultima_atualizacao'])) {
                $result[$produtoId]['_ultima_atualizacao'] = $ultimaAtualizacao;
            }
        }
        
        return $result;
    }

    
    private function getMetasMensais(array $produtoIds, ?FilterDTO $filters): array
    {
        if (empty($produtoIds)) {
            return [];
        }
        
        $fMetaTable = $this->getTableName(FMeta::class);
        $dCalendarioTable = $this->getTableName(DCalendario::class);
        $dEstruturaTable = $this->getTableName(DEstrutura::class);
        $placeholders = implode(',', array_fill(0, count($produtoIds), '?'));
        $sql = "SELECT 
                    m.produto_id,
                    DATE_FORMAT(c.data, '%Y-%m') as mes,
                    SUM(m.meta_mensal) as meta_total
                FROM {$fMetaTable} as m
                LEFT JOIN {$dCalendarioTable} as c ON c.data = m.data_meta
                LEFT JOIN {$dEstruturaTable} as e ON e.funcional = m.funcional
                WHERE m.produto_id IN ({$placeholders})";
        
        $params = $produtoIds;
        
                $dataInicio = $filters ? $filters->getDataInicio() : null;
        $dataFim = $filters ? $filters->getDataFim() : null;
        
        if ($dataInicio) {
            $sql .= " AND c.data >= ?";
            $params[] = $dataInicio;
        }
        
        if ($dataFim) {
            $sql .= " AND c.data <= ?";
            $params[] = $dataFim;
        }
        
                if ($filters) {
            $estruturaParams = [];
            $estruturaFilters = $this->buildEstruturaFilters($filters, $estruturaParams, 'e');
            if ($estruturaFilters) {
                                $estruturaSql = $estruturaFilters;
                foreach ($estruturaParams as $key => $value) {
                    $estruturaSql = str_replace(":{$key}", '?', $estruturaSql);
                    $params[] = $value;
                }
                $sql .= $estruturaSql;
            }
        }
        
        $sql .= " GROUP BY m.produto_id, DATE_FORMAT(c.data, '%Y-%m')";
        
        $connection = $this->getEntityManager()->getConnection();
        $result = $connection->executeQuery($sql, $params);
        
                $rows = [];
        while ($row = $result->fetchAssociative()) {
            $rows[] = $row;
        }
        $result->free();
        
        $result = [];
        foreach ($rows as $row) {
            $produtoId = (string)$row['produto_id'];
            $mes = $row['mes'] ?? '';
            if (!isset($result[$produtoId])) {
                $result[$produtoId] = [];
            }
            $result[$produtoId][$mes] = (float)($row['meta_total'] ?? 0);
        }
        
        return $result;
    }

    
    private function combineProdutosDataMonthly(
        array $produtos,
        array $realizadosMensais,
        array $metasMensais
    ): array {
        $result = [];
        
        foreach ($produtos as $produto) {
            $produtoId = (string)($produto['id'] ?? '');
            
            $realizadosMes = $realizadosMensais[$produtoId] ?? [];
            $metasMes = $metasMensais[$produtoId] ?? [];
            
                        $ultimaAtualizacao = $realizadosMes['_ultima_atualizacao'] ?? null;
            unset($realizadosMes['_ultima_atualizacao']);
            
                        $realizadoTotal = array_sum($realizadosMes);
            $metaTotal = array_sum($metasMes);
            $percentualMetric = $this->isPercentualMetric($produto['metrica'] ?? '');
            $atingRatio = $metaTotal > 0 ? ($realizadoTotal / $metaTotal) : 0;
            $ating = $percentualMetric ? $atingRatio * 100 : $atingRatio;
            
                        $meses = array_unique(array_merge(array_keys($realizadosMes), array_keys($metasMes)));
            $dadosMensais = [];
            
            foreach ($meses as $mes) {
                $meta = $metasMes[$mes] ?? 0;
                $realizado = $realizadosMes[$mes] ?? 0;
                $atingMes = $meta > 0 ? ($realizado / $meta) : 0;

                $dadosMensais[] = [
                    'mes' => $mes,
                    'meta' => $meta,
                    'realizado' => $realizado,
                    'atingimento' => $atingMes * 100
                ];
            }
            
                        $peso = $produto['peso'] ?? null;
            if ($peso === null || $peso === '') {
                $peso = 0;
            } else {
                $peso = (float)$peso;
            }
            
                        $pontos = $produto['pontos'] ?? null;
            if ($pontos === null || $pontos === '') {
                $pontos = 0;
            } else {
                $pontos = (float)$pontos;
            }
            
                        $pontosMeta = $produto['pontos_meta'] ?? null;
            if ($pontosMeta === null || $pontosMeta === '') {
                $pontosMeta = $peso;             } else {
                $pontosMeta = (float)$pontosMeta;
            }
            
            $result[] = [
                'id' => $produtoId,
                'id_indicador' => (string)($produto['id_indicador'] ?? ''),
                'indicador' => $produto['indicador'] ?? '',
                'id_familia' => (string)($produto['id_familia'] ?? ''),
                'familia' => $produto['familia'] ?? '',
                'id_subindicador' => $produto['id_subindicador'] ? (string)$produto['id_subindicador'] : null,
                'subindicador' => $produto['subindicador'] ?? null,
                'metrica' => $produto['metrica'] ?? 'valor',
                'peso' => $peso,
                'meta' => $metaTotal,
                'realizado' => $realizadoTotal,
                'pontos' => $pontos,
                'pontos_meta' => $pontosMeta,
                'ating' => $ating,
                'atingido' => $atingRatio >= 1,
                'ultima_atualizacao' => $ultimaAtualizacao,
                'meses' => $dadosMensais
            ];
        }
        
        return $result;
    }


    private function isPercentualMetric(?string $metric): bool
    {
        if ($metric === null || $metric === '') {
            return false;
        }

        $metricLower = strtolower(trim($metric));

        return in_array($metricLower, ['perc', 'percentual', 'percent', '%'], true);
    }


    private function getTableName(string $entityClass): string
    {
        return $this->getEntityManager()
            ->getClassMetadata($entityClass)
            ->getTableName();
    }

    
    private function buildEstruturaFilters(?FilterDTO $filters, array &$params, string $prefix = 'e', string $suffix = ''): string
    {
        if (!$filters) {
            return '';
        }

        $filtersSql = '';

        $gerente = $filters->getGerente();
        $gerenteGestao = $filters->getGerenteGestao();
        $agencia = $filters->getAgencia();
        $regional = $filters->getRegional();
        $diretoria = $filters->getDiretoria();
        $segmento = $filters->getSegmento();

                        if ($gerente !== null && $gerente !== '') {
                                                return '';
        } elseif ($gerenteGestao !== null && $gerenteGestao !== '') {
                                    return '';
        } elseif ($agencia !== null && $agencia !== '') {
                        $key = "agenciaId" . ($suffix ? "_{$suffix}" : '');
            $filtersSql .= " AND {$prefix}.agencia_id = :{$key}";
            $params[$key] = $agencia;
        } elseif ($regional !== null && $regional !== '') {
                        $key = "regionalId" . ($suffix ? "_{$suffix}" : '');
            $filtersSql .= " AND {$prefix}.regional_id = :{$key}";
            $params[$key] = $regional;
        } elseif ($diretoria !== null && $diretoria !== '') {
                        $key = "diretoriaId" . ($suffix ? "_{$suffix}" : '');
            $filtersSql .= " AND {$prefix}.diretoria_id = :{$key}";
            $params[$key] = $diretoria;
        } elseif ($segmento !== null && $segmento !== '') {
                        $key = "segmentoId" . ($suffix ? "_{$suffix}" : '');
            $filtersSql .= " AND {$prefix}.segmento_id = :{$key}";
            $params[$key] = $segmento;
        }

        return $filtersSql;
    }

    
    private function needsEstruturaJoin(?FilterDTO $filters): bool
    {
        if (!$filters) {
            return false;
        }

        $gerente = $filters->getGerente();
        $gerenteGestao = $filters->getGerenteGestao();
        
                if (($gerente !== null && $gerente !== '') || ($gerenteGestao !== null && $gerenteGestao !== '')) {
            return false;
        }

                return ($filters->getAgencia() !== null && $filters->getAgencia() !== '') ||
               ($filters->getRegional() !== null && $filters->getRegional() !== '') ||
               ($filters->getDiretoria() !== null && $filters->getDiretoria() !== '') ||
               ($filters->getSegmento() !== null && $filters->getSegmento() !== '');
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

    private function getGerentesFuncionaisByGerenteGestao($gerenteGestao): array
    {
        if ($gerenteGestao === null || $gerenteGestao === '') {
            return [];
        }

        $dEstruturaTable = $this->getTableName(DEstrutura::class);
        $conn = $this->getEntityManager()->getConnection();

        $gestaoFilterField = is_numeric($gerenteGestao) ? 'id' : 'funcional';
        $gestaoFilterValue = is_numeric($gerenteGestao) ? (int)$gerenteGestao : (string)$gerenteGestao;

        $gestaoRow = $conn->executeQuery(
            "SELECT agencia_id FROM {$dEstruturaTable} WHERE {$gestaoFilterField} = :value AND cargo_id = :cargoId LIMIT 1",
            [
                'value' => $gestaoFilterValue,
                'cargoId' => Cargo::GERENTE_GESTAO
            ]
        )->fetchAssociative();

        if (!$gestaoRow || empty($gestaoRow['agencia_id'])) {
            return [];
        }

        $result = $conn->executeQuery(
            "SELECT funcional FROM {$dEstruturaTable} WHERE cargo_id = :cargoId AND agencia_id = :agenciaId AND funcional IS NOT NULL",
            [
                'cargoId' => Cargo::GERENTE,
                'agenciaId' => $gestaoRow['agencia_id']
            ]
        );

        $rows = $result->fetchAllAssociative();
        $result->free();

        return array_values(array_filter(array_column($rows, 'funcional')));
    }
}

