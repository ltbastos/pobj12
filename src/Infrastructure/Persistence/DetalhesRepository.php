<?php

namespace App\Infrastructure\Persistence;

use PDO;
use App\Domain\DTO\DetalhesDTO;
use App\Infrastructure\Helpers\DateFormatter;

class DetalhesRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllAsArray(): array
    {
        $sql = "SELECT 
                    registro_id,
                    segmento,
                    segmento_id,
                    diretoria_id,
                    diretoria_nome,
                    gerencia_regional_id,
                    gerencia_regional_nome,
                    agencia_id,
                    agencia_nome,
                    gerente_gestao_id,
                    gerente_gestao_nome,
                    gerente_id,
                    gerente_nome,
                    familia_id,
                    familia_nome,
                    id_indicador,
                    ds_indicador,
                    subindicador,
                    id_subindicador,
                    carteira,
                    canal_venda,
                    tipo_venda,
                    modalidade_pagamento,
                    data,
                    competencia,
                    valor_meta,
                    valor_realizado,
                    quantidade,
                    peso,
                    pontos,
                    status_id
                FROM f_detalhes
                ORDER BY data DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(function ($row) {
            $dataIso = DateFormatter::toIsoDate(isset($row['data']) ? $row['data'] : null);
            $competenciaIso = DateFormatter::toIsoDate(isset($row['competencia']) ? $row['competencia'] : null);
            
            $dto = new DetalhesDTO(
                isset($row['registro_id']) ? $row['registro_id'] : null,
                isset($row['segmento']) ? $row['segmento'] : null,
                isset($row['segmento_id']) ? $row['segmento_id'] : null,
                isset($row['diretoria_id']) ? $row['diretoria_id'] : null,
                isset($row['diretoria_nome']) ? $row['diretoria_nome'] : null,
                isset($row['gerencia_regional_id']) ? $row['gerencia_regional_id'] : null,
                isset($row['gerencia_regional_nome']) ? $row['gerencia_regional_nome'] : null,
                isset($row['agencia_id']) ? $row['agencia_id'] : null,
                isset($row['agencia_nome']) ? $row['agencia_nome'] : null,
                isset($row['gerente_gestao_id']) ? $row['gerente_gestao_id'] : null,
                isset($row['gerente_gestao_nome']) ? $row['gerente_gestao_nome'] : null,
                isset($row['gerente_id']) ? $row['gerente_id'] : null,
                isset($row['gerente_nome']) ? $row['gerente_nome'] : null,
                isset($row['familia_id']) ? $row['familia_id'] : null,
                isset($row['familia_nome']) ? $row['familia_nome'] : null,
                isset($row['id_indicador']) ? $row['id_indicador'] : null,
                isset($row['ds_indicador']) ? $row['ds_indicador'] : null,
                isset($row['subindicador']) ? $row['subindicador'] : null,
                isset($row['id_subindicador']) ? $row['id_subindicador'] : null,
                isset($row['subindicador']) ? $row['subindicador'] : null,
                isset($row['id_subindicador']) ? $row['id_subindicador'] : null,
                isset($row['familia_id']) ? $row['familia_id'] : null,
                isset($row['id_indicador']) ? $row['id_indicador'] : null,
                isset($row['carteira']) ? $row['carteira'] : null,
                isset($row['canal_venda']) ? $row['canal_venda'] : null,
                isset($row['tipo_venda']) ? $row['tipo_venda'] : null,
                isset($row['modalidade_pagamento']) ? $row['modalidade_pagamento'] : null,
                $dataIso,
                $competenciaIso,
                $this->toFloat(isset($row['valor_meta']) ? $row['valor_meta'] : null),
                $this->toFloat(isset($row['valor_realizado']) ? $row['valor_realizado'] : null),
                $this->toFloat(isset($row['quantidade']) ? $row['quantidade'] : null),
                $this->toFloat(isset($row['peso']) ? $row['peso'] : null),
                $this->toFloat(isset($row['pontos']) ? $row['pontos'] : null),
                isset($row['status_id']) ? $row['status_id'] : null
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
