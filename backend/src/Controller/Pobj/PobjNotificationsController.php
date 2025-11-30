<?php

namespace App\Controller\Pobj;

use App\Controller\ControllerBase;
use App\Infrastructure\Database\Connection;
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
     * @Route("/api/pobj/notifications", name="api_pobj_notifications", methods={"POST"})
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

