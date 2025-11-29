<?php

namespace App\Presentation\Controllers\Omega;

use App\Application\UseCase\OmegaStatusUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Presentation\Controllers\ControllerBase;

/**
 * Controller para operações relacionadas a status Omega
 */
class OmegaStatusController extends ControllerBase
{
    private $omegaStatusUseCase;

    public function __construct(OmegaStatusUseCase $omegaStatusUseCase)
    {
        $this->omegaStatusUseCase = $omegaStatusUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $result = $this->omegaStatusUseCase->getAllStatus();
        
        return $this->success($response, $result);
    }
}
