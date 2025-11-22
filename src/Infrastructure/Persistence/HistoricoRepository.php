<?php

namespace App\Infrastructure\Persistence;

// Removed Connection dependency
use PDO;
use App\Domain\DTO\HistoricoDTO;
use App\Infrastructure\Helpers\DateFormatter;

class HistoricoRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllAsArray(): array
    {
        $sql = "SELECT 
                    nivel,
                    ano,
                    `database` AS data,
                    segmento,
                    segmento_id,
                    diretoria,
                    diretoria_nome,
                    gerencia_regional,
                    gerencia_regional_nome,
                    agencia,
                    agencia_nome,
                    gerente_gestao,
                    gerente_gestao_nome,
                    gerente,
                    gerente_nome,
                    participantes,
                    `rank`,
                    pontos,
                    realizado,
                    meta
                FROM f_historico_ranking_pobj
                ORDER BY `database` DESC, nivel, ano";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(function ($row) {
            $dataIso = DateFormatter::toIsoDate(isset($row['data']) ? $row['data'] : null);
            
            $dto = new HistoricoDTO(
                isset($row['nivel']) ? $row['nivel'] : null,
                isset($row['ano']) ? $row['ano'] : null,
                $dataIso,
                $dataIso,
                isset($row['segmento']) ? $row['segmento'] : null,
                isset($row['segmento_id']) ? $row['segmento_id'] : null,
                isset($row['diretoria']) ? $row['diretoria'] : null,
                isset($row['diretoria_nome']) ? $row['diretoria_nome'] : null,
                isset($row['gerencia_regional']) ? $row['gerencia_regional'] : null,
                isset($row['gerencia_regional_nome']) ? $row['gerencia_regional_nome'] : null,
                isset($row['agencia']) ? $row['agencia'] : null,
                isset($row['agencia_nome']) ? $row['agencia_nome'] : null,
                isset($row['gerente_gestao']) ? $row['gerente_gestao'] : null,
                isset($row['gerente_gestao_nome']) ? $row['gerente_gestao_nome'] : null,
                isset($row['gerente']) ? $row['gerente'] : null,
                isset($row['gerente_nome']) ? $row['gerente_nome'] : null,
                isset($row['participantes']) ? $row['participantes'] : null,
                isset($row['rank']) ? $row['rank'] : null,
                $this->toFloat(isset($row['pontos']) ? $row['pontos'] : null),
                $this->toFloat(isset($row['realizado']) ? $row['realizado'] : null),
                $this->toFloat(isset($row['meta']) ? $row['meta'] : null)
            );
            
            return $dto->toArray();
        }, $results);
    }
    
    private function toFloat($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (float)$value;
        }
        return null;
    }
}

