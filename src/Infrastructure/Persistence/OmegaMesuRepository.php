<?php

namespace App\Infrastructure\Persistence;

// Removed Connection dependency
use PDO;
use App\Domain\DTO\FilterDTO;
use App\Domain\DTO\OmegaMesuDTO;
use App\Domain\Enum\Tables;

class OmegaMesuRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAll(FilterDTO $filters = null): array
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
                FROM " . Tables::D_ESTRUTURA . "
                WHERE segmento IS NOT NULL";
        
        $params = [];
        
        if ($filters !== null && $filters->hasAnyFilter()) {
            if ($filters->segmento !== null) {
                $sql .= " AND id_segmento = :segmento";
                $params[':segmento'] = $filters->segmento;
            }
            if ($filters->diretoria !== null) {
                $sql .= " AND id_diretoria = :diretoria";
                $params[':diretoria'] = $filters->diretoria;
            }
            if ($filters->regional !== null) {
                $sql .= " AND id_regional = :regional";
                $params[':regional'] = $filters->regional;
            }
            if ($filters->agencia !== null) {
                $sql .= " AND id_agencia = :agencia";
                $params[':agencia'] = $filters->agencia;
            }
            if ($filters->gerenteGestao !== null) {
                $sql .= " AND funcional = :gerente_gestao";
                $params[':gerente_gestao'] = $filters->gerenteGestao;
            }
            if ($filters->gerente !== null) {
                $sql .= " AND funcional = :gerente";
                $params[':gerente'] = $filters->gerente;
            }
        }
        
        $sql .= " ORDER BY segmento, diretoria, regional, agencia";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(function ($row) {
            $segmentoId = isset($row['segmento_id']) ? $row['segmento_id'] : null;
            $diretoriaId = isset($row['diretoria_id']) ? $row['diretoria_id'] : null;
            $gerenciaRegionalId = isset($row['gerencia_regional_id']) ? $row['gerencia_regional_id'] : null;
            $agenciaId = isset($row['agencia_id']) ? $row['agencia_id'] : null;
            $gerenteGestaoId = isset($row['gerente_gestao_id']) ? $row['gerente_gestao_id'] : null;
            $gerenteId = isset($row['gerente_id']) ? $row['gerente_id'] : null;
            
            $dto = new OmegaMesuDTO(
                isset($row['segmento']) ? $row['segmento'] : null,
                $segmentoId !== null ? (string)$segmentoId : null,
                isset($row['diretoria']) ? $row['diretoria'] : null,
                $diretoriaId !== null ? (string)$diretoriaId : null,
                isset($row['gerencia_regional']) ? $row['gerencia_regional'] : null,
                $gerenciaRegionalId !== null ? (string)$gerenciaRegionalId : null,
                isset($row['agencia']) ? $row['agencia'] : null,
                $agenciaId !== null ? (string)$agenciaId : null,
                isset($row['gerente_gestao']) ? $row['gerente_gestao'] : null,
                $gerenteGestaoId !== null ? (string)$gerenteGestaoId : null,
                isset($row['gerente']) ? $row['gerente'] : null,
                $gerenteId !== null ? (string)$gerenteId : null
            );
            
            return $dto->toArray();
        }, $results);
    }
}

