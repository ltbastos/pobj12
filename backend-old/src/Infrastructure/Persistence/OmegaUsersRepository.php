<?php

namespace App\Infrastructure\Persistence;

use App\Domain\DTO\FilterDTO;
use App\Domain\DTO\OmegaUserDTO;
use App\Domain\Enum\Tables;

/**
 * Repositório para buscar todos os registros de usuários Omega
 */
class OmegaUsersRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(OmegaUserDTO::class);
    }

    /**
     * Retorna o SELECT completo da consulta
     * @return string
     */
    public function baseSelect(): string
    {
        return "SELECT 
                    id,
                    nome,
                    funcional,
                    matricula,
                    cargo,
                    usuario,
                    analista,
                    supervisor,
                    admin,
                    encarteiramento,
                    meta,
                    orcamento,
                    pobj,
                    matriz,
                    outros
                FROM " . Tables::OMEGA_USUARIOS . "
                WHERE 1=1";
    }

    /**
     * Constrói os filtros WHERE baseado no FilterDTO
     * OmegaUsers não utiliza filtros, então sempre retorna vazio
     * @param FilterDTO|null $filters
     * @return array ['sql' => string, 'params' => array]
     */
    public function builderFilter(FilterDTO $filters = null): array
    {
        // OmegaUsers não utiliza filtros
        return ['sql' => '', 'params' => []];
    }

    /**
     * Retorna a cláusula ORDER BY
     * @return string
     */
    protected function getOrderBy(): string
    {
        return "ORDER BY nome ASC";
    }

    /**
     * Mapeia um array de resultados para OmegaUserDTO
     * @param array $row
     * @return OmegaUserDTO
     */
    public function mapToDto(array $row): OmegaUserDTO
    {
        return new OmegaUserDTO(
            $row['id'] ?? null,
            $row['nome'] ?? null,
            $row['funcional'] ?? null,
            $row['matricula'] ?? null,
            $row['cargo'] ?? null,
            isset($row['usuario']) ? (bool)$row['usuario'] : false,
            isset($row['analista']) ? (bool)$row['analista'] : false,
            isset($row['supervisor']) ? (bool)$row['supervisor'] : false,
            isset($row['admin']) ? (bool)$row['admin'] : false,
            isset($row['encarteiramento']) ? (bool)$row['encarteiramento'] : false,
            isset($row['meta']) ? (bool)$row['meta'] : false,
            isset($row['orcamento']) ? (bool)$row['orcamento'] : false,
            isset($row['pobj']) ? (bool)$row['pobj'] : false,
            isset($row['matriz']) ? (bool)$row['matriz'] : false,
            isset($row['outros']) ? (bool)$row['outros'] : false
        );
    }
}
