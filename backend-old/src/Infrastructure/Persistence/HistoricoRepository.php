<?php

namespace App\Infrastructure\Persistence;

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
    public function __construct()
    {
        parent::__construct(HistoricoDTO::class);
    }

    /**
     * Retorna o SELECT completo da consulta
     * @return string
     */
    public function baseSelect(): string
    {
        return "SELECT 
                    h.id AS nivel,
                    h.data,
                    e.segmento_id AS id_segmento,
                    COALESCE(seg.nome, '') AS segmento,
                    e.diretoria_id AS id_diretoria,
                    COALESCE(dir.nome, '') AS diretoria,
                    e.regional_id AS id_regional,
                    COALESCE(reg.nome, '') AS regional,
                    e.agencia_id AS id_agencia,
                    COALESCE(ag.nome, '') AS agencia,
                    h.funcional,
                    h.grupo,
                    h.ranking,
                    h.realizado
                FROM " . Tables::F_HISTORICO_RANKING_POBJ . " h
                LEFT JOIN " . Tables::D_ESTRUTURA . " e ON e.funcional = h.funcional
                LEFT JOIN segmentos seg ON seg.id = e.segmento_id
                LEFT JOIN diretorias dir ON dir.id = e.diretoria_id
                LEFT JOIN regionais reg ON reg.id = e.regional_id
                LEFT JOIN agencias ag ON ag.id = e.agencia_id
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
            $sql .= " AND e.segmento_id = :segmento";
            $params[':segmento'] = $filters->getSegmento();
        }

        if ($filters->getDiretoria() !== null) {
            $sql .= " AND e.diretoria_id = :diretoria";
            $params[':diretoria'] = $filters->getDiretoria();
        }

        if ($filters->getRegional() !== null) {
            $sql .= " AND e.regional_id = :regional";
            $params[':regional'] = $filters->getRegional();
        }

        if ($filters->getAgencia() !== null) {
            $sql .= " AND e.agencia_id = :agencia";
            $params[':agencia'] = $filters->getAgencia();
        }

        if ($filters->getGerenteGestao() !== null) {
            $sql .= " AND h.funcional = :gerente_gestao";
            $params[':gerente_gestao'] = $filters->getGerenteGestao();
        }

        // No explicit "gerente" field in new schema, could adapt if such field exists

        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Retorna a cláusula ORDER BY
     * @return string
     */
    protected function getOrderBy(): string
    {
        // Supondo que a ordenação principal seja por ranking, depois por realizado desc
        return "ORDER BY ranking ASC, realizado DESC";
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
            $row['grupo'] ?? null,                          // nivel -> usando grupo como nivel
            null,                                           // ano -> não fornecido explicitamente
            $dataIso,                                       // data
            $dataIso,                                       // competencia
            $row['segmento'] ?? null,                       // segmento
            $row['id_segmento'] ?? null,                    // segmentoId
            $row['id_diretoria'] ?? null,                   // diretoriaId
            $row['diretoria'] ?? null,                      // diretoriaNome
            $row['id_regional'] ?? null,                    // gerenciaId
            $row['regional'] ?? null,                       // gerenciaNome
            $row['id_agencia'] ?? null,                     // agenciaId
            $row['agencia'] ?? null,                        // agenciaNome
            $row['funcional'] ?? null,                      // gerenteGestaoId (funcional do gerente)
            null,                                           // gerenteGestaoNome
            null,                                           // gerenteId
            null,                                           // gerenteNome
            null,                                           // participantes (não presente)
            $row['ranking'] ?? null,                        // rank
            null,                                           // pontos (não presente)
            ValueFormatter::toFloat($row['realizado'] ?? null), // realizadoMensal
            null                                            // metaMensal (não presente)
        );
    }
}
