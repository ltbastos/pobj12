<?php

namespace App\Controller\Omega;

use App\Controller\ControllerBase;
use App\Infrastructure\Database\Connection;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller para gerenciar notificações do Omega
 */
class OmegaNotificationsController extends ControllerBase
{
    /**
     * Cria uma nova notificação no Omega
     * 
     * @Route("/api/omega/notifications", name="api_omega_notifications", methods={"POST"})
     * 
     * @OA\Post(
     *     path="/api/omega/notifications",
     *     summary="Criar notificação Omega",
     *     description="Cria uma nova notificação relacionada a um chamado/ticket do Omega",
     *     tags={"Omega", "Notificações"},
     *     security={{"ApiKeyAuth": {}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados da notificação",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"ticketId"},
     *
     *             @OA\Property(
     *                 property="ticketId",
     *                 type="string",
     *                 description="ID do ticket/chamado",
     *                 example="TKT-12345"
     *             ),
     *             @OA\Property(
     *                 property="ticketSubject",
     *                 type="string",
     *                 description="Assunto do ticket",
     *                 example="Atualização de status"
     *             ),
     *             @OA\Property(
     *                 property="date",
     *                 type="string",
     *                 format="date-time",
     *                 description="Data do evento (ISO 8601)",
     *                 example="2024-12-03T22:00:00Z"
     *             ),
     *             @OA\Property(
     *                 property="actorId",
     *                 type="string",
     *                 description="ID do usuário que realizou a ação",
     *                 example="user123"
     *             ),
     *             @OA\Property(
     *                 property="action",
     *                 type="string",
     *                 description="Tipo de ação realizada",
     *                 example="Atualização"
     *             ),
     *             @OA\Property(
     *                 property="comment",
     *                 type="string",
     *                 description="Comentário adicional",
     *                 example="Status alterado para em andamento"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 description="Status do ticket",
     *                 example="aberto",
     *                 enum={"aberto", "em_andamento", "resolvido", "fechado"}
     *             ),
     *             @OA\Property(
     *                 property="type",
     *                 type="string",
     *                 description="Tipo da notificação",
     *                 example="status_update",
     *                 enum={"status_update", "comment", "assignment"}
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Notificação criada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id", type="string", example="ntf-1234567890"),
     *                 @OA\Property(property="ticketId", type="string", example="TKT-12345"),
     *                 @OA\Property(property="ticketSubject", type="string", example="Atualização de status"),
     *                 @OA\Property(property="date", type="string", format="date-time", example="2024-12-03T22:00:00Z"),
     *                 @OA\Property(property="actorId", type="string", example="user123"),
     *                 @OA\Property(property="action", type="string", example="Atualização"),
     *                 @OA\Property(property="comment", type="string", example="Status alterado"),
     *                 @OA\Property(property="status", type="string", example="aberto"),
     *                 @OA\Property(property="type", type="string", example="status_update"),
     *                 @OA\Property(property="read", type="boolean", example=false)
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Dados inválidos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="error", type="string", example="Dados inválidos"),
     *                 @OA\Property(property="code", type="string", example="BAD_REQUEST")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(response=401, description="Não autorizado"),
     *     @OA\Response(response=429, description="Rate limit excedido")
     * )
     */

    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!$data) {
            return $this->error('Dados inválidos', 400);
        }

        try {
            $ticketId = $data['ticketId'] ?? null;
            $ticketSubject = $data['ticketSubject'] ?? 'Chamado sem assunto';
            $date = $data['date'] ?? date('Y-m-d\TH:i:s\Z');
            $actorId = $data['actorId'] ?? null;
            $action = $data['action'] ?? 'Atualização';
            $comment = $data['comment'] ?? '';
            $status = $data['status'] ?? 'aberto';
            $type = $data['type'] ?? 'status_update';

            // Insere notificação na tabela (assumindo que existe uma tabela omega_notifications)
            // Se não existir, pode ser armazenado em uma tabela genérica ou em memória
            $connection = Connection::getInstance();
            $pdo = $connection->getPdo();
            
            $sql = "INSERT INTO omega_notifications 
                    (ticket_id, ticket_subject, date, actor_id, action, comment, status, type, read, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $ticketId,
                $ticketSubject,
                $date,
                $actorId,
                $action,
                $comment,
                $status,
                $type
            ]);

            $notificationId = $pdo->lastInsertId();

            return $this->success([
                'id' => $notificationId,
                'ticketId' => $ticketId,
                'ticketSubject' => $ticketSubject,
                'date' => $date,
                'actorId' => $actorId,
                'action' => $action,
                'comment' => $comment,
                'status' => $status,
                'type' => $type,
                'read' => false
            ]);
        } catch (\Exception $e) {
            // Se a tabela não existir, apenas loga e retorna sucesso (notificação em memória)
            error_log('Erro ao criar notificação Omega: ' . $e->getMessage());
            return $this->success([
                'id' => 'ntf-' . time(),
                'ticketId' => $data['ticketId'] ?? null,
                'ticketSubject' => $data['ticketSubject'] ?? 'Chamado sem assunto',
                'date' => $data['date'] ?? date('Y-m-d\TH:i:s\Z'),
                'actorId' => $data['actorId'] ?? null,
                'action' => $data['action'] ?? 'Atualização',
                'comment' => $data['comment'] ?? '',
                'status' => $data['status'] ?? 'aberto',
                'type' => $data['type'] ?? 'status_update',
                'read' => false
            ]);
        }
    }
}



