<?php

namespace App\Infrastructure\Persistence;

// Removed Connection dependency
use PDO;
use App\Domain\DTO\LeadsDTO;
use App\Infrastructure\Helpers\DateFormatter;

class LeadsRepository
{
    private $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findAllAsArray(): array
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
                FROM f_leads_propensos
                ORDER BY `database` DESC, nome_empresa";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
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

