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
        // Correspondendo explicitamente aos nomes da tabela fornecida no exemplo
        return "SELECT 
                    id AS nivel,
                    data,
                    id_segmento,
                    segmento,
                    id_diretoria,
                    diretoria,
                    id_regional,
                    regional,
                    id_agencia,
                    agencia,
                    funcional,
                    grupo,
                    ranking,
                    realizado
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
            $sql .= " AND id_segmento = :segmento";
            $params[':segmento'] = $filters->getSegmento();
        }

        if ($filters->getDiretoria() !== null) {
            $sql .= " AND id_diretoria = :diretoria";
            $params[':diretoria'] = $filters->getDiretoria();
        }

        if ($filters->getRegional() !== null) {
            $sql .= " AND id_regional = :regional";
            $params[':regional'] = $filters->getRegional();
        }

        if ($filters->getAgencia() !== null) {
            $sql .= " AND id_agencia = :agencia";
            $params[':agencia'] = $filters->getAgencia();
        }

        if ($filters->getGerenteGestao() !== null) {
            $sql .= " AND funcional = :gerente_gestao";
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
