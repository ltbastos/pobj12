<?php

namespace App\Controller\Pobj;

use App\Application\UseCase\Pobj\RankingUseCase;
use App\Controller\ControllerBase;
use App\Domain\DTO\FilterDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RankingController extends ControllerBase
{
    private $rankingUseCase;

    public function __construct(RankingUseCase $rankingUseCase)
    {
        $this->rankingUseCase = $rankingUseCase;
    }

    /** @Route("/api/pobj/ranking", name="api_pobj_ranking", methods={"GET"}) */
    public function handle(Request $request): JsonResponse
    {
        $filters = new FilterDTO($request->query->all());
        $result = $this->rankingUseCase->handle($filters);
        
        return $this->success($result);
    }
}

