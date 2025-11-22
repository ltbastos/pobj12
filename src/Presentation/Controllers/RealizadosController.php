<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\RealizadoUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RealizadosController
{
    private $realizadoUseCase;

    public function __construct(RealizadoUseCase $realizadoUseCase)
    {
        $this->realizadoUseCase = $realizadoUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $result = $this->realizadoUseCase->getAllRealizados();
        
        $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));
        return $response;
    }
}

