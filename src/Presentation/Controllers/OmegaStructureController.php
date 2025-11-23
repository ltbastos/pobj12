<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\OmegaStructureUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller para operações relacionadas a estrutura Omega
 */
class OmegaStructureController extends ControllerBase
{
    private $omegaStructureUseCase;

    public function __construct(OmegaStructureUseCase $omegaStructureUseCase)
    {
        $this->omegaStructureUseCase = $omegaStructureUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $result = $this->omegaStructureUseCase->getStructure();
        
        return $this->success($response, $result);
    }
}
