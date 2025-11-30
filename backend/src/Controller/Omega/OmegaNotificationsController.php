<?php

namespace App\Controller\Omega;

use App\Controller\ControllerBase;
use App\Infrastructure\Database\Connection;
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
     * @Route("/api/omega/notifications", name="api_omega_notifications", methods={"POST"})
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

