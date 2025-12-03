<?php

namespace App\Controller\Pobj;

use App\Application\UseCase\Pobj\AgentUseCase;
use App\Controller\ControllerBase;
use App\Exception\BadRequestException;
use App\Exception\ValidationException;
use OpenApi\Annotations as OA;
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

    /**
     * Processa perguntas do agente/AI
     * 
     * @Route("/api/agent", name="api_agent", methods={"POST"})
     * 
     * @OA\Post(
     *     path="/api/agent",
     *     summary="Processar pergunta do agente",
     *     description="Processa perguntas e retorna respostas do agente de IA",
     *     tags={"POBJ", "Agent"},
     *     security={{"ApiKeyAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados da pergunta",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"question"},
     *             @OA\Property(property="question", type="string", example="Qual o resumo do mês atual?"),
     *             @OA\Property(property="context", type="string", description="Contexto adicional")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pergunta processada com sucesso",
     *         @OA\Schema(
     *             
     *             @OA\Property(property="success",  example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Requisição inválida",
     *         @OA\Schema(
     *             
     *             @OA\Property(property="success",  example=false),
     *             @OA\Property(property="data", 
     *                 @OA\Property(property="error",  example="Payload inválido"),
     *                 @OA\Property(property="code",  example="BAD_REQUEST")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Erro de validação",
     *         @OA\Schema(
     *             
     *             @OA\Property(property="success",  example=false),
     *             @OA\Property(property="data", 
     *                 @OA\Property(property="error",  example="Dados de entrada inválidos"),
     *                 @OA\Property(property="code",  example="VALIDATION_ERROR"),
     *                 @OA\Property(property="details", 
     *                     @OA\Property(property="validation_errors", type="object")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autorizado"),
     *     @OA\Response(response=429, description="Rate limit excedido")
     * )
     */
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




