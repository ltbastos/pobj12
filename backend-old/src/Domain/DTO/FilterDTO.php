<?php

namespace App\Domain\DTO;

class FilterDTO
{
    public $page;
    public $limit;
    public $filters;
    private $paginationRequested;

    public function __construct(array $params = [])
    {
        // Verifica se page ou limit foram explicitamente solicitados
        $this->paginationRequested = isset($params['page']) || isset($params['limit']);
        
        $this->page = isset($params['page']) ? (int)$params['page'] : 1;
        $this->limit = isset($params['limit']) ? (int)$params['limit'] : 20;

        unset($params['page'], $params['limit']);
        $this->filters = $params;
    }

    public function hasAnyFilter(): bool
    {
        return !empty($this->filters);
    }

    public function hasPagination(): bool
    {
        // Só retorna true se a paginação foi explicitamente solicitada
        return $this->paginationRequested && $this->page > 0 && $this->limit > 0;
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }

    /**
     * Obtém um valor de filtro específico
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        // Para dataInicio e dataFim, não adiciona 'Id' no final
        if ($key === 'dataInicio' || $key === 'dataFim') {
            if (isset($this->filters[$key])) {
                return $this->filters[$key];
            }
            // Tenta variações comuns
            $variations = [
                $this->normalizeKey($key),
                strtolower($key),
                strtoupper($key)
            ];
            foreach ($variations as $k) {
                if (isset($this->filters[$k])) {
                    return $this->filters[$k];
                }
            }
            return $default;
        }
        
        // Suporta múltiplas chaves para compatibilidade
        $keys = [
            $key . 'Id',
            $key,
            $this->normalizeKey($key),
        ];

        foreach ($keys as $k) {
            if (isset($this->filters[$k])) {
                return $this->filters[$k];
            }
        }

        return $default;
    }

    /**
     * Normaliza a chave do filtro
     * @param string $key
     * @return string
     */
    private function normalizeKey(string $key): string
    {
        $normalizations = [
            'segmento' => ['segmentoId', 'segmento'],
            'diretoria' => ['diretoriaId', 'diretoria'],
            'regional' => ['regionalId', 'regional'],
            'agencia' => ['agenciaId', 'agencia'],
            'gerenteGestao' => ['gerenteGestaoId', 'gerente_gestao', 'gerenteGestao'],
            'gerente' => ['gerenteId', 'gerente'],
            'familia' => ['familiaId', 'familia'],
            'indicador' => ['indicadorId', 'indicador'],
            'subindicador' => ['subindicadorId', 'subindicador'],
            'dataInicio' => ['dataInicio'],
            'dataFim' => ['dataFim'],
        ];

        return $normalizations[$key][0] ?? $key;
    }

    // Métodos helper para acesso direto aos filtros comuns
    public function getSegmento()
    {
        return $this->get('segmento');
    }

    public function getDiretoria()
    {
        return $this->get('diretoria');
    }

    public function getRegional()
    {
        return $this->get('regional');
    }

    public function getAgencia()
    {
        return $this->get('agencia');
    }

    public function getGerenteGestao()
    {
        return $this->get('gerenteGestao');
    }

    public function getGerente()
    {
        return $this->get('gerente');
    }

    public function getFamilia()
    {
        return $this->get('familia');
    }

    public function getIndicador()
    {
        return $this->get('indicador');
    }

    public function getSubindicador()
    {
        return $this->get('subindicador');
    }

    public function getDataInicio()
    {
        return $this->get('dataInicio');
    }

    public function getDataFim()
    {
        return $this->get('dataFim');
    }
    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'limit' => $this->limit,
            'filters' => $this->filters,
        ];
    }
}
