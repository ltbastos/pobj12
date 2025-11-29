<?php

namespace App\Infrastructure\Persistence;

use App\Domain\DTO\FilterDTO;
use App\Domain\DTO\ProdutoDTO;
use App\Domain\Entity\DProduto;
use App\Domain\Enum\Tables;
use App\Infrastructure\Helpers\ValueFormatter;

/**
 * Repositório para buscar todos os registros de produtos com filtros opcionais
 */
class ProdutoRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(ProdutoDTO::class);
    }

    /**
     * Retorna o SELECT completo da consulta
     * @return string
     */
    public function baseSelect(): string
    {
        return "SELECT 
                    p.id,
                    p.familia_id as id_familia,
                    COALESCE(f.nm_familia, '') as familia,
                    p.indicador_id as id_indicador,
                    COALESCE(i.nm_indicador, '') as indicador,
                    p.subindicador_id as id_subindicador,
                    COALESCE(s.nm_subindicador, '') as subindicador,
                    p.metrica,
                    MAX(p.peso) as peso
                FROM " . Tables::D_PRODUTOS . " p
                LEFT JOIN familia f ON f.id = p.familia_id
                LEFT JOIN indicador i ON i.id = p.indicador_id
                LEFT JOIN subindicador s ON s.id = p.subindicador_id
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

        if ($filters->getFamilia() !== null) {
            $sql .= " AND p.familia_id = :familia";
            $params[':familia'] = $filters->getFamilia();
        }

        if ($filters->getIndicador() !== null) {
            $sql .= " AND p.indicador_id = :indicador";
            $params[':indicador'] = $filters->getIndicador();
        }

        if ($filters->getSubindicador() !== null) {
            $sql .= " AND p.subindicador_id = :subindicador";
            $params[':subindicador'] = $filters->getSubindicador();
        }

        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Retorna a cláusula ORDER BY e GROUP BY
     * @return string
     */
    protected function getOrderBy(): string
    {
        return "GROUP BY 
                    p.id,
                    p.familia_id,
                    f.nm_familia,
                    p.indicador_id,
                    i.nm_indicador,
                    p.subindicador_id,
                    s.nm_subindicador,
                    p.metrica
                ORDER BY f.nm_familia ASC, i.nm_indicador ASC, s.nm_subindicador ASC";
    }

    /**
     * Retorna o SQL base para contar registros
     * Para queries com GROUP BY, conta os grupos resultantes
     * @return string
     */
    protected function baseCountSelect(): string
    {
        $baseSelect = $this->baseSelect();
        $fromPos = stripos($baseSelect, 'FROM');
        if ($fromPos === false) {
            return "SELECT COUNT(*) as total";
        }
        
        // Para GROUP BY, precisamos contar os grupos resultantes
        // Usa subquery para contar após o GROUP BY
        $sql = "SELECT COUNT(*) as total FROM (" . $baseSelect;
        $orderBy = $this->getOrderBy();
        if ($orderBy !== "") {
            $sql .= " " . $orderBy;
        }
        $sql .= ") as grouped_results";
        
        return $sql;
    }

    /**
     * Mapeia um array de resultados para ProdutoDTO
     * @param array $row
     * @return ProdutoDTO
     */
    public function mapToDto(array $row): ProdutoDTO
    {
            $entity = DProduto::fromArray($row);
        return $entity->toDTO();
    }
}
