<?php

namespace App\Infrastructure\Persistence;

// Removed Connection dependency
use PDO;
use App\Domain\DTO\FilterDTO;
use App\Domain\Enum\Cargo;
use App\Domain\Enum\Tables;

class VariavelRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllAsArray(FilterDTO $filters = null): array
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
                FROM " . Tables::F_VARIAVEL . " v
                LEFT JOIN " . Tables::D_ESTRUTURA . " e ON e.funcional = CAST(v.funcional AS CHAR)
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
                $sql .= " AND v.funcional = :gerente";
                $params[':gerente'] = $filters->gerente;
            }
        }
        
        $sql .= " ORDER BY v.dt_atualizacao DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

