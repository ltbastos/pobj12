<?php

namespace App\Presentation\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Container;

class OmegaTicketsController
{
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function handle(Request $request, Response $response): Response
    {
        try {
            $service = $this->container->get('App\Application\UseCase\OmegaTicketsService');
            $result = $service->getAllTickets();
            
            $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));
            return $response;
        } catch (\Throwable $e) {
            if ($this->container->has('logger')) {
                $logger = $this->container->get('logger');
                $logger->error('Erro ao carregar chamados Omega', [
                    'endpoint' => 'omega/tickets',
                    'error' => $e->getMessage(),
                ]);
            }
            
            $response = $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode([
                'error' => 'Erro ao carregar chamados Omega: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));
            return $response;
        }
    }
}

