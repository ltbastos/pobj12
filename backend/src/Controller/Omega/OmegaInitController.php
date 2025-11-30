<?php

namespace App\Controller\Omega;

use App\Application\UseCase\Omega\OmegaInitUseCase;
use App\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OmegaInitController extends ControllerBase
{
    private $initUseCase;

    public function __construct(OmegaInitUseCase $initUseCase)
    {
        $this->initUseCase = $initUseCase;
    }

    /** @Route("/api/omega/init", name="api_omega_init", methods={"GET"}) */
    public function handle(Request $request): JsonResponse
    {
        $result = $this->initUseCase->handle();
        
        return $this->success($result);
    }
}

