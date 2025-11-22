<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\ResumoUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ResumoController
{
    private $resumoUseCase;

    public function __construct(ResumoUseCase $resumoUseCase)
    {
        $this->resumoUseCase = $resumoUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        try {
            $params = array_merge(
                $request->getQueryParams(),
                $request->getParsedBody() ?: []
            );
            
            $result = $this->resumoUseCase->getResumo($params);
            
            $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));
            return $response;
        } catch (\InvalidArgumentException $e) {
            $response = $response->withStatus(400)
                ->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));
            return $response;
        }
    }
}

