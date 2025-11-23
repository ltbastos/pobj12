<?php

namespace App\Domain\DTO;

class FilterDTO
{
    public $segmento = null;
    public $diretoria = null;
    public $regional = null;
    public $agencia = null;
    public $gerenteGestao = null;
    public $gerente = null;
    public $familia = null;
    public $indicador = null;
    public $subindicador = null;
    public $page = null;
    public $limit = null;

    public function __construct(array $filters = [])
    {
        $this->segmento = $filters['segmentoId'] ?? $filters['segmento'] ?? null;
        $this->diretoria = $filters['diretoriaId'] ?? $filters['diretoria'] ?? null;
        $this->regional = $filters['regionalId'] ?? $filters['regional'] ?? null;
        $this->agencia = $filters['agenciaId'] ?? $filters['agencia'] ?? null;
        $this->gerenteGestao = $filters['gerenteGestaoId'] ?? $filters['gerente_gestao'] ?? $filters['gerenteGestao'] ?? null;
        $this->gerente = $filters['gerenteId'] ?? $filters['gerente'] ?? null;
        $this->familia = $filters['familiaId'] ?? $filters['familia'] ?? null;
        $this->indicador = $filters['indicadorId'] ?? $filters['indicador'] ?? null;
        $this->subindicador = $filters['subindicadorId'] ?? $filters['subindicador'] ?? null;
        
        // Paginação
        $this->page = isset($filters['page']) ? max(1, (int)$filters['page']) : null;
        $this->limit = isset($filters['limit']) ? max(1, min(1000, (int)$filters['limit'])) : null;
    }

    public function hasAnyFilter(): bool
    {
        return $this->segmento !== null
            || $this->diretoria !== null
            || $this->regional !== null
            || $this->agencia !== null
            || $this->gerenteGestao !== null
            || $this->gerente !== null
            || $this->familia !== null
            || $this->indicador !== null
            || $this->subindicador !== null;
    }

    public function hasPagination(): bool
    {
        return $this->page !== null && $this->limit !== null;
    }

    public function getOffset(): int
    {
        if (!$this->hasPagination()) {
            return 0;
        }
        return ($this->page - 1) * $this->limit;
    }

    public function toArray(): array
    {
        return [
            'segmento' => $this->segmento,
            'diretoria' => $this->diretoria,
            'regional' => $this->regional,
            'agencia' => $this->agencia,
            'gerente_gestao' => $this->gerenteGestao,
            'gerente' => $this->gerente,
            'familia' => $this->familia,
            'indicador' => $this->indicador,
            'subindicador' => $this->subindicador,
            'page' => $this->page,
            'limit' => $this->limit,
        ];
    }
}

