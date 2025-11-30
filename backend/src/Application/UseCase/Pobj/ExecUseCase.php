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
        $heatmap = $this->execRepository->findHeatmap($filters);

        return [
            'kpis' => $kpis,
            'ranking' => $ranking,
            'status' => $status,
            'chart' => $chartData,
            'heatmap' => $heatmap
        ];
    }
}


