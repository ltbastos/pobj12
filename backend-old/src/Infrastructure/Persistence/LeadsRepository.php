<?php

namespace App\Infrastructure\Persistence;

use App\Domain\DTO\FilterDTO;
use App\Domain\DTO\LeadsDTO;
use App\Domain\Enum\Tables;
use App\Infrastructure\Helpers\DateFormatter;
use App\Infrastructure\Helpers\ValueFormatter;

/**
 * Repositório para buscar todos os registros de leads com filtros opcionais
 */
class LeadsRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(LeadsDTO::class);
    }

    /**
     * Retorna o SELECT completo da consulta
     * @return string
     */
    public function baseSelect(): string
    {
        return "SELECT 
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
    }

    /**
     * Constrói os filtros WHERE baseado no FilterDTO
     * @param FilterDTO|null $filters
     * @return array ['sql' => string, 'params' => array]
     */
    public function builderFilter(FilterDTO $filters = null): array
    {
        $sql = "";
        $params = [];

        if ($filters === null || !$filters->hasAnyFilter()) {
            return ['sql' => $sql, 'params' => $params];
        }

        if ($filters->getSegmento() !== null) {
            $sql .= " AND segmento_cliente_id = :segmento";
            $params[':segmento'] = $filters->getSegmento();
        }

        if ($filters->getDiretoria() !== null) {
            $sql .= " AND diretoria_cliente_id = :diretoria";
            $params[':diretoria'] = $filters->getDiretoria();
        }

        if ($filters->getRegional() !== null) {
            $sql .= " AND regional_cliente_id = :regional";
            $params[':regional'] = $filters->getRegional();
        }

        if ($filters->getAgencia() !== null) {
            $sql .= " AND agencia_cliente_id = :agencia";
            $params[':agencia'] = $filters->getAgencia();
        }

        if ($filters->getGerenteGestao() !== null) {
            $sql .= " AND gerente_gestao_cliente_id = :gerente_gestao";
            $params[':gerente_gestao'] = $filters->getGerenteGestao();
        }

        if ($filters->getGerente() !== null) {
            $sql .= " AND gerente_cliente_id = :gerente";
            $params[':gerente'] = $filters->getGerente();
        }

        if ($filters->getFamilia() !== null) {
            $sql .= " AND familia_produto_propenso = :familia";
            $params[':familia'] = $filters->getFamilia();
        }

        if ($filters->getIndicador() !== null) {
            $sql .= " AND id_indicador = :indicador";
            $params[':indicador'] = $filters->getIndicador();
        }

        if ($filters->getSubindicador() !== null) {
            $sql .= " AND id_subindicador = :subindicador";
            $params[':subindicador'] = $filters->getSubindicador();
        }

        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Retorna a cláusula ORDER BY
     * @return string
     */
    protected function getOrderBy(): string
    {
        return "ORDER BY `database` DESC, nome_empresa";
    }

    /**
     * Mapeia um array de resultados para LeadsDTO
     * @param array $row
     * @return LeadsDTO
     */
    public function mapToDto(array $row): LeadsDTO
    {
        $dataIso = DateFormatter::toIsoDate($row['data'] ?? null);
        $dataContatoIso = DateFormatter::toIsoDate($row['data_contato'] ?? null);
        $registroId = $dataIso . '_' . ($row['nome_empresa'] ?? '');
        
        $creditoPreAprovado = $row['credito_pre_aprovado'] ?? null;
        $creditoPreAprovado = $creditoPreAprovado !== null ? (string)$creditoPreAprovado : null;

        return new LeadsDTO(
            $registroId,                                             // registroId
            $dataIso,                                                 // data
            $dataIso,                                                 // competencia
            $row['nome_empresa'] ?? null,                            // nomeEmpresa
            $row['cnae'] ?? null,                                    // cnae
            $row['segmento_cliente'] ?? null,                        // segmento
            $row['segmento_cliente_id'] ?? null,                     // segmentoId
            $row['produto_propenso'] ?? null,                        // produtoPropenso
            $row['familia_produto_propenso'] ?? null,                // familiaNome
            $row['secao_produto_propenso'] ?? null,                  // subproduto
            $row['id_indicador'] ?? null,                            // idIndicador
            $row['id_subindicador'] ?? null,                         // subindicadorCodigo
            $dataContatoIso,                                          // dataContato
            $row['comentario'] ?? null,                              // comentario
            $row['responsavel_contato'] ?? null,                     // responsavelContato
            $row['diretoria_cliente'] ?? null,                       // diretoriaNome
            $row['diretoria_cliente_id'] ?? null,                    // diretoriaId
            $row['regional_cliente'] ?? null,                       // regionalNome
            $row['regional_cliente_id'] ?? null,                     // gerenciaId
            $row['agencia_cliente'] ?? null,                         // agenciaNome
            $row['agencia_cliente_id'] ?? null,                      // agenciaId
            $row['gerente_gestao_cliente'] ?? null,                  // gerenteGestaoNome
            $row['gerente_gestao_cliente_id'] ?? null,               // gerenteGestaoId
            $row['gerente_cliente'] ?? null,                         // gerenteNome
            $row['gerente_cliente_id'] ?? null,                      // gerenteId
            $creditoPreAprovado,                                      // creditoPreAprovado
            $row['origem_lead'] ?? null                              // origemLead
        );
    }
}
