<?php

namespace App\Infrastructure\Persistence;

use App\Domain\DTO\FilterDTO;
use App\Domain\DTO\CalendarioDTO;
use App\Domain\Enum\Tables;
use App\Infrastructure\Helpers\DateFormatter;

/**
 * Repositório para buscar todos os registros do calendário
 */
class CalendarioRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(CalendarioDTO::class);
    }

    /**
     * Retorna o SELECT completo da consulta
     * @return string
     */
    public function baseSelect(): string
    {
        return "SELECT 
                    data,
                    ano,
                    mes,
                    mes_nome,
                    dia,
                    dia_da_semana,
                    semana,
                    trimestre,
                    semestre,
                    eh_dia_util
                FROM " . Tables::D_CALENDARIO . "
                WHERE 1=1";
    }

    /**
     * Constrói os filtros WHERE baseado no FilterDTO
     * Calendario não utiliza filtros, então sempre retorna vazio
     * @param FilterDTO|null $filters
     * @return array ['sql' => string, 'params' => array]
     */
    public function builderFilter(FilterDTO $filters = null): array
    {
        // Calendario não utiliza filtros
        return ['sql' => '', 'params' => []];
    }

    /**
     * Retorna a cláusula ORDER BY
     * @return string
     */
    protected function getOrderBy(): string
    {
        return "ORDER BY data";
    }

    /**
     * Mapeia um array de resultados para CalendarioDTO
     * @param array $row
     * @return CalendarioDTO
     */
    public function mapToDto(array $row): CalendarioDTO
    {
        $dataIso = DateFormatter::toIsoDate($row['data'] ?? null);
        $ehDiaUtil = isset($row['eh_dia_util']) ? ($row['eh_dia_util'] ? 'Sim' : 'Não') : null;
        
        return new CalendarioDTO(
            $dataIso,                                    // data
            $dataIso,                                    // competencia
            $row['ano'] ?? null,                        // ano
            $row['mes'] ?? null,                        // mes
            $row['mes_nome'] ?? null,                   // mesNome
            $row['dia'] ?? null,                        // dia
            $row['dia_da_semana'] ?? null,              // diaDaSemana
            $row['semana'] ?? null,                     // semana
            $row['trimestre'] ?? null,                  // trimestre
            $row['semestre'] ?? null,                   // semestre
            $ehDiaUtil                                  // ehDiaUtil
        );
    }

    /**
     * Busca todos os registros do calendário sem necessidade de filtros
     * @return array
     */
    public function fetchAll(): array
    {
        return $this->fetch(null);
    }
}
