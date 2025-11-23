<?php

namespace App\Infrastructure\Persistence;

use PDO;
use App\Domain\DTO\FilterDTO;
use App\Domain\DTO\HistoricoDTO;
use App\Domain\Enum\Tables;
use App\Infrastructure\Helpers\DateFormatter;
use App\Infrastructure\Helpers\ValueFormatter;

/**
 * Repositório para buscar todos os registros de histórico com filtros opcionais
 */
class HistoricoRepository extends BaseRepository
{
    /**
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, HistoricoDTO::class);
    }

    /**
     * Retorna o SELECT completo da consulta
     * @return string
     */
    public function baseSelect(): string
    {
        return "SELECT 
                    nivel,
                    ano,
                    `database` AS data,
                    segmento,
                    segmento_id,
                    diretoria,
                    diretoria_nome,
                    gerencia_regional,
                    gerencia_regional_nome,
                    agencia,
                    agencia_nome,
                    gerente_gestao,
                    gerente_gestao_nome,
                    gerente,
                    gerente_nome,
                    participantes,
                    `rank`,
                    pontos,
                    realizado,
                    meta
                FROM " . Tables::F_HISTORICO_RANKING_POBJ . "
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
            $sql .= " AND segmento_id = :segmento";
            $params[':segmento'] = $filters->getSegmento();
        }

        if ($filters->getDiretoria() !== null) {
            $sql .= " AND diretoria = :diretoria";
            $params[':diretoria'] = $filters->getDiretoria();
        }

        if ($filters->getRegional() !== null) {
            $sql .= " AND gerencia_regional = :regional";
            $params[':regional'] = $filters->getRegional();
        }

        if ($filters->getAgencia() !== null) {
            $sql .= " AND agencia = :agencia";
            $params[':agencia'] = $filters->getAgencia();
        }

        if ($filters->getGerenteGestao() !== null) {
            $sql .= " AND gerente_gestao = :gerente_gestao";
            $params[':gerente_gestao'] = $filters->getGerenteGestao();
        }

        if ($filters->getGerente() !== null) {
            $sql .= " AND gerente = :gerente";
            $params[':gerente'] = $filters->getGerente();
        }

        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Retorna a cláusula ORDER BY
     * @return string
     */
    protected function getOrderBy(): string
    {
        return "ORDER BY `database` DESC, nivel, ano";
    }

    /**
     * Mapeia um array de resultados para HistoricoDTO
     * @param array $row
     * @return HistoricoDTO
     */
    public function mapToDto(array $row): HistoricoDTO
    {
        $dataIso = DateFormatter::toIsoDate($row['data'] ?? null);
            
        return new HistoricoDTO(
            $row['nivel'] ?? null,                                    // nivel
            $row['ano'] ?? null,                                     // ano
            $dataIso,                                                 // data
            $dataIso,                                                 // competencia
            $row['segmento'] ?? null,                                 // segmento
            $row['segmento_id'] ?? null,                              // segmentoId
            $row['diretoria'] ?? null,                                // diretoriaId (usando diretoria como ID)
            $row['diretoria_nome'] ?? null,                           // diretoriaNome
            $row['gerencia_regional'] ?? null,                        // gerenciaId (usando gerencia_regional como ID)
            $row['gerencia_regional_nome'] ?? null,                   // gerenciaNome
            $row['agencia'] ?? null,                                  // agenciaId (usando agencia como ID)
            $row['agencia_nome'] ?? null,                             // agenciaNome
            $row['gerente_gestao'] ?? null,                           // gerenteGestaoId (usando gerente_gestao como ID)
            $row['gerente_gestao_nome'] ?? null,                      // gerenteGestaoNome
            $row['gerente'] ?? null,                                  // gerenteId (usando gerente como ID)
            $row['gerente_nome'] ?? null,                             // gerenteNome
            $row['participantes'] ?? null,                            // participantes
            $row['rank'] ?? null,                                     // rank
            ValueFormatter::toFloat($row['pontos'] ?? null),         // pontos
            ValueFormatter::toFloat($row['realizado'] ?? null),      // realizadoMensal
            ValueFormatter::toFloat($row['meta'] ?? null)             // metaMensal
        );
    }
}
