<?php

namespace App\Presentation\Controllers\Omega;

use App\Application\UseCase\OmegaUsersUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Presentation\Controllers\ControllerBase;

/**
 * Controller para operações relacionadas a usuários Omega
 */
class OmegaUsersController extends ControllerBase
{
    private $omegaUsersUseCase;

    public function __construct(OmegaUsersUseCase $omegaUsersUseCase)
    {
        $this->omegaUsersUseCase = $omegaUsersUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $result = $this->omegaUsersUseCase->getAllUsers();
        
        return $this->success($response, $result);
    }
}
