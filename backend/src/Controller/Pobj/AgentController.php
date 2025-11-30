<?php

namespace App\Controller\Pobj;

use App\Application\UseCase\Pobj\AgentUseCase;
use App\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AgentController extends ControllerBase
{
    private $agentUseCase;

    public function __construct(AgentUseCase $agentUseCase)
    {
        $this->agentUseCase = $agentUseCase;
    }

    /** @Route("/api/agent", name="api_agent", methods={"POST"}) */
    public function handle(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        
        if (!is_array($payload)) {
            return $this->error('Payload inválido. Esperado JSON.', 400);
        }

        try {
            $result = $this->agentUseCase->processQuestion($payload ?: []);
            
            return new JsonResponse($result, 200, [
                'Content-Type' => 'application/json; charset=utf-8'
            ], JSON_UNESCAPED_UNICODE);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        } catch (\RuntimeException $e) {
            return $this->error($e->getMessage(), 500);
        } catch (\Throwable $err) {
            $message = trim($err->getMessage()) ?: 'Falha interna ao processar a pergunta.';
            return $this->error($message, 500);
        }
    }
}

