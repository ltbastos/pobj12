<?php

namespace App\Infrastructure\Persistence;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;

class VariavelRepository
{
    private $connection;

    public function __construct(EntityManager $entityManager)
    {
        $this->connection = $entityManager->getConnection();
    }

    public function findAllAsArray(): array
    {
        $sql = "SELECT 
                    v.id,
                    v.funcional,
                    v.meta,
                    v.variavel,
                    v.dt_atualizacao,
                    COALESCE(e.nome, '') AS nome_funcional,
                    COALESCE(e.segmento, '') AS segmento,
                    COALESCE(CAST(e.id_segmento AS CHAR), '') AS segmento_id,
                    COALESCE(e.diretoria, '') AS diretoria_nome,
                    COALESCE(CAST(e.id_diretoria AS CHAR), '') AS diretoria_id,
                    COALESCE(e.regional, '') AS regional_nome,
                    COALESCE(CAST(e.id_regional AS CHAR), '') AS gerencia_id,
                    COALESCE(e.agencia, '') AS agencia_nome,
                    COALESCE(CAST(e.id_agencia AS CHAR), '') AS agencia_id
                FROM f_variavel v
                LEFT JOIN d_estrutura e ON e.funcional COLLATE utf8mb4_unicode_ci = CAST(v.funcional AS CHAR) COLLATE utf8mb4_unicode_ci
                ORDER BY v.dt_atualizacao DESC";
        
        return $this->connection->executeQuery($sql)->fetchAll(\PDO::FETCH_ASSOC);
    }
}

