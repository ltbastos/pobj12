<?php

namespace App\Presentation\Controllers\Omega;

use App\Application\UseCase\OmegaTicketsUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Presentation\Controllers\ControllerBase;

/**
 * Controller para operações relacionadas a tickets Omega
 */
class OmegaTicketsController extends ControllerBase
{
    private $omegaTicketsUseCase;

    public function __construct(OmegaTicketsUseCase $omegaTicketsUseCase)
    {
        $this->omegaTicketsUseCase = $omegaTicketsUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $result = $this->omegaTicketsUseCase->getAllTickets();
        
        return $this->success($response, $result);
    }
}
