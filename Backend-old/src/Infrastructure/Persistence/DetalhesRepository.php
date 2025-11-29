<?php

namespace App\Infrastructure\Persistence;

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
    public function __construct()
    {
        parent::__construct(DetalhesDTO::class);
    }

    /**
     * Retorna o SELECT completo da consulta
     * @return string
     */
    public function baseSelect(): string
    {
        return "SELECT
                    fr.id_contrato,
                    fr.id_contrato AS registro_id,
                    fr.data_realizado AS data,
                    fr.data_realizado AS competencia,
                    cal.ano,
                    cal.mes,
                    cal.mes_nome,
                    est.segmento_id,
                    seg.nome AS segmento,
                    est.diretoria_id,
                    dir.nome AS diretoria_nome,
                    est.regional_id AS gerencia_regional_id,
                    reg.nome AS gerencia_regional_nome,
                    est.agencia_id,
                    ag.nome AS agencia_nome,
                    fr.funcional AS gerente_id,
                    est.nome AS gerente_nome,
                    prod.familia_id,
                    fam.nm_familia AS familia_nome,
                    prod.indicador_id AS id_indicador,
                    ind.nm_indicador AS ds_indicador,
                    prod.subindicador_id AS id_subindicador,
                    sub.nm_subindicador AS subindicador,
                    prod.peso,
                    fr.realizado AS valor_realizado,
                    meta.meta_mensal AS valor_meta,
                    det.canal_venda,
                    det.tipo_venda,
                    det.condicao_pagamento AS modalidade_pagamento,
                    det.dt_vencimento,
                    det.dt_cancelamento,
                    det.motivo_cancelamento,
                    det.status_id
                FROM " . Tables::F_REALIZADOS . " fr
                JOIN " . Tables::D_CALENDARIO . " cal
                    ON cal.data = fr.data_realizado
                JOIN " . Tables::D_ESTRUTURA . " est
                    ON est.funcional = fr.funcional
                JOIN " . Tables::D_PRODUTOS . " prod
                    ON prod.id = fr.produto_id
                LEFT JOIN segmentos seg
                    ON seg.id = est.segmento_id
                LEFT JOIN diretorias dir
                    ON dir.id = est.diretoria_id
                LEFT JOIN regionais reg
                    ON reg.id = est.regional_id
                LEFT JOIN agencias ag
                    ON ag.id = est.agencia_id
                LEFT JOIN familia fam
                    ON fam.id = prod.familia_id
                LEFT JOIN indicador ind
                    ON ind.id = prod.indicador_id
                LEFT JOIN subindicador sub
                    ON sub.id = prod.subindicador_id
                LEFT JOIN " . Tables::F_META . " meta
                    ON  meta.funcional = fr.funcional
                    AND meta.produto_id = fr.produto_id
                    AND YEAR(meta.data_meta) = cal.ano
                    AND MONTH(meta.data_meta) = cal.mes
                LEFT JOIN " . Tables::F_DETALHES . " det
                    ON det.contrato_id = fr.id_contrato
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
            $sql .= " AND est.segmento_id = :segmento";
            $params[':segmento'] = $filters->getSegmento();
        }

        if ($filters->getDiretoria() !== null) {
            $sql .= " AND est.diretoria_id = :diretoria";
            $params[':diretoria'] = $filters->getDiretoria();
        }

        if ($filters->getRegional() !== null) {
            $sql .= " AND est.regional_id = :regional";
            $params[':regional'] = $filters->getRegional();
        }

        if ($filters->getAgencia() !== null) {
            $sql .= " AND est.agencia_id = :agencia";
            $params[':agencia'] = $filters->getAgencia();
        }

        if ($filters->getGerente() !== null) {
            $sql .= " AND fr.funcional = :gerente";
            $params[':gerente'] = $filters->getGerente();
        }

        if ($filters->getFamilia() !== null) {
            $sql .= " AND prod.familia_id = :familia";
            $params[':familia'] = $filters->getFamilia();
        }

        if ($filters->getIndicador() !== null) {
            $sql .= " AND prod.indicador_id = :indicador";
            $params[':indicador'] = $filters->getIndicador();
        }

        if ($filters->getSubindicador() !== null) {
            $sql .= " AND prod.subindicador_id = :subindicador";
            $params[':subindicador'] = $filters->getSubindicador();
        }

        if ($filters->getDataInicio() !== null) {
            $sql .= " AND fr.data_realizado >= :dataInicio";
            $params[':dataInicio'] = $filters->getDataInicio();
        }

        if ($filters->getDataFim() !== null) {
            $sql .= " AND fr.data_realizado <= :dataFim";
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
        return "ORDER BY est.diretoria_id, est.regional_id, est.agencia_id, est.nome, prod.familia_id, prod.indicador_id, prod.subindicador_id, fr.id_contrato";
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
            $row['registro_id'] ?? $row['id_contrato'] ?? null,
            $row['segmento'] ?? null,
            $row['segmento_id'] ?? null,
            $row['diretoria_id'] ?? null,
            $row['diretoria_nome'] ?? null,
            $row['gerencia_regional_id'] ?? null,
            $row['gerencia_regional_nome'] ?? null,
            $row['agencia_id'] ?? null,
            $row['agencia_nome'] ?? null,
            null, // gerente_gestao_id (não disponível na query atual)
            null, // gerente_gestao_nome (não disponível na query atual)
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
            null, // carteira
            $row['canal_venda'] ?? null,
            $row['tipo_venda'] ?? null,
            $row['modalidade_pagamento'] ?? null,
            $dataIso,
            $competenciaIso,
            ValueFormatter::toFloat($row['valor_meta'] ?? null),
            ValueFormatter::toFloat($row['valor_realizado'] ?? null),
            null, // quantidade
            ValueFormatter::toFloat($row['peso'] ?? null),
            null, // pontos
            $row['status_id'] ?? null
        );
    }
}
