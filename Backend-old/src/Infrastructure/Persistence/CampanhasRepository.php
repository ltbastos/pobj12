<?php

namespace App\Infrastructure\Persistence;

use App\Domain\DTO\FilterDTO;
use App\Domain\DTO\CampanhasDTO;
use App\Domain\Enum\Tables;
use App\Infrastructure\Helpers\DateFormatter;
use App\Infrastructure\Helpers\ValueFormatter;

/**
 * Repositório para buscar todos os registros de campanhas com filtros opcionais
 */
class CampanhasRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(CampanhasDTO::class);
    }

    /**
     * Retorna o SELECT completo da consulta
     * @return string
     */
    public function baseSelect(): string
    {
        return "SELECT 
                    campanha_id,
                    sprint_id,
                    segmento,
                    segmento_id,
                    diretoria_id,
                    diretoria_nome,
                    gerencia_regional_id,
                    regional_nome,
                    agencia_id,
                    agencia_nome,
                    gerente_gestao_id,
                    gerente_gestao_nome,
                    gerente_id,
                    gerente_nome,
                    familia_id,
                    id_indicador,
                    ds_indicador,
                    subproduto,
                    id_subindicador,
                    subindicador_codigo,
                    familia_codigo,
                    indicador_codigo,
                    carteira,
                    data,
                    linhas,
                    cash,
                    conquista,
                    atividade
                FROM " . Tables::F_CAMPANHAS . "
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
            $sql .= " AND segmento_id = :segmento";
            $params[':segmento'] = $filters->getSegmento();
        }

        if ($filters->getDiretoria() !== null) {
            $sql .= " AND diretoria_id = :diretoria";
            $params[':diretoria'] = $filters->getDiretoria();
        }

        if ($filters->getRegional() !== null) {
            $sql .= " AND gerencia_regional_id = :regional";
            $params[':regional'] = $filters->getRegional();
        }

        if ($filters->getAgencia() !== null) {
            $sql .= " AND agencia_id = :agencia";
            $params[':agencia'] = $filters->getAgencia();
        }

        if ($filters->getGerenteGestao() !== null) {
            $sql .= " AND gerente_gestao_id = :gerente_gestao";
            $params[':gerente_gestao'] = $filters->getGerenteGestao();
        }

        if ($filters->getGerente() !== null) {
            $sql .= " AND gerente_id = :gerente";
            $params[':gerente'] = $filters->getGerente();
        }

        if ($filters->getFamilia() !== null) {
            $sql .= " AND familia_id = :familia";
            $params[':familia'] = $filters->getFamilia();
        }

        if ($filters->getIndicador() !== null) {
            $sql .= " AND id_indicador = :indicador";
            $params[':indicador'] = $filters->getIndicador();
        }

        if ($filters->getSubindicador() !== null) {
            $sql .= " AND id_subindicador = :subindicador";
            $params[':subindicador'] = $filters->getSubindicador();
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
     * Mapeia um array de resultados para CampanhasDTO
     * @param array $row
     * @return CampanhasDTO
     */
    public function mapToDto(array $row): CampanhasDTO
    {
        $dataIso = DateFormatter::toIsoDate($row['data'] ?? null);

        return new CampanhasDTO(
            $row['campanha_id'] ?? null,                      // campanhaId
            $row['sprint_id'] ?? null,                        // sprintId
            $row['segmento'] ?? null,                          // segmento
            $row['segmento_id'] ?? null,                        // segmentoId
            $row['diretoria_id'] ?? null,                      // diretoriaId
            $row['diretoria_nome'] ?? null,                    // diretoriaNome
            $row['gerencia_regional_id'] ?? null,               // gerenciaRegionalId
            $row['regional_nome'] ?? null,                     // regionalNome
            $row['agencia_id'] ?? null,                        // agenciaId
            $row['agencia_nome'] ?? null,                      // agenciaNome
            $row['gerente_gestao_id'] ?? null,                 // gerenteGestaoId
            $row['gerente_gestao_nome'] ?? null,               // gerenteGestaoNome
            $row['gerente_id'] ?? null,                        // gerenteId
            $row['gerente_nome'] ?? null,                      // gerenteNome
            $row['familia_id'] ?? null,                         // familiaId
            $row['id_indicador'] ?? null,                      // idIndicador
            $row['ds_indicador'] ?? null,                      // dsIndicador
            $row['subproduto'] ?? null,                        // subproduto
            $row['id_subindicador'] ?? null,                   // idSubindicador
            $row['subindicador_codigo'] ?? null,               // subindicadorCodigo
            $row['familia_codigo'] ?? null,                     // familiaCodigo
            $row['indicador_codigo'] ?? null,                  // indicadorCodigo
            $row['carteira'] ?? null,                          // carteira
            $dataIso,                                          // data
            $dataIso,                                          // competencia
            ValueFormatter::toFloat($row['linhas'] ?? null),   // linhas
            ValueFormatter::toFloat($row['cash'] ?? null),     // cash
            ValueFormatter::toFloat($row['conquista'] ?? null), // conquista
            $row['atividade'] ?? null                          // atividade
        );
    }
}
