<?php

namespace App\Infrastructure\Persistence;

use App\Domain\DTO\FilterDTO;
use App\Domain\DTO\OmegaStatusDTO;
use App\Domain\Enum\Tables;

/**
 * Repositório para buscar todos os registros de status Omega
 */
class OmegaStatusRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(OmegaStatusDTO::class);
    }

    /**
     * Retorna o SELECT completo da consulta
     * @return string
     */
    public function baseSelect(): string
    {
        return "SELECT 
                    id,
                    label,
                    tone,
                    descricao,
                    ordem,
                    departamento_id
                FROM " . Tables::OMEGA_STATUS . "
                WHERE 1=1";
    }

    /**
     * Constrói os filtros WHERE baseado no FilterDTO
     * OmegaStatus não utiliza filtros, então sempre retorna vazio
     * @param FilterDTO|null $filters
     * @return array ['sql' => string, 'params' => array]
     */
    public function builderFilter(FilterDTO $filters = null): array
    {
        // OmegaStatus não utiliza filtros
        return ['sql' => '', 'params' => []];
    }

    /**
     * Retorna a cláusula ORDER BY
     * @return string
     */
    protected function getOrderBy(): string
    {
        return "ORDER BY ordem ASC, label ASC";
    }

    /**
     * Mapeia um array de resultados para OmegaStatusDTO
     * @param array $row
     * @return OmegaStatusDTO
     */
    public function mapToDto(array $row): OmegaStatusDTO
    {
        return new OmegaStatusDTO(
            $row['id'] ?? null,
            $row['label'] ?? null,
            $row['tone'] ?? null,
            $row['descricao'] ?? null,
            $row['ordem'] ?? null,
            $row['departamento_id'] ?? null
        );
    }
}
