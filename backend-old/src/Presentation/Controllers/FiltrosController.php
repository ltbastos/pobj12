<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\FiltrosUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FiltrosController
{
    private $filtrosUseCase;

    public function __construct(FiltrosUseCase $filtrosUseCase)
    {
        $this->filtrosUseCase = $filtrosUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $params = $request->getQueryParams();
        $nivelStr = isset($params['nivel']) ? $params['nivel'] : '';
        
        if (empty($nivelStr)) {
            $response = $response->withStatus(400)
                ->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode([
                'error' => 'Parâmetro "nivel" é obrigatório'
            ], JSON_UNESCAPED_UNICODE));
            return $response;
        }

        try {
            $result = $this->filtrosUseCase->getFiltroByNivel($nivelStr);
            
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

