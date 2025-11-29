<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\HistoricoUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HistoricoController extends ControllerBase
{
    private $historicoUseCase;

    public function __construct(HistoricoUseCase $historicoUseCase)
    {
        $this->historicoUseCase = $historicoUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $filters = $request->getAttribute('filters');
        $result = $this->historicoUseCase->handle($filters);
        
        return $this->success($response, $result);
    }
}

