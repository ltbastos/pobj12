<?php

namespace App\Infrastructure\Persistence;

// Removed Connection dependency
use PDO;
use App\Domain\DTO\FilterDTO;
use App\Domain\DTO\LeadsDTO;
use App\Domain\Enum\Tables;
use App\Infrastructure\Helpers\DateFormatter;

class LeadsRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllAsArray(FilterDTO $filters = null): array
    {
        $sql = "SELECT 
                    `database` AS data,
                    nome_empresa,
                    cnae,
                    segmento_cliente,
                    segmento_cliente_id,
                    produto_propenso,
                    familia_produto_propenso,
                    secao_produto_propenso,
                    id_indicador,
                    id_subindicador,
                    data_contato,
                    comentario,
                    responsavel_contato,
                    diretoria_cliente,
                    diretoria_cliente_id,
                    regional_cliente,
                    regional_cliente_id,
                    agencia_cliente,
                    agencia_cliente_id,
                    gerente_gestao_cliente,
                    gerente_gestao_cliente_id,
                    gerente_cliente,
                    gerente_cliente_id,
                    credito_pre_aprovado,
                    origem_lead
                FROM " . Tables::F_LEADS_PROPENSOS . "
                WHERE 1=1";
        
        $params = [];
        
        if ($filters !== null && $filters->hasAnyFilter()) {
            if ($filters->segmento !== null) {
                $sql .= " AND segmento_cliente_id = :segmento";
                $params[':segmento'] = $filters->segmento;
            }
            if ($filters->diretoria !== null) {
                $sql .= " AND diretoria_cliente_id = :diretoria";
                $params[':diretoria'] = $filters->diretoria;
            }
            if ($filters->regional !== null) {
                $sql .= " AND regional_cliente_id = :regional";
                $params[':regional'] = $filters->regional;
            }
            if ($filters->agencia !== null) {
                $sql .= " AND agencia_cliente_id = :agencia";
                $params[':agencia'] = $filters->agencia;
            }
            if ($filters->gerenteGestao !== null) {
                $sql .= " AND gerente_gestao_cliente_id = :gerente_gestao";
                $params[':gerente_gestao'] = $filters->gerenteGestao;
            }
            if ($filters->gerente !== null) {
                $sql .= " AND gerente_cliente_id = :gerente";
                $params[':gerente'] = $filters->gerente;
            }
            if ($filters->familia !== null) {
                $sql .= " AND familia_produto_propenso = :familia";
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
        
        $sql .= " ORDER BY `database` DESC, nome_empresa";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return array_map(function ($row) {
            $dataIso = DateFormatter::toIsoDate(isset($row['data']) ? $row['data'] : null);
            $dataContatoIso = DateFormatter::toIsoDate(isset($row['data_contato']) ? $row['data_contato'] : null);
            $registroId = $dataIso . '_' . (isset($row['nome_empresa']) ? $row['nome_empresa'] : '');
            
            $dto = new LeadsDTO(
                $registroId,
                $dataIso,
                $dataIso,
                isset($row['nome_empresa']) ? $row['nome_empresa'] : null,
                isset($row['cnae']) ? $row['cnae'] : null,
                isset($row['segmento_cliente']) ? $row['segmento_cliente'] : null,
                isset($row['segmento_cliente_id']) ? $row['segmento_cliente_id'] : null,
                isset($row['produto_propenso']) ? $row['produto_propenso'] : null,
                isset($row['familia_produto_propenso']) ? $row['familia_produto_propenso'] : null,
                isset($row['secao_produto_propenso']) ? $row['secao_produto_propenso'] : null,
                isset($row['id_indicador']) ? $row['id_indicador'] : null,
                isset($row['id_subindicador']) ? $row['id_subindicador'] : null,
                $dataContatoIso,
                isset($row['comentario']) ? $row['comentario'] : null,
                isset($row['responsavel_contato']) ? $row['responsavel_contato'] : null,
                isset($row['diretoria_cliente']) ? $row['diretoria_cliente'] : null,
                isset($row['diretoria_cliente_id']) ? $row['diretoria_cliente_id'] : null,
                isset($row['regional_cliente']) ? $row['regional_cliente'] : null,
                isset($row['regional_cliente_id']) ? $row['regional_cliente_id'] : null,
                isset($row['agencia_cliente']) ? $row['agencia_cliente'] : null,
                isset($row['agencia_cliente_id']) ? $row['agencia_cliente_id'] : null,
                isset($row['gerente_gestao_cliente']) ? $row['gerente_gestao_cliente'] : null,
                isset($row['gerente_gestao_cliente_id']) ? $row['gerente_gestao_cliente_id'] : null,
                isset($row['gerente_cliente']) ? $row['gerente_cliente'] : null,
                isset($row['gerente_cliente_id']) ? $row['gerente_cliente_id'] : null,
                (($credito = isset($row['credito_pre_aprovado']) ? $row['credito_pre_aprovado'] : null) !== null) ? (string)$credito : null,
                isset($row['origem_lead']) ? $row['origem_lead'] : null
            );
            
            return $dto->toArray();
        }, $results);
    }
}

