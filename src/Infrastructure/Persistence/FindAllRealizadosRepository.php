<?php

namespace App\Infrastructure\Persistence;

use PDO;
use App\Domain\DTO\FilterDTO;
use App\Domain\DTO\RealizadoDTO;
use App\Domain\Enum\Cargo;
use App\Domain\Enum\Tables;
use App\Infrastructure\Helpers\DateFormatter;
use App\Infrastructure\Helpers\ValueFormatter;

/**
 * Repositório para buscar todos os registros de realizados com filtros opcionais
 */
class FindAllRealizadosRepository extends BaseRepository
{
    /**
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, RealizadoDTO::class);
    }

    /**
     * Retorna o SELECT completo da consulta
     * @return string
     */
    public function baseSelect(): string
    {
        return "SELECT 
                    r.id AS registro_id,
                    r.data_realizado AS data,
                    r.data_realizado AS competencia,
                    r.funcional,
                    r.realizado AS realizado_mensal,
                    r.familia_id,
                    r.indicador_id,
                    r.subindicador_id,
                    r.segmento_id,
                    r.diretoria_id,
                    r.gerencia_regional_id,
                    r.agencia_id,
                    COALESCE(u.segmento, '') AS segmento,
                    COALESCE(u.diretoria, '') AS diretoria_nome,
                    COALESCE(u.regional, '') AS gerencia_regional_nome,
                    COALESCE(u.regional, '') AS regional_nome,
                    COALESCE(u.agencia, '') AS agencia_nome,
                    COALESCE(u.funcional, '') AS gerente_gestao_id,
                    COALESCE(u.nome, '') AS gerente_gestao_nome,
                    COALESCE(u.funcional, '') AS gerente_id,
                    COALESCE(u.nome, '') AS gerente_nome,
                    COALESCE(p.familia, '') AS familia_nome,
                    COALESCE(CAST(p.id_indicador AS CHAR), '') AS id_indicador,
                    COALESCE(p.indicador, '') AS ds_indicador,
                    COALESCE(p.subindicador, '') AS subproduto,
                    COALESCE(CAST(COALESCE(p.id_subindicador, 0) AS CHAR), '0') AS id_subindicador
                FROM " . Tables::F_REALIZADOS . " r
                LEFT JOIN " . Tables::D_ESTRUTURA . " u ON u.id_segmento = r.segmento_id
                    AND u.id_diretoria = r.diretoria_id
                    AND u.id_regional = r.gerencia_regional_id
                    AND u.id_agencia = r.agencia_id
                    AND u.funcional = r.funcional
                LEFT JOIN " . Tables::D_PRODUTOS . " p ON p.id_indicador = r.indicador_id
                    AND (p.id_subindicador = r.subindicador_id OR (p.id_subindicador IS NULL AND r.subindicador_id IS NULL))
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

        if ($filters->segmento !== null) {
            $sql .= " AND r.segmento_id = :segmento";
            $params[':segmento'] = $filters->segmento;
        }

        if ($filters->diretoria !== null) {
            $sql .= " AND r.diretoria_id = :diretoria";
            $params[':diretoria'] = $filters->diretoria;
        }

        if ($filters->regional !== null) {
            $sql .= " AND r.gerencia_regional_id = :regional";
            $params[':regional'] = $filters->regional;
        }

        if ($filters->agencia !== null) {
            $sql .= " AND r.agencia_id = :agencia";
            $params[':agencia'] = $filters->agencia;
        }

        if ($filters->gerenteGestao !== null) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM " . Tables::D_ESTRUTURA . " ggestao 
                WHERE ggestao.funcional = :gerente_gestao
                AND ggestao.id_cargo = " . Cargo::GERENTE_GESTAO . "
                AND ggestao.id_segmento = r.segmento_id
                AND ggestao.id_diretoria = r.diretoria_id
                AND ggestao.id_regional = r.gerencia_regional_id
                AND ggestao.id_agencia = r.agencia_id
            )";
            $params[':gerente_gestao'] = $filters->gerenteGestao;
        }

        if ($filters->gerente !== null) {
            $sql .= " AND r.funcional = :gerente";
            $params[':gerente'] = $filters->gerente;
        }

        if ($filters->familia !== null) {
            $sql .= " AND r.familia_id = :familia";
            $params[':familia'] = $filters->familia;
        }

        if ($filters->indicador !== null) {
            $sql .= " AND r.indicador_id = :indicador";
            $params[':indicador'] = $filters->indicador;
        }

        if ($filters->subindicador !== null) {
            $sql .= " AND r.subindicador_id = :subindicador";
            $params[':subindicador'] = $filters->subindicador;
        }

        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Retorna a cláusula ORDER BY
     * @return string
     */
    protected function getOrderBy(): string
    {
        return "ORDER BY r.data_realizado DESC, r.id";
    }

    /**
     * Mapeia um array de resultados para RealizadoDTO
     * @param array $row
     * @return RealizadoDTO
     */
    public function mapToDto(array $row): RealizadoDTO
    {
        return new RealizadoDTO(
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
            (string)($row['familia_id'] ?? null),        // familiaCodigo
            (string)($row['indicador_id'] ?? null),      // indicadorCodigo
            null,                                        // carteira
            null,                                        // canalVenda
            null,                                        // tipoVenda
            null,                                        // modalidadePagamento
            DateFormatter::toIsoDate($row['data'] ?? null),
            DateFormatter::toIsoDate($row['competencia'] ?? null),
            ValueFormatter::toFloat($row['realizado_mensal'] ?? null),
            null,                                        // realizado_acumulado
            ValueFormatter::toFloat($row['quantidade'] ?? null),
            ValueFormatter::toFloat($row['variavel_real'] ?? null)
        );
    }
}

