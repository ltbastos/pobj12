<?php

namespace App\Infrastructure\Persistence;

use PDO;
use App\Domain\DTO\FilterDTO;
use App\Domain\Entity\FPontos;
use App\Domain\Enum\Cargo;
use App\Domain\Enum\Tables;
use App\Infrastructure\Helpers\DateFormatter;

class PontosRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllAsArray(FilterDTO $filters = null): array
    {
        $sql = "SELECT 
                    p.id,
                    p.funcional,
                    p.id_indicador,
                    p.id_familia,
                    p.indicador,
                    p.meta,
                    p.realizado,
                    p.data_realizado,
                    p.dt_atualizacao
                FROM " . Tables::F_PONTOS . " p
                LEFT JOIN " . Tables::D_ESTRUTURA . " e ON e.funcional = CAST(p.funcional AS CHAR)
                WHERE 1=1";
        
        $params = [];
        
        if ($filters !== null && $filters->hasAnyFilter()) {
            if ($filters->segmento !== null) {
                $sql .= " AND e.id_segmento = :segmento";
                $params[':segmento'] = $filters->segmento;
            }
            
            if ($filters->diretoria !== null) {
                $sql .= " AND e.id_diretoria = :diretoria";
                $params[':diretoria'] = $filters->diretoria;
            }
            
            if ($filters->regional !== null) {
                $sql .= " AND e.id_regional = :regional";
                $params[':regional'] = $filters->regional;
            }
            
            if ($filters->agencia !== null) {
                $sql .= " AND e.id_agencia = :agencia";
                $params[':agencia'] = $filters->agencia;
            }
            
            if ($filters->gerenteGestao !== null) {
                $sql .= " AND EXISTS (
                    SELECT 1 FROM " . Tables::D_ESTRUTURA . " ggestao 
                    WHERE ggestao.funcional = :gerente_gestao
                    AND ggestao.id_cargo = " . Cargo::GERENTE_GESTAO . "
                    AND ggestao.id_segmento = e.id_segmento
                    AND ggestao.id_diretoria = e.id_diretoria
                    AND ggestao.id_regional = e.id_regional
                    AND ggestao.id_agencia = e.id_agencia
                )";
                $params[':gerente_gestao'] = $filters->gerenteGestao;
            }
            
            if ($filters->gerente !== null) {
                $sql .= " AND p.funcional = :gerente";
                $params[':gerente'] = $filters->gerente;
            }
            
            if ($filters->familia !== null) {
                $sql .= " AND p.id_familia = :familia";
                $params[':familia'] = $filters->familia;
            }
            
            if ($filters->indicador !== null) {
                $sql .= " AND p.id_indicador = :indicador";
                $params[':indicador'] = $filters->indicador;
            }
        }
        
        $sql .= " ORDER BY p.data_realizado DESC, p.id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(function ($row) {
            $dataRealizadoIso = DateFormatter::toIsoDate(isset($row['data_realizado']) ? $row['data_realizado'] : null);
            $dtAtualizacaoIso = isset($row['dt_atualizacao']) ? DateFormatter::toIsoDate($row['dt_atualizacao']) : null;
            
            $row['data_realizado'] = $dataRealizadoIso;
            $row['dt_atualizacao'] = $dtAtualizacaoIso;
            if (isset($row['meta'])) {
                $row['meta'] = $this->toFloat($row['meta']);
            }
            if (isset($row['realizado'])) {
                $row['realizado'] = $this->toFloat($row['realizado']);
            }
            
            $entity = FPontos::fromArray($row);
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

