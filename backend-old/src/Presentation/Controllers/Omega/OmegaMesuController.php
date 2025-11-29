<?php

namespace App\Presentation\Controllers\Omega;

use App\Application\UseCase\OmegaMesuUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Presentation\Controllers\ControllerBase;

/**
 * Controller para operações relacionadas a estrutura Omega Mesu
 */
class OmegaMesuController extends ControllerBase
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

