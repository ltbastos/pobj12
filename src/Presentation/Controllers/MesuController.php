<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\OmegaMesuUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller para operações relacionadas a estrutura Mesu
 */
class MesuController extends ControllerBase
{
    private $omegaMesuUseCase;

    public function __construct(OmegaMesuUseCase $omegaMesuUseCase)
    {
        $this->omegaMesuUseCase = $omegaMesuUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $filters = $request->getAttribute('filters');
        $result = $this->omegaMesuUseCase->handle($filters);
        
        return $this->success($response, $result);
    }
}
