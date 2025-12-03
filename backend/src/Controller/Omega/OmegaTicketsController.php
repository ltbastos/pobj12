<?php

namespace App\Controller\Omega;

use App\Application\UseCase\Omega\OmegaTicketsUseCase;
use App\Controller\ControllerBase;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class OmegaTicketsController extends ControllerBase
{
    private $omegaTicketsUseCase;

    public function __construct(OmegaTicketsUseCase $omegaTicketsUseCase)
    {
        $this->omegaTicketsUseCase = $omegaTicketsUseCase;
    }
    
    /**
     * Lista todos os tickets ou cria um novo ticket
     *
     * @Route("/api/omega/tickets", name="api_omega_tickets", methods={"GET", "POST"})
     *
     * @OA\Get(
     *     path="/api/omega/tickets",
     *     summary="Lista de tickets",
     *     description="Retorna todos os tickets do sistema Omega",
     *     tags={"Omega", "Tickets"},
     *     security={{"ApiKeyAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Lista de tickets retornada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autorizado"),
     *     @OA\Response(response=429, description="Rate limit excedido")
     * )
     *
     * @OA\Post(
     *     path="/api/omega/tickets",
     *     summary="Criar ticket",
     *     description="Cria um novo ticket no sistema Omega",
     *     tags={"Omega", "Tickets"},
     *     security={{"ApiKeyAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             required={"subject"},
     *             @OA\Property(property="subject", type="string", example="Novo chamado"),
     *             @OA\Property(property="queue", type="string", example="POBJ"),
     *             @OA\Property(property="status", type="string", example="aberto"),
     *             @OA\Property(property="priority", type="string", example="normal"),
     *             @OA\Property(property="owner", type="string", example="user123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Ticket criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Dados inválidos"),
     *     @OA\Response(response=401, description="Não autorizado"),
     *     @OA\Response(response=429, description="Rate limit excedido")
     * )
     */

    public function handle(Request $request): JsonResponse
    {
        if ($request->getMethod() === 'POST') {
            return $this->create($request);
        }
        
        $tickets = $this->omegaTicketsUseCase->getAllTickets();
        
        // Converte entidades para arrays para evitar problemas de serialização
        // e garantir que o eager loading funcione corretamente
        $ticketsArray = array_map(function($ticket) {
            return $this->omegaTicketsUseCase->ticketToArray($ticket);
        }, $tickets);
        
        return $this->success($ticketsArray);
    }

    /**
     * Atualiza um ticket existente
     * 
     * @Route("/api/omega/tickets/{id}", name="api_omega_tickets_update", methods={"POST", "PUT"})
     * 
     * @OA\Put(
     *     path="/api/omega/tickets/{id}",
     *     summary="Atualizar ticket",
     *     description="Atualiza um ticket existente no sistema Omega",
     *     tags={"Omega", "Tickets"},
     *     security={{"ApiKeyAuth": {}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do ticket",
     *         @OA\Schema(type="string", example="TKT-12345")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados para atualização",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="subject", type="string", example="Assunto atualizado"),
     *             @OA\Property(property="status", type="string", example="em_andamento"),
     *             @OA\Property(property="priority", type="string", example="alta"),
     *             @OA\Property(property="owner", type="string", example="user456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ticket atualizado com sucesso",
     *         @OA\Schema(
     *             
     *             @OA\Property(property="success",  example=true),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Dados inválidos"),
     *     @OA\Response(response=404, description="Ticket não encontrado"),
     *     @OA\Response(response=401, description="Não autorizado"),
     *     @OA\Response(response=429, description="Rate limit excedido")
     * )
     */
    public function update(string $id, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->error('Dados inválidos', 400);
        }
        
        try {
            $result = $this->omegaTicketsUseCase->updateTicket($id, $data);
            if (!$result) {
                return $this->error('Ticket não encontrado', 404);
            }
            // Converte a entidade para array para serialização JSON
            $ticketArray = $this->omegaTicketsUseCase->ticketToArray($result);
            return $this->success($ticketArray);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
    

    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->error('Dados inválidos', 400);
        }
        
        try {
            $result = $this->omegaTicketsUseCase->createTicket($data);
            // Converte a entidade para array para serialização JSON
            $ticketArray = $this->omegaTicketsUseCase->ticketToArray($result);
            return $this->success($ticketArray);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }
}





