<?php

namespace App\Infrastructure\Persistence;

use App\Domain\DTO\FilterDTO;
use App\Domain\Enum\Tables;
use App\Domain\Enum\StatusIndicador;

/**
 * Repositório para buscar todos os registros de status de indicadores
 */
class StatusIndicadoresRepository extends BaseRepository
{
    public function __construct()
    {
        // Não usa DTO, então passamos null e retornamos array diretamente
        parent::__construct(null);
    }

    /**
     * Retorna o SELECT completo da consulta
     * @return string
     */
    public function baseSelect(): string
    {
        return "SELECT 
                    id,
                    status AS label
                FROM " . Tables::D_STATUS_INDICADORES . "
                WHERE 1=1";
    }

    /**
     * Constrói os filtros WHERE baseado no FilterDTO
     * StatusIndicadores não utiliza filtros, então sempre retorna vazio
     * @param FilterDTO|null $filters
     * @return array ['sql' => string, 'params' => array]
     */
    public function builderFilter(FilterDTO $filters = null): array
    {
        // StatusIndicadores não utiliza filtros
        return ['sql' => '', 'params' => []];
    }

    /**
     * Retorna a cláusula ORDER BY
     * @return string
     */
    protected function getOrderBy(): string
    {
        return "ORDER BY id ASC";
    }

    /**
     * Mapeia um array de resultados para array simples
     * @param array $row
     * @return array
     */
    public function mapToDto(array $row): array
    {
        return [
            'id' => $row['id'] ?? null,
            'label' => $row['label'] ?? null,
        ];
    }

    /**
     * Busca todos os status de indicadores
     * @return array
     */
    public function findAllAsArray(): array
    {
        $result = $this->fetch(null);
        
        // Se não houver resultados, retorna os defaults
        if (empty($result)) {
            return StatusIndicador::getDefaults();
        }

        return $result;
    }

    /**
     * Busca todos os status de indicadores para filtros
     * @return array
     */
    public function findAllForFilter(): array
    {
        $result = $this->fetch(null);
        
        // Se não houver resultados, retorna os defaults
        if (empty($result)) {
            return StatusIndicador::getDefaultsForFilter();
        }

        return $result;
    }
}
