<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\HistoricoUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HistoricoController
{
    private $historicoUseCase;

    public function __construct(HistoricoUseCase $historicoUseCase)
    {
        $this->historicoUseCase = $historicoUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $result = $this->historicoUseCase->getAllHistorico();
        
        $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));
        return $response;
    }
}

