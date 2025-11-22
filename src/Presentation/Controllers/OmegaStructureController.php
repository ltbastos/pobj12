<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\OmegaStructureUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class OmegaStructureController
{
    private $omegaStructureUseCase;

    public function __construct(OmegaStructureUseCase $omegaStructureUseCase)
    {
        $this->omegaStructureUseCase = $omegaStructureUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        try {
            $result = $this->omegaStructureUseCase->getStructure();
            
            $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));
            return $response;
        } catch (\Throwable $e) {
            $response = $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode([
                'error' => 'Erro ao carregar estrutura Omega: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));
            return $response;
        }
    }
}

