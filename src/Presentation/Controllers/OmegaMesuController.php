<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\OmegaMesuUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class OmegaMesuController
{
    private $omegaMesuUseCase;

    public function __construct(OmegaMesuUseCase $omegaMesuUseCase)
    {
        $this->omegaMesuUseCase = $omegaMesuUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        try {
            $result = $this->omegaMesuUseCase->getMesuData();
            
            $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));
            return $response;
        } catch (\Throwable $e) {
            $response = $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode([
                'error' => 'Erro ao carregar dados MESU: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));
            return $response;
        }
    }
}

