<?php

namespace App\Infrastructure\Persistence;

use PDO;
use App\Domain\DTO\FilterDTO;
use App\Domain\DTO\DetalhesDTO;
use App\Domain\Enum\Tables;
use App\Infrastructure\Helpers\DateFormatter;
use App\Infrastructure\Helpers\ValueFormatter;

/**
 * Repositório para buscar todos os registros de detalhes com filtros opcionais
 */
class DetalhesRepository extends BaseRepository
{
    /**
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, DetalhesDTO::class);
    }

    /**
     * Retorna o SELECT completo da consulta
     * @return string
     */
    public function baseSelect(): string
    {
        return "SELECT 
                    registro_id,
                    segmento,
                    segmento_id,
                    diretoria_id,
                    diretoria_nome,
                    gerencia_regional_id,
                    gerencia_regional_nome,
                    agencia_id,
                    agencia_nome,
                    gerente_gestao_id,
                    gerente_gestao_nome,
                    gerente_id,
                    gerente_nome,
                    familia_id,
                    familia_nome,
                    id_indicador,
                    ds_indicador,
                    subindicador,
                    id_subindicador,
                    carteira,
                    canal_venda,
                    tipo_venda,
                    modalidade_pagamento,
                    data,
                    competencia,
                    valor_meta,
                    valor_realizado,
                    quantidade,
                    peso,
                    pontos,
                    status_id
                FROM " . Tables::F_DETALHES . "
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
                $sql .= " AND segmento_id = :segmento";
                $params[':segmento'] = $filters->segmento;
            }

            if ($filters->diretoria !== null) {
                $sql .= " AND diretoria_id = :diretoria";
                $params[':diretoria'] = $filters->diretoria;
            }

            if ($filters->regional !== null) {
                $sql .= " AND gerencia_regional_id = :regional";
                $params[':regional'] = $filters->regional;
            }

            if ($filters->agencia !== null) {
                $sql .= " AND agencia_id = :agencia";
                $params[':agencia'] = $filters->agencia;
            }

            if ($filters->gerenteGestao !== null) {
                $sql .= " AND gerente_gestao_id = :gerente_gestao";
                $params[':gerente_gestao'] = $filters->gerenteGestao;
            }

            if ($filters->gerente !== null) {
                $sql .= " AND gerente_id = :gerente";
                $params[':gerente'] = $filters->gerente;
            }

            if ($filters->familia !== null) {
                $sql .= " AND familia_id = :familia";
                $params[':familia'] = $filters->familia;
            }

            if ($filters->indicador !== null) {
                $sql .= " AND id_indicador = :indicador";
                $params[':indicador'] = $filters->indicador;
            }

            if ($filters->subindicador !== null) {
                $sql .= " AND id_subindicador = :subindicador";
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
        return "ORDER BY data DESC";
    }

    /**
     * Mapeia um array de resultados para DetalhesDTO
     * @param array $row
     * @return DetalhesDTO
     */
    public function mapToDto(array $row): DetalhesDTO
    {
        $dataIso = DateFormatter::toIsoDate($row['data'] ?? null);
        $competenciaIso = DateFormatter::toIsoDate($row['competencia'] ?? null);
            
        return new DetalhesDTO(
            $row['registro_id'] ?? null,
            $row['segmento'] ?? null,
            $row['segmento_id'] ?? null,
            $row['diretoria_id'] ?? null,
            $row['diretoria_nome'] ?? null,
            $row['gerencia_regional_id'] ?? null,
            $row['gerencia_regional_nome'] ?? null,
            $row['agencia_id'] ?? null,
            $row['agencia_nome'] ?? null,
            $row['gerente_gestao_id'] ?? null,
            $row['gerente_gestao_nome'] ?? null,
            $row['gerente_id'] ?? null,
            $row['gerente_nome'] ?? null,
            $row['familia_id'] ?? null,
            $row['familia_nome'] ?? null,
            $row['id_indicador'] ?? null,
            $row['ds_indicador'] ?? null,
            $row['subindicador'] ?? null,
            $row['id_subindicador'] ?? null,
            $row['subindicador'] ?? null,
            null, // subindicadorCodigo
            null, // familiaCodigo
            null, // indicadorCodigo
            $row['carteira'] ?? null,
            $row['canal_venda'] ?? null,
            $row['tipo_venda'] ?? null,
            $row['modalidade_pagamento'] ?? null,
                $dataIso,
                $competenciaIso,
            ValueFormatter::toFloat($row['valor_meta'] ?? null),
            ValueFormatter::toFloat($row['valor_realizado'] ?? null),
            ValueFormatter::toFloat($row['quantidade'] ?? null),
            ValueFormatter::toFloat($row['peso'] ?? null),
            ValueFormatter::toFloat($row['pontos'] ?? null),
            $row['status_id'] ?? null
            );
    }
}
