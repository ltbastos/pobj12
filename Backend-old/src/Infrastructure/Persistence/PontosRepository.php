<?php

namespace App\Infrastructure\Persistence;

use App\Domain\DTO\FilterDTO;
use App\Domain\DTO\PontosDTO;
use App\Domain\Enum\Cargo;
use App\Domain\Enum\Tables;
use App\Infrastructure\Helpers\DateFormatter;
use App\Infrastructure\Helpers\ValueFormatter;

/**
 * Repositório para buscar todos os registros de pontos com filtros opcionais
 */
class PontosRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(PontosDTO::class);
    }

    /**
     * Retorna o SELECT completo da consulta
     * @return string
     */
    public function baseSelect(): string
    {
        return "SELECT 
                    p.id,
                    p.funcional,
                    dp.indicador_id AS id_indicador,
                    dp.familia_id AS id_familia,
                    COALESCE(ind.nm_indicador, '') AS indicador,
                    p.meta,
                    p.realizado,
                    p.data_realizado,
                    p.dt_atualizacao,
                    e.segmento_id,
                    e.diretoria_id,
                    e.regional_id,
                    e.agencia_id
                FROM " . Tables::F_PONTOS . " p
                LEFT JOIN " . Tables::D_PRODUTOS . " dp ON dp.id = p.produto_id
                LEFT JOIN indicador ind ON ind.id = dp.indicador_id
                LEFT JOIN " . Tables::D_ESTRUTURA . " e ON e.funcional = CAST(p.funcional AS CHAR)
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
            $sql .= " AND EXISTS (
                SELECT 1 FROM " . Tables::D_ESTRUTURA . " ggestao 
                WHERE ggestao.funcional = :gerente_gestao
                AND ggestao.id_cargo = " . Cargo::GERENTE_GESTAO . "
                AND ggestao.id_segmento = e.id_segmento
                AND ggestao.id_diretoria = e.id_diretoria
                AND ggestao.id_regional = e.id_regional
                AND ggestao.id_agencia = e.id_agencia
            )";
            $params[':gerente_gestao'] = $filters->getGerenteGestao();
        }

        if ($filters->getGerente() !== null) {
            $sql .= " AND p.funcional = :gerente";
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

        if ($filters->getDataInicio() !== null) {   
            $sql .= " AND p.data_realizado >= :dataInicio";
            $params[':dataInicio'] = $filters->getDataInicio();
        }
        if ($filters->getDataFim() !== null) {
            $sql .= " AND p.data_realizado <= :dataFim";
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
        return "ORDER BY p.data_realizado DESC, p.id";
    }

    /**
     * Mapeia um array de resultados para PontosDTO
     * @param array $row
     * @return PontosDTO
     */
    public function mapToDto(array $row): PontosDTO
    {
        $dataRealizadoIso = DateFormatter::toIsoDate($row['data_realizado'] ?? null);
        $dtAtualizacaoIso = DateFormatter::toIsoDate($row['dt_atualizacao'] ?? null);

        return new PontosDTO(
            $row['id'] ?? null,                                    // id
            $row['funcional'] ?? null,                             // funcional
            $row['id_indicador'] ?? null,                          // idIndicador
            $row['id_familia'] ?? null,                            // idFamilia
            $row['indicador'] ?? null,                             // indicador
            ValueFormatter::toFloat($row['meta'] ?? null),         // meta
            ValueFormatter::toFloat($row['realizado'] ?? null),   // realizado
            $dataRealizadoIso,                                      // dataRealizado
            $dtAtualizacaoIso                                       // dtAtualizacao
        );
    }
}
