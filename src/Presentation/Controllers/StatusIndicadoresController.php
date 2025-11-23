<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\StatusIndicadoresUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller para operações relacionadas a status de indicadores
 */
class StatusIndicadoresController extends ControllerBase
{
    private $statusIndicadoresUseCase;

    public function __construct(StatusIndicadoresUseCase $statusIndicadoresUseCase)
    {
        $this->statusIndicadoresUseCase = $statusIndicadoresUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $result = $this->statusIndicadoresUseCase->handle();
        
        return $this->success($response, $result);
    }
}

