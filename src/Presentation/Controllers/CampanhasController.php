<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\CampanhasUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CampanhasController
{
    private $campanhasUseCase;

    public function __construct(CampanhasUseCase $campanhasUseCase)
    {
        $this->campanhasUseCase = $campanhasUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $result = $this->campanhasUseCase->getAllCampanhas();
        
        $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));
        return $response;
    }
}

