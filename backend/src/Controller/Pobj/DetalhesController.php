<?php

namespace App\Controller\Pobj;

use App\Application\UseCase\Pobj\DetalhesUseCase;
use App\Controller\ControllerBase;
use App\Domain\DTO\FilterDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DetalhesController extends ControllerBase
{
    private $detalhesUseCase;

    public function __construct(DetalhesUseCase $detalhesUseCase)
    {
        $this->detalhesUseCase = $detalhesUseCase;
    }

    /** @Route("/api/pobj/detalhes", name="api_pobj_detalhes", methods={"GET"}) */
    public function handle(Request $request): JsonResponse
    {
        $filters = new FilterDTO($request->query->all());
        $result = $this->detalhesUseCase->handle($filters);
        
        return $this->success($result);
    }
}

