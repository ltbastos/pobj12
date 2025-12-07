<?php

namespace App\Repository\Pobj;

use App\Domain\DTO\FilterDTO;
use App\Entity\Pobj\DProduto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository facade que orquestra os repositórios especializados
 * para buscar dados executivos de forma modular
 */
class ExecRepository extends ServiceEntityRepository
{
    private $kpisRepository;
    private $rankingRepository;
    private $chartRepository;
    private $heatmapSectionsRepository;
    private $heatmapMetaRepository;

    public function __construct(
        ManagerRegistry $registry,
        ExecKPIsRepository $kpisRepository,
        ExecRankingRepository $rankingRepository,
        ExecChartRepository $chartRepository,
        ExecHeatmapSectionsRepository $heatmapSectionsRepository,
        ExecHeatmapMetaRepository $heatmapMetaRepository
    ) {
        parent::__construct($registry, DProduto::class);
        $this->kpisRepository = $kpisRepository;
        $this->rankingRepository = $rankingRepository;
        $this->chartRepository = $chartRepository;
        $this->heatmapSectionsRepository = $heatmapSectionsRepository;
        $this->heatmapMetaRepository = $heatmapMetaRepository;
    }

    public function findKPIs(?FilterDTO $filters = null): array
    {
        return $this->kpisRepository->findKPIs($filters);
    }

    public function findRankingByRegional(?FilterDTO $filters = null): array
    {
        return $this->rankingRepository->findRankingByRegional($filters);
    }

    public function findStatusByRegional(?FilterDTO $filters = null): array
    {
        return $this->rankingRepository->findStatusByRegional($filters);
    }

    public function findChartData(?FilterDTO $filters = null): array
    {
        return $this->chartRepository->findChartData($filters);
    }

    public function findHeatmapSections(?FilterDTO $filters = null): array
    {
        return $this->heatmapSectionsRepository->findHeatmapSections($filters);
    }

    public function findHeatmapMeta(?FilterDTO $filters = null): array
    {
        return $this->heatmapMetaRepository->findHeatmapMeta($filters);
    }
}
