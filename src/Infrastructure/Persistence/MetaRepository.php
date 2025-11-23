<?php

namespace App\Infrastructure\Persistence;

// Removed Connection dependency
use PDO;
use App\Domain\DTO\FilterDTO;
use App\Domain\DTO\MetaDTO;
use App\Domain\Enum\Tables;
use App\Infrastructure\Helpers\DateFormatter;

class MetaRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllAsArray(FilterDTO $filters = null): array
    {
        $sql = "SELECT DISTINCT
                    m.id AS registro_id,
                    m.data_meta AS data,
                    m.data_meta AS competencia,
                    m.funcional,
                    m.meta_mensal,
                    m.id_familia AS familia_id,
                    m.id_indicador AS indicador_id,
                    m.id_subindicador AS subindicador_id,
                    m.segmento_id,
                    m.diretoria_id,
                    m.gerencia_regional_id,
                    m.agencia_id,
                    COALESCE(u.segmento, '') AS segmento,
                    COALESCE(u.diretoria, '') AS diretoria_nome,
                    COALESCE(u.regional, '') AS gerencia_regional_nome,
                    COALESCE(u.regional, '') AS regional_nome,
                    COALESCE(u.agencia, '') AS agencia_nome,
                    COALESCE(u.funcional, '') AS gerente_gestao_id,
                    COALESCE(u.nome, '') AS gerente_gestao_nome,
                    COALESCE(u.funcional, '') AS gerente_id,
                    COALESCE(u.nome, '') AS gerente_nome,
                    COALESCE(p.familia, '') AS familia_nome,
                    COALESCE(CAST(p.id_indicador AS CHAR), '') AS id_indicador,
                    COALESCE(p.indicador, '') AS ds_indicador,
                    COALESCE(p.subindicador, '') AS subproduto,
                    COALESCE(CAST(p.id_subindicador AS CHAR), '0') AS id_subindicador
                FROM " . Tables::F_META . " m
                LEFT JOIN " . Tables::D_ESTRUTURA . " u ON u.id_segmento = m.segmento_id
                    AND u.id_diretoria = m.diretoria_id
                    AND u.id_regional = m.gerencia_regional_id
                    AND u.id_agencia = m.agencia_id
                    AND u.funcional = m.funcional
                LEFT JOIN " . Tables::D_PRODUTOS . " p ON p.id_indicador = m.id_indicador
                    AND (p.id_subindicador = m.id_subindicador OR (p.id_subindicador IS NULL AND m.id_subindicador IS NULL))
                WHERE 1=1";
        
        $params = [];
        
        if ($filters !== null && $filters->hasAnyFilter()) {
            if ($filters->segmento !== null) {
                $sql .= " AND m.segmento_id = :segmento";
                $params[':segmento'] = $filters->segmento;
            }
            if ($filters->diretoria !== null) {
                $sql .= " AND m.diretoria_id = :diretoria";
                $params[':diretoria'] = $filters->diretoria;
            }
            if ($filters->regional !== null) {
                $sql .= " AND m.gerencia_regional_id = :regional";
                $params[':regional'] = $filters->regional;
            }
            if ($filters->agencia !== null) {
                $sql .= " AND m.agencia_id = :agencia";
                $params[':agencia'] = $filters->agencia;
            }
            if ($filters->gerenteGestao !== null) {
                $sql .= " AND u.funcional = :gerente_gestao";
                $params[':gerente_gestao'] = $filters->gerenteGestao;
            }
            if ($filters->gerente !== null) {
                $sql .= " AND m.funcional = :gerente";
                $params[':gerente'] = $filters->gerente;
            }
            if ($filters->familia !== null) {
                $sql .= " AND m.id_familia = :familia";
                $params[':familia'] = $filters->familia;
            }
            if ($filters->indicador !== null) {
                $sql .= " AND m.id_indicador = :indicador";
                $params[':indicador'] = $filters->indicador;
            }
            if ($filters->subindicador !== null) {
                $sql .= " AND m.id_subindicador = :subindicador";
                $params[':subindicador'] = $filters->subindicador;
            }
        }
        
        $sql .= " ORDER BY m.data_meta DESC, m.id";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(function ($row) {
            $dataIso = DateFormatter::toIsoDate(isset($row['data']) ? $row['data'] : null);
            
            $registroId = isset($row['registro_id']) ? $row['registro_id'] : null;
            $segmentoId = isset($row['segmento_id']) ? $row['segmento_id'] : null;
            $diretoriaId = isset($row['diretoria_id']) ? $row['diretoria_id'] : null;
            $gerenciaRegionalId = isset($row['gerencia_regional_id']) ? $row['gerencia_regional_id'] : null;
            $agenciaId = isset($row['agencia_id']) ? $row['agencia_id'] : null;
            $familiaId = isset($row['familia_id']) ? $row['familia_id'] : null;
            $indicadorId = isset($row['indicador_id']) ? $row['indicador_id'] : null;
            
            $dto = new MetaDTO(
                $registroId !== null ? (string)$registroId : null,
                isset($row['segmento']) ? $row['segmento'] : null,
                $segmentoId !== null ? (string)$segmentoId : null,
                $diretoriaId !== null ? (string)$diretoriaId : null,
                isset($row['diretoria_nome']) ? $row['diretoria_nome'] : null,
                $gerenciaRegionalId !== null ? (string)$gerenciaRegionalId : null,
                isset($row['gerencia_regional_nome']) ? $row['gerencia_regional_nome'] : null,
                isset($row['regional_nome']) ? $row['regional_nome'] : null,
                $agenciaId !== null ? (string)$agenciaId : null,
                isset($row['agencia_nome']) ? $row['agencia_nome'] : null,
                isset($row['gerente_gestao_id']) ? $row['gerente_gestao_id'] : null,
                isset($row['gerente_gestao_nome']) ? $row['gerente_gestao_nome'] : null,
                isset($row['gerente_id']) ? $row['gerente_id'] : null,
                isset($row['gerente_nome']) ? $row['gerente_nome'] : null,
                $familiaId !== null ? (string)$familiaId : null,
                isset($row['familia_nome']) ? $row['familia_nome'] : null,
                isset($row['id_indicador']) ? $row['id_indicador'] : null,
                isset($row['ds_indicador']) ? $row['ds_indicador'] : null,
                isset($row['subproduto']) ? $row['subproduto'] : null,
                isset($row['id_subindicador']) ? $row['id_subindicador'] : null,
                null,
                null,
                null,
                null,
                null,
                null,
                $dataIso,
                $dataIso,
                $this->toFloat(isset($row['meta_mensal']) ? $row['meta_mensal'] : null)
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

