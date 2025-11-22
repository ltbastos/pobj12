<?php

namespace App\Application\UseCase;

use App\Infrastructure\Persistence\RealizadoRepository;
use App\Infrastructure\Persistence\MetaRepository;

class ResumoService
{
    private $realizadoRepository;
    private $metaRepository;

    public function __construct(
        RealizadoRepository $realizadoRepository,
        MetaRepository $metaRepository
    ) {
        $this->realizadoRepository = $realizadoRepository;
        $this->metaRepository = $metaRepository;
    }

    public function getResumo(array $params): array
    {
        $dateFrom = trim($params['data_ini'] ?? '');
        $dateTo = trim($params['data_fim'] ?? '');

        if ($dateFrom === '' || $dateTo === '') {
            throw new \InvalidArgumentException('data_ini/data_fim obrigatórios');
        }

        list($filters, $bindValues) = $this->buildFilters($params);
        $indicadorId = trim($params['id_indicador'] ?? '') ?: null;

        $realizadoTotal = $this->realizadoRepository->sumByPeriodAndFilters(
            $dateFrom,
            $dateTo,
            $filters,
            $bindValues,
            $indicadorId
        );

        $metaTotal = $this->metaRepository->sumByPeriodAndFilters(
            $dateFrom,
            $dateTo,
            $filters,
            $bindValues,
            $indicadorId
        );

        return [
            'realizado_total' => $realizadoTotal,
            'meta_total' => $metaTotal,
        ];
    }

    private function buildFilters(array $params): array
    {
        $filters = [];
        $bindValues = [];
        
        $seg = trim($params['segmento_id'] ?? '');
        $dir = trim($params['diretoria_id'] ?? '');
        $reg = trim($params['regional_id'] ?? '');
        $age = trim($params['agencia_id'] ?? '');
        $gg = trim($params['gg_funcional'] ?? '');
        $ger = trim($params['gerente_funcional'] ?? '');

        if ($seg !== '') {
            $filters[] = 'e.id_segmento = :segmento_id';
            $bindValues[':segmento_id'] = $seg;
        }
        if ($dir !== '') {
            $filters[] = 'e.id_diretoria = :diretoria_id';
            $bindValues[':diretoria_id'] = $dir;
        }
        if ($reg !== '') {
            $filters[] = 'e.id_regional = :regional_id';
            $bindValues[':regional_id'] = $reg;
        }
        if ($age !== '') {
            $filters[] = 'e.id_agencia = :agencia_id';
            $bindValues[':agencia_id'] = $age;
        }
        if ($gg !== '') {
            $filters[] = 'e.funcional = :gg_funcional';
            $bindValues[':gg_funcional'] = $gg;
        }
        if ($ger !== '') {
            $filters[] = 'e.funcional = :gerente_funcional';
            $bindValues[':gerente_funcional'] = $ger;
        }

        return [$filters, $bindValues];
    }
}

