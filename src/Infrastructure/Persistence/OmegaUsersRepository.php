<?php

namespace App\Infrastructure\Persistence;

use PDO;
use App\Domain\DTO\OmegaUserDTO;
use App\Domain\Enum\Tables;

class OmegaUsersRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllAsArray(): array
    {
        $sql = "SELECT 
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
                ORDER BY nome ASC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(function ($row) {
            $dto = new OmegaUserDTO(
                isset($row['id']) ? $row['id'] : null,
                isset($row['nome']) ? $row['nome'] : null,
                isset($row['funcional']) ? $row['funcional'] : null,
                isset($row['matricula']) ? $row['matricula'] : null,
                isset($row['cargo']) ? $row['cargo'] : null,
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
            
            return $dto->toArray();
        }, $results);
    }
}
