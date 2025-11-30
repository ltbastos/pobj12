<?php

namespace App\Controller\Omega;

use App\Application\UseCase\Omega\OmegaTicketsUseCase;
use App\Controller\ControllerBase;
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

    /** @Route("/api/omega/tickets", name="api_omega_tickets", methods={"GET", "POST"}) */
    public function handle(Request $request): JsonResponse
    {
        if ($request->getMethod() === 'POST') {
            return $this->create($request);
        }
        
        $result = $this->omegaTicketsUseCase->getAllTickets();
        
        return $this->success($result);
    }

    /** @Route("/api/omega/tickets/{id}", name="api_omega_tickets_update", methods={"POST", "PUT"}) */
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

