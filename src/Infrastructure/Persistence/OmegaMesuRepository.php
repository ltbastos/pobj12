<?php

namespace App\Infrastructure\Persistence;

use PDO;
use App\Domain\DTO\FilterDTO;
use App\Domain\DTO\OmegaMesuDTO;
use App\Domain\Enum\Tables;

/**
 * Repositório para buscar todos os registros de estrutura Omega Mesu com filtros opcionais
 */
class OmegaMesuRepository extends BaseRepository
{
    /**
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, OmegaMesuDTO::class);
    }

    /**
     * Retorna o SELECT completo da consulta
     * @return string
     */
    public function baseSelect(): string
    {
        return "SELECT DISTINCT
                    segmento AS segmento,
                    id_segmento AS segmento_id,
                    diretoria AS diretoria,
                    id_diretoria AS diretoria_id,
                    regional AS gerencia_regional,
                    id_regional AS gerencia_regional_id,
                    agencia AS agencia,
                    id_agencia AS agencia_id,
                    nome AS gerente_gestao,
                    funcional AS gerente_gestao_id,
                    nome AS gerente,
                    funcional AS gerente_id
                FROM " . Tables::D_ESTRUTURA . "
                WHERE segmento IS NOT NULL";
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
            $sql .= " AND id_segmento = :segmento";
            $params[':segmento'] = $filters->getSegmento();
        }

        if ($filters->getDiretoria() !== null) {
            $sql .= " AND id_diretoria = :diretoria";
            $params[':diretoria'] = $filters->getDiretoria();
        }

        if ($filters->getRegional() !== null) {
            $sql .= " AND id_regional = :regional";
            $params[':regional'] = $filters->getRegional();
        }

        if ($filters->getAgencia() !== null) {
            $sql .= " AND id_agencia = :agencia";
            $params[':agencia'] = $filters->getAgencia();
        }

        if ($filters->getGerenteGestao() !== null) {
            $sql .= " AND funcional = :gerente_gestao";
            $params[':gerente_gestao'] = $filters->getGerenteGestao();
        }

        if ($filters->getGerente() !== null) {
            $sql .= " AND funcional = :gerente";
            $params[':gerente'] = $filters->getGerente();
        }

        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Retorna a cláusula ORDER BY
     * @return string
     */
    protected function getOrderBy(): string
    {
        return "ORDER BY segmento, diretoria, regional, agencia";
    }

    /**
     * Mapeia um array de resultados para OmegaMesuDTO
     * @param array $row
     * @return OmegaMesuDTO
     */
    public function mapToDto(array $row): OmegaMesuDTO
    {
        $segmentoId = isset($row['segmento_id']) ? $row['segmento_id'] : null;
        $diretoriaId = isset($row['diretoria_id']) ? $row['diretoria_id'] : null;
        $gerenciaRegionalId = isset($row['gerencia_regional_id']) ? $row['gerencia_regional_id'] : null;
        $agenciaId = isset($row['agencia_id']) ? $row['agencia_id'] : null;
        $gerenteGestaoId = isset($row['gerente_gestao_id']) ? $row['gerente_gestao_id'] : null;
        $gerenteId = isset($row['gerente_id']) ? $row['gerente_id'] : null;

        return new OmegaMesuDTO(
            $row['segmento'] ?? null,
            $segmentoId !== null ? (string)$segmentoId : null,
            $row['diretoria'] ?? null,
            $diretoriaId !== null ? (string)$diretoriaId : null,
            $row['gerencia_regional'] ?? null,
            $gerenciaRegionalId !== null ? (string)$gerenciaRegionalId : null,
            $row['agencia'] ?? null,
            $agenciaId !== null ? (string)$agenciaId : null,
            $row['gerente_gestao'] ?? null,
            $gerenteGestaoId !== null ? (string)$gerenteGestaoId : null,
            $row['gerente'] ?? null,
            $gerenteId !== null ? (string)$gerenteId : null
        );
    }
}
