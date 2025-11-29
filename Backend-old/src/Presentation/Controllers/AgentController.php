<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\AgentUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class AgentController
{
    private $agentUseCase;

    public function __construct(AgentUseCase $agentUseCase)
    {
        $this->agentUseCase = $agentUseCase;
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
            $result = $this->agentUseCase->processQuestion($payload ?: []);
            
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

