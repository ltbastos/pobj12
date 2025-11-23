<?php

namespace App\Infrastructure\Persistence;

use PDO;
use App\Domain\DTO\FilterDTO;
use App\Domain\Entity\FCampanhas;
use App\Domain\Enum\Tables;
use App\Infrastructure\Helpers\DateFormatter;

class CampanhasRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllAsArray(FilterDTO $filters = null): array
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
                FROM " . Tables::F_CAMPANHAS . "
                WHERE 1=1";
        
        $params = [];
        
        if ($filters !== null && $filters->hasAnyFilter()) {
            if ($filters->segmento !== null) {
                $sql .= " AND segmento_id = :segmento";
                $params[':segmento'] = $filters->segmento;
            }
            if ($filters->diretoria !== null) {
                $sql .= " AND diretoria_id = :diretoria";
                $params[':diretoria'] = $filters->diretoria;
            }
            if ($filters->regional !== null) {
                $sql .= " AND gerencia_regional_id = :regional";
                $params[':regional'] = $filters->regional;
            }
            if ($filters->agencia !== null) {
                $sql .= " AND agencia_id = :agencia";
                $params[':agencia'] = $filters->agencia;
            }
            if ($filters->gerenteGestao !== null) {
                $sql .= " AND gerente_gestao_id = :gerente_gestao";
                $params[':gerente_gestao'] = $filters->gerenteGestao;
            }
            if ($filters->gerente !== null) {
                $sql .= " AND gerente_id = :gerente";
                $params[':gerente'] = $filters->gerente;
            }
            if ($filters->familia !== null) {
                $sql .= " AND familia_id = :familia";
                $params[':familia'] = $filters->familia;
            }
            if ($filters->indicador !== null) {
                $sql .= " AND id_indicador = :indicador";
                $params[':indicador'] = $filters->indicador;
            }
            if ($filters->subindicador !== null) {
                $sql .= " AND id_subindicador = :subindicador";
                $params[':subindicador'] = $filters->subindicador;
            }
        }
        
        $sql .= " ORDER BY data DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(function ($row) {
            $dataIso = DateFormatter::toIsoDate(isset($row['data']) ? $row['data'] : null);
            $row['data'] = $dataIso;
            
            if (isset($row['linhas'])) {
                $row['linhas'] = $this->toFloat($row['linhas']);
            }
            if (isset($row['cash'])) {
                $row['cash'] = $this->toFloat($row['cash']);
            }
            if (isset($row['conquista'])) {
                $row['conquista'] = $this->toFloat($row['conquista']);
            }
            
            $entity = FCampanhas::fromArray($row);
            return $entity->toDTO()->toArray();
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
