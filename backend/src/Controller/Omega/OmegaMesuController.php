<?php

namespace App\Controller\Omega;

use App\Application\UseCase\Omega\OmegaMesuUseCase;
use App\Controller\ControllerBase;
use App\Domain\DTO\FilterDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OmegaMesuController extends ControllerBase
{
    private $omegaMesuUseCase;

    public function __construct(OmegaMesuUseCase $omegaMesuUseCase)
    {
        $this->omegaMesuUseCase = $omegaMesuUseCase;
    }

    /** @Route("/api/omega/mesu", name="api_omega_mesu", methods={"GET"}) */
    public function handle(Request $request): JsonResponse
    {
        $filters = new FilterDTO($request->query->all());
        $result = $this->omegaMesuUseCase->handle($filters);
            
        return $this->success($result);
    }
}

