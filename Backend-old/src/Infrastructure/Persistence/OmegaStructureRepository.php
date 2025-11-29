<?php

namespace App\Infrastructure\Persistence;

use App\Domain\DTO\FilterDTO;
use App\Domain\DTO\OmegaStructureDTO;
use App\Domain\Enum\Tables;

/**
 * Repositório para buscar todos os registros de estrutura Omega
 */
class OmegaStructureRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(OmegaStructureDTO::class);
    }

    /**
     * Retorna o SELECT completo da consulta
     * @return string
     */
    public function baseSelect(): string
    {
        return "SELECT 
                    departamento,
                    tipo,
                    departamento_id,
                    ordem_departamento,
                    ordem_tipo
                FROM " . Tables::OMEGA_DEPARTAMENTOS . "
                WHERE 1=1";
    }

    /**
     * Constrói os filtros WHERE baseado no FilterDTO
     * OmegaStructure não utiliza filtros, então sempre retorna vazio
     * @param FilterDTO|null $filters
     * @return array ['sql' => string, 'params' => array]
     */
    public function builderFilter(FilterDTO $filters = null): array
    {
        // OmegaStructure não utiliza filtros
        return ['sql' => '', 'params' => []];
    }

    /**
     * Retorna a cláusula ORDER BY
     * @return string
     */
    protected function getOrderBy(): string
    {
        return "ORDER BY ordem_departamento ASC, ordem_tipo ASC, departamento ASC, tipo ASC";
    }

    /**
     * Mapeia um array de resultados para OmegaStructureDTO
     * @param array $row
     * @return OmegaStructureDTO
     */
    public function mapToDto(array $row): OmegaStructureDTO
    {
        return new OmegaStructureDTO(
            $row['departamento'] ?? null,
            $row['tipo'] ?? null,
            $row['departamento_id'] ?? null,
            $row['ordem_departamento'] ?? null,
            $row['ordem_tipo'] ?? null
        );
    }
}
