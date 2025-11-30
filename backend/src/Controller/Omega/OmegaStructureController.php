<?php

namespace App\Controller\Omega;

use App\Application\UseCase\Omega\OmegaStructureUseCase;
use App\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OmegaStructureController extends ControllerBase
{
    private $omegaStructureUseCase;

    public function __construct(OmegaStructureUseCase $omegaStructureUseCase)
    {
        $this->omegaStructureUseCase = $omegaStructureUseCase;
    }

    /** @Route("/api/omega/structure", name="api_omega_structure", methods={"GET"}) */
    public function handle(Request $request): JsonResponse
    {
        $result = $this->omegaStructureUseCase->getStructure();
        
        return $this->success($result);
    }
}

