<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\StatusIndicadoresUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class StatusIndicadoresController
{
    private $statusIndicadoresUseCase;

    public function __construct(StatusIndicadoresUseCase $statusIndicadoresUseCase)
    {
        $this->statusIndicadoresUseCase = $statusIndicadoresUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $result = $this->statusIndicadoresUseCase->getAllStatus();
        
        $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));
        return $response;
    }
}

