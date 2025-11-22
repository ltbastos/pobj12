<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\VariavelUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class VariavelController
{
    private $variavelUseCase;

    public function __construct(VariavelUseCase $variavelUseCase)
    {
        $this->variavelUseCase = $variavelUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $result = $this->variavelUseCase->getAllVariaveis();
        
        $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));
        return $response;
    }
}

