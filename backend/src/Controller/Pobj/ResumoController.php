<?php

namespace App\Controller\Pobj;

use App\Application\UseCase\Pobj\ResumoUseCase;
use App\Controller\ControllerBase;
use App\Domain\DTO\FilterDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ResumoController extends ControllerBase
{
    private $resumoUseCase;

    public function __construct(ResumoUseCase $resumoUseCase)
    {
        $this->resumoUseCase = $resumoUseCase;
    }

    /** @Route("/api/pobj/resumo", name="api_pobj_resumo", methods={"GET"}) */
    public function handle(Request $request): JsonResponse
    {
        $filters = new FilterDTO($request->query->all());
        $result = $this->resumoUseCase->handle($filters);

        return $this->success($result);
    }
}

