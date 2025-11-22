<?php

namespace App\Infrastructure\Persistence;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use App\Domain\DTO\OmegaMesuDTO;
use App\Infrastructure\Helpers\RowMapper;

class OmegaMesuRepository
{
    private $entityManager;
    private $connection;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->connection = $entityManager->getConnection();
    }

    public function findAll(): array
    {
        $sql = "SELECT DISTINCT
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
                FROM d_estrutura
                WHERE segmento IS NOT NULL
                ORDER BY segmento, diretoria, regional, agencia";

        $results = $this->connection->executeQuery($sql)->fetchAll(\PDO::FETCH_ASSOC);
        
        return array_map(function ($row) {
            $dto = new OmegaMesuDTO(
                isset($row['segmento']) ? $row['segmento'] : null,
                RowMapper::toString(isset($row['segmento_id']) ? $row['segmento_id'] : null),
                isset($row['diretoria']) ? $row['diretoria'] : null,
                RowMapper::toString(isset($row['diretoria_id']) ? $row['diretoria_id'] : null),
                isset($row['gerencia_regional']) ? $row['gerencia_regional'] : null,
                RowMapper::toString(isset($row['gerencia_regional_id']) ? $row['gerencia_regional_id'] : null),
                isset($row['agencia']) ? $row['agencia'] : null,
                RowMapper::toString(isset($row['agencia_id']) ? $row['agencia_id'] : null),
                isset($row['gerente_gestao']) ? $row['gerente_gestao'] : null,
                RowMapper::toString(isset($row['gerente_gestao_id']) ? $row['gerente_gestao_id'] : null),
                isset($row['gerente']) ? $row['gerente'] : null,
                RowMapper::toString(isset($row['gerente_id']) ? $row['gerente_id'] : null)
            );
            
            return $dto->toArray();
        }, $results);
    }
}

