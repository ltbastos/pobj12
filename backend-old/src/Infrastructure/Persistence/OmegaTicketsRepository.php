<?php

namespace App\Infrastructure\Persistence;

use App\Domain\DTO\FilterDTO;
use App\Domain\DTO\OmegaTicketDTO;
use App\Domain\Enum\Tables;
use App\Infrastructure\Helpers\DateFormatter;

/**
 * Repositório para buscar todos os registros de tickets Omega
 */
class OmegaTicketsRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(OmegaTicketDTO::class);
    }

    /**
     * Retorna o SELECT completo da consulta
     * @return string
     */
    public function baseSelect(): string
    {
        return "SELECT 
                    id,
                    subject,
                    company,
                    product_id,
                    product_label,
                    family,
                    section,
                    queue,
                    category,
                    status,
                    priority,
                    opened,
                    updated,
                    due_date,
                    requester_id,
                    owner_id,
                    team_id,
                    history,
                    diretoria,
                    gerencia,
                    agencia,
                    gerente_gestao,
                    gerente,
                    credit,
                    attachment
                FROM " . Tables::OMEGA_CHAMADOS . "
                WHERE 1=1";
    }

    /**
     * Constrói os filtros WHERE baseado no FilterDTO
     * OmegaTickets não utiliza filtros, então sempre retorna vazio
     * @param FilterDTO|null $filters
     * @return array ['sql' => string, 'params' => array]
     */
    public function builderFilter(FilterDTO $filters = null): array
    {
        // OmegaTickets não utiliza filtros
        return ['sql' => '', 'params' => []];
    }

    /**
     * Retorna a cláusula ORDER BY
     * @return string
     */
    protected function getOrderBy(): string
    {
        return "ORDER BY updated DESC, opened DESC";
    }

    /**
     * Mapeia um array de resultados para OmegaTicketDTO
     * @param array $row
     * @return OmegaTicketDTO
     */
    public function mapToDto(array $row): OmegaTicketDTO
    {
        $opened = isset($row['opened']) && $row['opened'] ? $this->formatDateTime($row['opened']) : null;
        $updated = isset($row['updated']) && $row['updated'] ? $this->formatDateTime($row['updated']) : null;
        $dueDate = isset($row['due_date']) && $row['due_date'] ? $this->formatDateTime($row['due_date']) : null;

        return new OmegaTicketDTO(
            $row['id'] ?? null,
            $row['subject'] ?? null,
            $row['company'] ?? null,
            $row['product_id'] ?? null,
            $row['product_label'] ?? null,
            $row['family'] ?? null,
            $row['section'] ?? null,
            $row['queue'] ?? null,
            $row['category'] ?? null,
            $row['status'] ?? null,
            $row['priority'] ?? null,
            $opened,
            $updated,
            $dueDate,
            $row['requester_id'] ?? null,
            $row['owner_id'] ?? null,
            $row['team_id'] ?? null,
            $row['history'] ?? null,
            $row['diretoria'] ?? null,
            $row['gerencia'] ?? null,
            $row['agencia'] ?? null,
            $row['gerente_gestao'] ?? null,
            $row['gerente'] ?? null,
            $row['credit'] ?? null,
            $row['attachment'] ?? null
        );
    }

    /**
     * Formata um valor para datetime ISO (Y-m-d H:i:s)
     * @param mixed $value
     * @return string|null
     */
    private function formatDateTime($value)
    {
        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }
        
        if (is_string($value) && !empty($value)) {
            $timestamp = strtotime($value);
            if ($timestamp !== false) {
                return date('Y-m-d H:i:s', $timestamp);
            }
        }
        
        return null;
    }
}
