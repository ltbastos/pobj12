<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\EstruturaUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class EstruturaController
{
    private $estruturaUseCase;

    public function __construct(EstruturaUseCase $estruturaUseCase)
    {
        $this->estruturaUseCase = $estruturaUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $result = $this->estruturaUseCase->getAllEstrutura();
        
        $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));
        return $response;
    }
}

