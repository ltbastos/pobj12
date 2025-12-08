<?php

namespace App\Application\UseCase\Pobj;

use App\Domain\DTO\FilterDTO;
use App\Repository\Pobj\ExecRepository;

class ExecUseCase
{
    private $execRepository;

    public function __construct(ExecRepository $execRepository)
    {
        $this->execRepository = $execRepository;
    }

    public function handle(?FilterDTO $filters = null): array
    {
        $kpis = $this->execRepository->findKPIs($filters);
        $ranking = $this->execRepository->findRankingByRegional($filters);
        $status = $this->execRepository->findStatusByRegional($filters);
        $chartData = $this->execRepository->findChartData($filters);
        $heatmapSections = $this->execRepository->findHeatmapSections($filters);
        $heatmapMeta = $this->execRepository->findHeatmapMeta($filters);

        // Mesclar dados do heatmap para o formato esperado pelo frontend
        // Mesclar dataFamiliaMensal somando valores quando houver chaves duplicadas
        $dataFamiliaMensal = $heatmapSections['dataFamiliaMensal'] ?? [];
        foreach ($heatmapMeta['dataFamiliaMensal'] ?? [] as $key => $data) {
            if (isset($dataFamiliaMensal[$key])) {
                $dataFamiliaMensal[$key]['real'] += $data['real'] ?? 0;
                $dataFamiliaMensal[$key]['meta'] += $data['meta'] ?? 0;
            } else {
                $dataFamiliaMensal[$key] = $data;
            }
        }

        $heatmap = [
            'units' => array_merge(
                $heatmapSections['units'] ?? [],
                $heatmapMeta['units'] ?? []
            ),
            'sectionsFamilia' => $heatmapSections['sectionsFamilia'] ?? [],
            'sectionsIndicador' => $heatmapSections['sectionsIndicador'] ?? [],
            'dataFamilia' => $heatmapSections['dataFamilia'] ?? [],
            'dataIndicador' => $heatmapSections['dataIndicador'] ?? [],
            'dataFamiliaMensal' => $dataFamiliaMensal,
            'dataIndicadorMensal' => $heatmapSections['dataIndicadorMensal'] ?? [],
            'months' => $heatmapSections['months'] ?? $heatmapMeta['months'] ?? []
        ];

        // Remover unidades duplicadas baseado no value
        $uniqueUnits = [];
        foreach ($heatmap['units'] as $unit) {
            $uniqueUnits[$unit['value']] = $unit;
        }
        $heatmap['units'] = array_values($uniqueUnits);

        return [
            'kpis' => $kpis,
            'ranking' => $ranking,
            'status' => $status,
            'chart' => $chartData,
            'heatmap' => $heatmap
        ];
    }
}


