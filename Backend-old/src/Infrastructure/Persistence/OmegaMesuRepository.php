<?php

namespace App\Infrastructure\Persistence;

use App\Domain\DTO\FilterDTO;
use App\Domain\DTO\OmegaMesuDTO;
use App\Domain\Enum\Cargo;

class OmegaMesuRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(OmegaMesuDTO::class);
    }

    public function baseSelect(): string
    {
        $cargoGerenteGestao = (int) Cargo::GERENTE_GESTAO;
        $cargoGerente = (int) Cargo::GERENTE;

        return "SELECT 
                    seg.nome AS segmento,
                    seg.id AS segmento_id,
                    dir.nome AS diretoria,
                    dir.id AS diretoria_id,
                    reg.nome AS gerencia_regional,
                    reg.id AS gerencia_regional_id,
                    age.nome AS agencia,
                    age.id AS agencia_id,
                    gg.nome AS gerente_gestao,
                    gg.funcional AS gerente_gestao_id,
                    g.nome AS gerente,
                    g.funcional AS gerente_id
                FROM agencias age
                LEFT JOIN regionais reg ON reg.id = age.regional_id
                LEFT JOIN diretorias dir ON dir.id = reg.diretoria_id
                LEFT JOIN segmentos seg ON seg.id = dir.segmento_id
                LEFT JOIN d_estrutura gg ON gg.agencia_id = age.id AND gg.cargo_id = {$cargoGerenteGestao}
                LEFT JOIN d_estrutura g ON g.agencia_id = age.id AND g.cargo_id = {$cargoGerente}
                WHERE 1=1";
    }

    public function builderFilter(FilterDTO $filters = null): array
    {
        return ['sql' => '', 'params' => []];
    }

    protected function getOrderBy(): string
    {
        return "ORDER BY seg.nome ASC, dir.nome ASC, reg.nome ASC, age.nome ASC";
    }

    public function mapToDto(array $row): OmegaMesuDTO
    {
        return new OmegaMesuDTO(
            $row['segmento'] ?? null,
            $row['segmento_id'] ?? null,
            $row['diretoria'] ?? null,
            $row['diretoria_id'] ?? null,
            $row['gerencia_regional'] ?? null,
            $row['gerencia_regional_id'] ?? null,
            $row['agencia'] ?? null,
            $row['agencia_id'] ?? null,
            $row['gerente_gestao'] ?? null,
            $row['gerente_gestao_id'] ?? null,
            $row['gerente'] ?? null,
            $row['gerente_id'] ?? null
        );
    }
}

