<?php

namespace App\Infrastructure\Persistence;

use PDO;
use App\Domain\DTO\OmegaTicketDTO;
use App\Domain\Enum\Tables;

class OmegaTicketsRepository
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
                    subject,
                    company,
                    product_id,
                    product_label,
                    family,
                    section,
                    queue,
                    category,
                    status,
                    priority,
                    opened,
                    updated,
                    due_date,
                    requester_id,
                    owner_id,
                    team_id,
                    history,
                    diretoria,
                    gerencia,
                    agencia,
                    gerente_gestao,
                    gerente,
                    credit,
                    attachment
                FROM " . Tables::OMEGA_CHAMADOS . "
                ORDER BY updated DESC, opened DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(function ($row) {
            $opened = isset($row['opened']) && $row['opened'] ? date('Y-m-d H:i:s', strtotime($row['opened'])) : null;
            $updated = isset($row['updated']) && $row['updated'] ? date('Y-m-d H:i:s', strtotime($row['updated'])) : null;
            $dueDate = isset($row['due_date']) && $row['due_date'] ? date('Y-m-d H:i:s', strtotime($row['due_date'])) : null;
            
            $dto = new OmegaTicketDTO(
                isset($row['id']) ? $row['id'] : null,
                isset($row['subject']) ? $row['subject'] : null,
                isset($row['company']) ? $row['company'] : null,
                isset($row['product_id']) ? $row['product_id'] : null,
                isset($row['product_label']) ? $row['product_label'] : null,
                isset($row['family']) ? $row['family'] : null,
                isset($row['section']) ? $row['section'] : null,
                isset($row['queue']) ? $row['queue'] : null,
                isset($row['category']) ? $row['category'] : null,
                isset($row['status']) ? $row['status'] : null,
                isset($row['priority']) ? $row['priority'] : null,
                $opened,
                $updated,
                $dueDate,
                isset($row['requester_id']) ? $row['requester_id'] : null,
                isset($row['owner_id']) ? $row['owner_id'] : null,
                isset($row['team_id']) ? $row['team_id'] : null,
                isset($row['history']) ? $row['history'] : null,
                isset($row['diretoria']) ? $row['diretoria'] : null,
                isset($row['gerencia']) ? $row['gerencia'] : null,
                isset($row['agencia']) ? $row['agencia'] : null,
                isset($row['gerente_gestao']) ? $row['gerente_gestao'] : null,
                isset($row['gerente']) ? $row['gerente'] : null,
                isset($row['credit']) ? $row['credit'] : null,
                isset($row['attachment']) ? $row['attachment'] : null
            );
            
            return $dto->toArray();
        }, $results);
    }
}
