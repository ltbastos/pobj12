<?php

namespace App\Infrastructure\Persistence;

use Illuminate\Database\Capsule\Manager as DB;
use App\Domain\DTO\FilterDTO;
use App\Infrastructure\Persistence\Contracts\BaseRepositoryInterface;

abstract class BaseRepository implements BaseRepositoryInterface
{
    protected $dtoClass;

    public function __construct(string $dtoClass = null)
    {
        $this->dtoClass = $dtoClass;
    }

    /**
     * Mapeia um array de resultados para DTO
     * Deve ser implementado pelos repositórios filhos
     * @param array $row
     * @return mixed
     */
    abstract public function mapToDto(array $row);

    protected function getOrderBy(): string
    {
        return "";
    }

    /**
     * Retorna o SQL base para contar registros (sem SELECT específico)
     * Remove GROUP BY se existir, pois COUNT precisa ser feito antes do agrupamento
     * @return string
     */
    protected function baseCountSelect(): string
    {
        $baseSelect = $this->baseSelect();
        $orderBy = $this->getOrderBy();
        
        // Remove o SELECT e campos, mantém FROM e JOINs
        $fromPos = stripos($baseSelect, 'FROM');
        if ($fromPos === false) {
            return "SELECT COUNT(*) as total";
        }
        
        $sql = "SELECT COUNT(*) as total " . substr($baseSelect, $fromPos);
        
        // Remove GROUP BY e ORDER BY do count (se existirem)
        $sql = preg_replace('/\s+GROUP BY.*?(?=ORDER BY|$)/is', '', $sql);
        $sql = preg_replace('/\s+ORDER BY.*?$/is', '', $sql);
        
        return $sql;
    }

    /**
     * Conta o total de registros com os filtros aplicados
     * @param FilterDTO|null $filters
     * @return int
     */
    protected function count(FilterDTO $filters = null): int
    {
        $sql = $this->baseCountSelect();
        $filterResult = $this->builderFilter($filters);
        
        $sql .= $filterResult['sql'];
        $params = $filterResult['params'];

        $result = DB::select($sql, $params);
        return (int)(!empty($result) ? $result[0]->total : 0);
    }

    public function fetch(FilterDTO $filters = null): array
    {
        $sql = $this->baseSelect();
        $filterResult = $this->builderFilter($filters);
        
        $sql .= $filterResult['sql'];
        $orderBy = $this->getOrderBy();
        if ($orderBy !== "") {
            $sql .= " " . $orderBy;
        }
        
        $params = $filterResult['params'];
        
        // Adiciona paginação se necessário
        if ($filters !== null && $filters->hasPagination()) {
            $offset = $filters->getOffset();
            $sql .= " LIMIT :limit OFFSET :offset";
            $params[':limit'] = $filters->limit;
            $params[':offset'] = $offset;
        }

        $results = DB::select($sql, $params);
        
        // Converter objetos stdClass para arrays
        $results = array_map(function ($row) {
            return (array) $row;
        }, $results);
        
        $data = array_map(function ($row) {
            $dto = $this->mapToDto($row);
            // Se for array, retorna diretamente; se for objeto com toArray, chama toArray
            if (is_array($dto)) {
                return $dto;
            }
            return method_exists($dto, 'toArray') ? $dto->toArray() : $dto;
        }, $results);

        // Se há paginação, retorna com metadata
        if ($filters !== null && $filters->hasPagination()) {
            $total = $this->count($filters);
            $totalPages = (int)ceil($total / $filters->limit);
            
            return [
                'data' => $data,
                'pagination' => [
                    'page' => $filters->page,
                    'limit' => $filters->limit,
                    'total' => $total,
                    'totalPages' => $totalPages,
                ]
            ];
        }

        return $data;
    }
}
