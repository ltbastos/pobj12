<?php

namespace App\Presentation\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Container;

class OmegaMesuController
{
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function handle(Request $request, Response $response): Response
    {
        try {
            $service = $this->container->get('App\Application\UseCase\OmegaMesuService');
            $result = $service->getMesuData();
            
            $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));
            return $response;
        } catch (\Throwable $e) {
            if ($this->container->has('logger')) {
                $logger = $this->container->get('logger');
                $logger->error('Erro ao carregar dados MESU', [
                    'endpoint' => 'omega/mesu',
                    'error' => $e->getMessage(),
                ]);
            }
            
            $response = $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode([
                'error' => 'Erro ao carregar dados MESU: ' . $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));
            return $response;
        }
    }
}

