<?php

namespace App\Controller\Omega;

use App\Application\UseCase\Omega\OmegaTicketsUseCase;
use App\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OmegaTicketsController extends ControllerBase
{
    private $omegaTicketsUseCase;

    public function __construct(OmegaTicketsUseCase $omegaTicketsUseCase)
    {
        $this->omegaTicketsUseCase = $omegaTicketsUseCase;
    }

    /** @Route("/api/omega/tickets", name="api_omega_tickets", methods={"GET"}) */
    public function handle(Request $request): JsonResponse
    {
        $result = $this->omegaTicketsUseCase->getAllTickets();
        
        return $this->success($result);
    }
}

