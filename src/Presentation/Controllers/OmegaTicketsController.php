<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\OmegaTicketsUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class OmegaTicketsController
{
    private $omegaTicketsUseCase;

    public function __construct(OmegaTicketsUseCase $omegaTicketsUseCase)
    {
        $this->omegaTicketsUseCase = $omegaTicketsUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        try {
            $result = $this->omegaTicketsUseCase->getAllTickets();
            
            $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));
            return $response;
        } catch (\Throwable $e) {
            $response = $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode([
                'error' => 'Erro ao carregar chamados Omega: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));
            return $response;
        }
    }
}

