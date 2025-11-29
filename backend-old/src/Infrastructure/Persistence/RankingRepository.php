<?php

namespace App\Infrastructure\Persistence;

use App\Domain\DTO\FilterDTO;
use App\Domain\DTO\HistoricoDTO;
use App\Domain\Enum\Tables;
use App\Infrastructure\Helpers\DateFormatter;
use App\Infrastructure\Helpers\ValueFormatter;

/**
 * Repositório para buscar dados de ranking da tabela f_historico_ranking_pobj
 */
class RankingRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(HistoricoDTO::class);
    }

    /**
     * Retorna o SELECT completo da consulta com JOINs para obter dados da estrutura
     * @return string
     */
    public function baseSelect(): string
    {
        return "SELECT 
                    r.id,
                    r.data,
                    r.funcional,
                    r.grupo,
                    r.ranking,
                    r.realizado,
                    e.nome as gerente_nome,
                    e.agencia_id,
                    e.segmento_id,
                    e.diretoria_id,
                    e.regional_id,
                    -- Gerente de Gestão (busca pelo cargo_id = 3 na mesma agência)
                    ggestao.funcional as gerente_gestao_funcional,
                    ggestao.nome as gerente_gestao_nome,
                    ggestao.id as gerente_gestao_id,
                    -- Informações da agência
                    a.nome as agencia_nome,
                    -- Informações do segmento
                    s.nome as segmento_nome,
                    -- Informações da diretoria
                    d.nome as diretoria_nome,
                    -- Informações da regional
                    reg.nome as regional_nome,
                    YEAR(r.data) as ano
                FROM " . Tables::F_HISTORICO_RANKING_POBJ . " r
                LEFT JOIN " . Tables::D_ESTRUTURA . " e ON r.funcional = e.funcional
                LEFT JOIN " . Tables::D_ESTRUTURA . " ggestao ON e.agencia_id = ggestao.agencia_id 
                    AND ggestao.cargo_id = 3
                LEFT JOIN agencias a ON e.agencia_id = a.id
                LEFT JOIN segmentos s ON e.segmento_id = s.id
                LEFT JOIN diretorias d ON e.diretoria_id = d.id
                LEFT JOIN regionais reg ON e.regional_id = reg.id
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
        
        // Se não há filtros, retorna todos os dados
        if ($filters === null || !$filters->hasAnyFilter()) {
            return ['sql' => $sql, 'params' => $params];
        }

        // Filtra apenas por funcional (gerente de gestão) quando selecionado
        if ($filters->getGerenteGestao() !== null) {
            $sql .= " AND r.funcional = :gerente_gestao";
            $params[':gerente_gestao'] = $filters->getGerenteGestao();
        }

        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Retorna a cláusula ORDER BY
     * @return string
     */
    protected function getOrderBy(): string
    {
        return "ORDER BY r.ranking ASC, r.realizado DESC, r.data DESC";
    }

    /**
     * Mapeia um array de resultados para HistoricoDTO
     * @param array $row
     * @return HistoricoDTO
     */
    public function mapToDto(array $row): HistoricoDTO
    {
        $dataIso = DateFormatter::toIsoDate($row['data'] ?? null);
        $ano = isset($row['ano']) ? (int)$row['ano'] : null;
        
        // Converte ranking para inteiro se não for null
        $ranking = isset($row['ranking']) ? (int)$row['ranking'] : null;
            
        return new HistoricoDTO(
            $ano,                                            // ano (primeiro parâmetro)
            $dataIso,                                       // data
            $dataIso,                                       // competencia
            $row['segmento_nome'] ?? null,                  // segmento
            isset($row['segmento_id']) ? (string)$row['segmento_id'] : null, // segmentoId
            isset($row['diretoria_id']) ? (string)$row['diretoria_id'] : null, // diretoriaId
            $row['diretoria_nome'] ?? null,                 // diretoriaNome
            isset($row['regional_id']) ? (string)$row['regional_id'] : null, // gerenciaId
            $row['regional_nome'] ?? null,                  // gerenciaNome
            isset($row['agencia_id']) ? (string)$row['agencia_id'] : null, // agenciaId
            $row['agencia_nome'] ?? null,                    // agenciaNome
            $row['gerente_gestao_funcional'] ?? null,       // gerenteGestaoId (funcional)
            $row['gerente_gestao_nome'] ?? null,            // gerenteGestaoNome
            $row['funcional'] ?? null,                      // gerenteId (funcional do gerente)
            $row['gerente_nome'] ?? null,                   // gerenteNome
            null,                                           // participantes
            $ranking,                                       // rank
            null,                                           // pontos
            ValueFormatter::toFloat($row['realizado'] ?? null), // realizadoMensal
            null                                            // metaMensal
        );
    }
}

