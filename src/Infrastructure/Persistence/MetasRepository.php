<?php

namespace App\Infrastructure\Persistence;

use PDO;
use App\Domain\DTO\FilterDTO;
use App\Domain\DTO\MetaDTO;
use App\Domain\Enum\Tables;
use App\Infrastructure\Helpers\DateFormatter;
use App\Infrastructure\Helpers\ValueFormatter;

/**
 * Repositório para buscar todos os registros de metas com filtros opcionais
 */
class MetasRepository extends BaseRepository
{
    /**
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, MetaDTO::class);
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
                    m.id_familia AS familia_id,
                    m.id_indicador AS indicador_id,
                    m.id_subindicador AS subindicador_id,
                    m.segmento_id,
                    m.diretoria_id,
                    m.gerencia_regional_id,
                    m.agencia_id,
                    COALESCE(u.segmento, '') AS segmento,
                    COALESCE(u.diretoria, '') AS diretoria_nome,
                    COALESCE(u.regional, '') AS gerencia_regional_nome,
                    COALESCE(u.regional, '') AS regional_nome,
                    COALESCE(u.agencia, '') AS agencia_nome,
                    COALESCE(gg.funcional, '') AS gerente_gestao_id,
                    COALESCE(gg.nome, '') AS gerente_gestao_nome,
                    COALESCE(u.funcional, '') AS gerente_id,
                    COALESCE(u.nome, '') AS gerente_nome,
                    COALESCE(p.familia, '') AS familia_nome,
                    COALESCE(CAST(p.id_indicador AS CHAR), '') AS id_indicador,
                    COALESCE(p.indicador, '') AS ds_indicador,
                    COALESCE(p.subindicador, '') AS subproduto,
                    COALESCE(CAST(p.id_subindicador AS CHAR), '0') AS id_subindicador,
                    m.meta_mensal
                FROM " . Tables::F_META . " m
                    LEFT JOIN d_estrutura u
                        ON u.funcional = m.funcional
                        AND u.id_agencia = m.agencia_id
                        AND u.id_regional = m.gerencia_regional_id
                        AND u.id_diretoria = m.diretoria_id
                        AND u.id_segmento = m.segmento_id
                        AND u.id_cargo = 1
                    LEFT JOIN d_estrutura gg
                        ON gg.id_agencia = u.id_agencia
                        AND gg.id_regional = u.id_regional
                        AND gg.id_diretoria = u.id_diretoria
                        AND gg.id_segmento = u.id_segmento
                        AND gg.id_cargo = 3
                LEFT JOIN " . Tables::D_PRODUTOS . " p ON p.id_indicador = m.id_indicador
                    AND (p.id_subindicador = m.id_subindicador OR (p.id_subindicador IS NULL AND m.id_subindicador IS NULL))
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
            $sql .= " AND m.segmento_id = :segmento";
            $params[':segmento'] = $filters->getSegmento();
        }

        if ($filters->getDiretoria() !== null) {
            $sql .= " AND m.diretoria_id = :diretoria";
            $params[':diretoria'] = $filters->getDiretoria();
        }

        if ($filters->getRegional() !== null) {
            $sql .= " AND m.gerencia_regional_id = :regional";
            $params[':regional'] = $filters->getRegional();
        }

        if ($filters->getAgencia() !== null) {
            $sql .= " AND m.agencia_id = :agencia";
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
            $sql .= " AND m.id_familia = :familia";
            $params[':familia'] = $filters->getFamilia();
        }

        if ($filters->getIndicador() !== null) {
            $sql .= " AND m.id_indicador = :indicador";
            $params[':indicador'] = $filters->getIndicador();
        }

        if ($filters->getSubindicador() !== null) {
            $sql .= " AND m.id_subindicador = :subindicador";
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

