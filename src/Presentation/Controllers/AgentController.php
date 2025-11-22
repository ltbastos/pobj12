<?php

namespace App\Presentation\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Container;

class AgentController
{
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function handle(Request $request, Response $response): Response
    {
        $payload = $request->getParsedBody();
        
        if (!is_array($payload)) {
            $response = $response->withStatus(400)
                ->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode([
                'error' => 'Payload inválido. Esperado JSON.'
            ], JSON_UNESCAPED_UNICODE));
            return $response;
        }

        try {
            $service = $this->container->get('App\Application\UseCase\AgentService');
            $result = $service->processQuestion($payload ?: []);
            
            $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));
            return $response;
        } catch (\InvalidArgumentException $e) {
            $response = $response->withStatus(422)
                ->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));
            return $response;
        } catch (\RuntimeException $e) {
            $response = $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode([
                'error' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));
            return $response;
        } catch (\Throwable $err) {
            $message = trim($err->getMessage()) ?: 'Falha interna ao processar a pergunta.';
            $response = $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode([
                'error' => $message
            ], JSON_UNESCAPED_UNICODE));
            return $response;
        }
    }
}

