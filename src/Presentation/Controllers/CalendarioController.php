<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\CalendarioUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CalendarioController
{
    private $calendarioUseCase;

    public function __construct(CalendarioUseCase $calendarioUseCase)
    {
        $this->calendarioUseCase = $calendarioUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $result = $this->calendarioUseCase->getAllCalendario();
        
        $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));
        return $response;
    }
}

