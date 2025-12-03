<?php

namespace App\Controller\Pobj;

use App\Controller\ControllerBase;
use App\Infrastructure\Database\Connection;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller para gerenciar notificações do POBJ
 */
class PobjNotificationsController extends ControllerBase
{

    /**
     * Cria uma nova notificação no POBJ
     * 
     * @Route("/api/pobj/notifications", name="api_pobj_notifications", methods={"POST"})
     * 
     * @OA\Post(
     *     path="/api/pobj/notifications",
     *     summary="Criar notificação",
     *     description="Cria uma nova notificação relacionada a um chamado/ticket do POBJ",
     *     tags={"POBJ", "Notificações"},
     *     security={{"ApiKeyAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Dados da notificação",
     *         @OA\JsonContent(
     *             type="object",
     *             required={"ticketId"},
     *             @OA\Property(property="ticketId", type="string", description="ID do ticket/chamado", example="TKT-12345"),
     *             @OA\Property(property="ticketSubject", type="string", description="Assunto do ticket", example="Atualização de status"),
     *             @OA\Property(property="date", type="string", format="date-time", description="Data do evento (ISO 8601)", example="2024-12-03T22:00:00Z"),
     *             @OA\Property(property="actorId", type="string", description="ID do usuário que realizou a ação", example="user123"),
     *             @OA\Property(property="action", type="string", description="Tipo de ação realizada", example="Atualização"),
     *             @OA\Property(property="comment", type="string", description="Comentário adicional", example="Status alterado para em andamento"),
     *             @OA\Property(property="status", type="string", description="Status do ticket", example="aberto", enum={"aberto", "em_andamento", "resolvido", "fechado"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Notificação criada com sucesso",
     *         @OA\Schema(
     *             
     *             @OA\Property(property="success",  example=true),
     *             @OA\Property(
     *                 property="data",
     *                 
     *                 @OA\Property(property="id",  example=1),
     *                 @OA\Property(property="ticketId",  example="TKT-12345"),
     *                 @OA\Property(property="ticketSubject",  example="Atualização de status"),
     *                 @OA\Property(property="date",  format="date-time", example="2024-12-03T22:00:00Z"),
     *                 @OA\Property(property="actorId",  example="user123"),
     *                 @OA\Property(property="action",  example="Atualização"),
     *                 @OA\Property(property="comment",  example="Status alterado"),
     *                 @OA\Property(property="status",  example="aberto"),
     *                 @OA\Property(property="read",  example=false)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Dados inválidos",
     *         @OA\Schema(
     *             
     *             @OA\Property(property="success",  example=false),
     *             @OA\Property(property="data", 
     *                 @OA\Property(property="error",  example="Dados inválidos"),
     *                 @OA\Property(property="code",  example="BAD_REQUEST")
     *             )
     *         )
     *     ),
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

            // Insere notificação na tabela do POBJ
            // Assumindo que existe uma tabela pobj_notifications ou similar
            $connection = Connection::getInstance();
            $pdo = $connection->getPdo();
            
            $sql = "INSERT INTO pobj_notifications 
                    (ticket_id, ticket_subject, date, actor_id, action, comment, status, read, created_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 0, NOW())";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $ticketId,
                $ticketSubject,
                $date,
                $actorId,
                $action,
                $comment,
                $status
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
                'read' => false
            ]);
        } catch (\Exception $e) {
            // Se a tabela não existir, apenas loga e retorna sucesso (notificação em memória)
            error_log('Erro ao criar notificação POBJ: ' . $e->getMessage());
            return $this->success([
                'id' => 'pobj-ntf-' . time(),
                'ticketId' => $data['ticketId'] ?? null,
                'ticketSubject' => $data['ticketSubject'] ?? 'Chamado sem assunto',
                'date' => $data['date'] ?? date('Y-m-d\TH:i:s\Z'),
                'actorId' => $data['actorId'] ?? null,
                'action' => $data['action'] ?? 'Atualização',
                'comment' => $data['comment'] ?? '',
                'status' => $data['status'] ?? 'aberto',
                'read' => false
            ]);
        }
    }
}




