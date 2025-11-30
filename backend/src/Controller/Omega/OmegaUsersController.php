<?php

namespace App\Controller\Omega;

use App\Application\UseCase\Omega\OmegaUsersUseCase;
use App\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OmegaUsersController extends ControllerBase
{
    private $omegaUsersUseCase;

    public function __construct(OmegaUsersUseCase $omegaUsersUseCase)
    {
        $this->omegaUsersUseCase = $omegaUsersUseCase;
    }

    /** @Route("/api/omega/users", name="api_omega_users", methods={"GET"}) */
    public function handle(Request $request): JsonResponse
    {
        $result = $this->omegaUsersUseCase->getAllUsers();
        
        return $this->success($result);
    }
}

