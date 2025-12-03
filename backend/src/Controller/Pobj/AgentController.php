<?php

namespace App\Controller\Pobj;

use App\Application\UseCase\Pobj\AgentUseCase;
use App\Controller\ControllerBase;
use App\Exception\BadRequestException;
use App\Exception\ValidationException;
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
        // Validação de payload
        $payload = json_decode($request->getContent(), true);
        
        if (!is_array($payload)) {
            throw new BadRequestException('Payload inválido. Esperado JSON.');
        }

        // Validação de campos obrigatórios (exemplo)
        $validationErrors = [];
        if (empty($payload['question'] ?? null)) {
            $validationErrors['question'] = 'Campo obrigatório';
        }

        if (!empty($validationErrors)) {
            throw new ValidationException('Dados de entrada inválidos', $validationErrors);
        }

        // O ExceptionSubscriber vai capturar qualquer exceção lançada pelo UseCase
        $result = $this->agentUseCase->processQuestion($payload);
        
        return $this->success($result);
    }
}

