<?php

namespace App\Infrastructure\Persistence;

use PDO;
use App\Domain\DTO\CampanhasDTO;
use App\Infrastructure\Helpers\DateFormatter;

class CampanhasRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllAsArray(): array
    {
        $sql = "SELECT 
                    campanha_id,
                    sprint_id,
                    segmento,
                    segmento_id,
                    diretoria_id,
                    diretoria_nome,
                    gerencia_regional_id,
                    regional_nome,
                    agencia_id,
                    agencia_nome,
                    gerente_gestao_id,
                    gerente_gestao_nome,
                    gerente_id,
                    gerente_nome,
                    familia_id,
                    id_indicador,
                    ds_indicador,
                    subproduto,
                    id_subindicador,
                    subindicador_codigo,
                    familia_codigo,
                    indicador_codigo,
                    carteira,
                    data,
                    linhas,
                    cash,
                    conquista,
                    atividade
                FROM f_campanhas
                ORDER BY data DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(function ($row) {
            $dataIso = DateFormatter::toIsoDate(isset($row['data']) ? $row['data'] : null);
            
            $dto = new CampanhasDTO(
                isset($row['campanha_id']) ? $row['campanha_id'] : null,
                isset($row['sprint_id']) ? $row['sprint_id'] : null,
                isset($row['segmento']) ? $row['segmento'] : null,
                isset($row['segmento_id']) ? $row['segmento_id'] : null,
                isset($row['diretoria_id']) ? $row['diretoria_id'] : null,
                isset($row['diretoria_nome']) ? $row['diretoria_nome'] : null,
                isset($row['gerencia_regional_id']) ? $row['gerencia_regional_id'] : null,
                isset($row['regional_nome']) ? $row['regional_nome'] : null,
                isset($row['agencia_id']) ? $row['agencia_id'] : null,
                isset($row['agencia_nome']) ? $row['agencia_nome'] : null,
                isset($row['gerente_gestao_id']) ? $row['gerente_gestao_id'] : null,
                isset($row['gerente_gestao_nome']) ? $row['gerente_gestao_nome'] : null,
                isset($row['gerente_id']) ? $row['gerente_id'] : null,
                isset($row['gerente_nome']) ? $row['gerente_nome'] : null,
                isset($row['familia_id']) ? $row['familia_id'] : null,
                isset($row['id_indicador']) ? $row['id_indicador'] : null,
                isset($row['ds_indicador']) ? $row['ds_indicador'] : null,
                isset($row['subproduto']) ? $row['subproduto'] : null,
                isset($row['id_subindicador']) ? $row['id_subindicador'] : null,
                isset($row['subindicador_codigo']) ? $row['subindicador_codigo'] : null,
                isset($row['familia_codigo']) ? $row['familia_codigo'] : null,
                isset($row['indicador_codigo']) ? $row['indicador_codigo'] : null,
                isset($row['carteira']) ? $row['carteira'] : null,
                $dataIso,
                $dataIso,
                $this->toFloat(isset($row['linhas']) ? $row['linhas'] : null),
                $this->toFloat(isset($row['cash']) ? $row['cash'] : null),
                $this->toFloat(isset($row['conquista']) ? $row['conquista'] : null),
                isset($row['atividade']) ? $row['atividade'] : null
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
