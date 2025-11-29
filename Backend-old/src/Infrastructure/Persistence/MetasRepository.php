<?php

namespace App\Infrastructure\Persistence;

use App\Domain\DTO\FilterDTO;
use App\Domain\DTO\MetaDTO;
use App\Domain\Enum\Tables;
use App\Domain\Enum\Cargo;
use App\Infrastructure\Helpers\DateFormatter;
use App\Infrastructure\Helpers\ValueFormatter;

/**
 * Repositório para buscar todos os registros de metas com filtros opcionais
 */
class MetasRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(MetaDTO::class);
    }

    /**
     * Retorna o SELECT completo da consulta
     * @return string
     */
    public function baseSelect(): string
    {
        return "SELECT DISTINCT
                    m.id AS registro_id,
                    m.data_meta AS data,
                    m.data_meta AS competencia,
                    m.funcional,
                    m.meta_mensal,
                    dp.familia_id,
                    dp.indicador_id,
                    dp.subindicador_id,
                    e.segmento_id,
                    e.diretoria_id,
                    e.regional_id AS gerencia_regional_id,
                    e.agencia_id,
                    COALESCE(seg.nome, '') AS segmento,
                    COALESCE(dir.nome, '') AS diretoria_nome,
                    COALESCE(reg.nome, '') AS gerencia_regional_nome,
                    COALESCE(reg.nome, '') AS regional_nome,
                    COALESCE(ag.nome, '') AS agencia_nome,
                    COALESCE(gg.funcional, '') AS gerente_gestao_id,
                    COALESCE(gg.nome, '') AS gerente_gestao_nome,
                    COALESCE(e.funcional, '') AS gerente_id,
                    COALESCE(e.nome, '') AS gerente_nome,
                    COALESCE(fam.nm_familia, '') AS familia_nome,
                    COALESCE(CAST(dp.indicador_id AS CHAR), '') AS id_indicador,
                    COALESCE(ind.nm_indicador, '') AS ds_indicador,
                    COALESCE(sub.nm_subindicador, '') AS subproduto,
                    COALESCE(CAST(COALESCE(dp.subindicador_id, 0) AS CHAR), '0') AS id_subindicador,
                    m.meta_mensal
                FROM " . Tables::F_META . " m
                LEFT JOIN " . Tables::D_PRODUTOS . " dp ON dp.id = m.produto_id
                LEFT JOIN familia fam ON fam.id = dp.familia_id
                LEFT JOIN indicador ind ON ind.id = dp.indicador_id
                LEFT JOIN subindicador sub ON sub.id = dp.subindicador_id
                LEFT JOIN " . Tables::D_ESTRUTURA . " e ON e.funcional = m.funcional
                LEFT JOIN segmentos seg ON seg.id = e.segmento_id
                LEFT JOIN diretorias dir ON dir.id = e.diretoria_id
                LEFT JOIN regionais reg ON reg.id = e.regional_id
                LEFT JOIN agencias ag ON ag.id = e.agencia_id
                LEFT JOIN d_estrutura gg
                    ON gg.agencia_id = e.agencia_id
                    AND gg.cargo_id = " . Cargo::GERENTE_GESTAO . "
                WHERE 1=1";
    }

    /**
     * Constrói os filtros WHERE baseado no FilterDTO
     * @param FilterDTO|null $filters
     * @return array ['sql' => string, 'params' => array]
     */
    public function builderFilter(FilterDTO $filters = null): array
    {
        $sql = "";
        $params = [];

        if ($filters === null || !$filters->hasAnyFilter()) {
            return ['sql' => $sql, 'params' => $params];
        }

        if ($filters->getSegmento() !== null) {
            $sql .= " AND e.segmento_id = :segmento";
            $params[':segmento'] = $filters->getSegmento();
        }

        if ($filters->getDiretoria() !== null) {
            $sql .= " AND e.diretoria_id = :diretoria";
            $params[':diretoria'] = $filters->getDiretoria();
        }

        if ($filters->getRegional() !== null) {
            $sql .= " AND e.regional_id = :regional";
            $params[':regional'] = $filters->getRegional();
        }

        if ($filters->getAgencia() !== null) {
            $sql .= " AND e.agencia_id = :agencia";
            $params[':agencia'] = $filters->getAgencia();
        }

        if ($filters->getGerenteGestao() !== null) {
            $sql .= " AND gg.funcional = :gerente_gestao";
            $params[':gerente_gestao'] = $filters->getGerenteGestao();
        }

        if ($filters->getGerente() !== null) {
            $sql .= " AND m.funcional = :gerente";
            $params[':gerente'] = $filters->getGerente();
        }

        if ($filters->getFamilia() !== null) {
            $sql .= " AND dp.familia_id = :familia";
            $params[':familia'] = $filters->getFamilia();
        }

        if ($filters->getIndicador() !== null) {
            $sql .= " AND dp.indicador_id = :indicador";
            $params[':indicador'] = $filters->getIndicador();
        }

        if ($filters->getSubindicador() !== null) {
            $sql .= " AND dp.subindicador_id = :subindicador";
            $params[':subindicador'] = $filters->getSubindicador();
        }
        
        if ($filters->getDataInicio() !== null) {
            $sql .= " AND m.data_meta >= :dataInicio";
            $params[':dataInicio'] = $filters->getDataInicio();
        }
        if ($filters->getDataFim() !== null) {
            $sql .= " AND m.data_meta <= :dataFim";
            $params[':dataFim'] = $filters->getDataFim();
        }

        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Retorna a cláusula ORDER BY
     * @return string
     */
    protected function getOrderBy(): string
    {
        return "ORDER BY m.data_meta DESC, m.id";
    }

    /**
     * Mapeia um array de resultados para MetaDTO
     * @param array $row
     * @return MetaDTO
     */
    public function mapToDto(array $row): MetaDTO
    {
        $dataIso = DateFormatter::toIsoDate($row['data'] ?? null);
        
        return new MetaDTO(
            (string)($row['registro_id'] ?? null),
            $row['segmento'] ?? null,
            (string)($row['segmento_id'] ?? null),
            (string)($row['diretoria_id'] ?? null),
            $row['diretoria_nome'] ?? null,
            (string)($row['gerencia_regional_id'] ?? null),
            $row['gerencia_regional_nome'] ?? null,
            $row['regional_nome'] ?? null,
            (string)($row['agencia_id'] ?? null),
            $row['agencia_nome'] ?? null,
            $row['gerente_gestao_id'] ?? null,
            $row['gerente_gestao_nome'] ?? null,
            $row['gerente_id'] ?? null,
            $row['gerente_nome'] ?? null,
            (string)($row['familia_id'] ?? null),
            $row['familia_nome'] ?? null,
            $row['id_indicador'] ?? null,
            $row['ds_indicador'] ?? null,
            $row['subproduto'] ?? null,
            $row['id_subindicador'] ?? null,
            (string)($row['familia_id'] ?? null),  
            (string)($row['indicador_id'] ?? null),                                                            
            ValueFormatter::toFloat($row['meta_mensal'] ?? null)  
        );
    }
}

