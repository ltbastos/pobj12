<?php

namespace App\Controller;

use App\Exception\BadRequestException;
use App\Exception\NotFoundException;
use App\Exception\ValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;

abstract class ControllerBase
{
    protected function json($data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status, [
            'Content-Type' => 'application/json; charset=utf-8'
        ], false);
    }

    protected function success($data): JsonResponse
    {
        if (is_array($data) && isset($data['data']) && isset($data['pagination'])) {
            return $this->json([
                'success' => true,
                'data' => $data['data'],
                'pagination' => $data['pagination']
            ], 200);
        }
        
        return $this->json([
            'success' => true,
            'data' => $data
        ], 200);
    }

    /**
     * @deprecated Use exceptions customizadas em vez deste método
     * Este método mantém compatibilidade com código existente
     */
    protected function error(string $message, int $status = 400): JsonResponse
    {
        return $this->json([
            'success' => false,
            'error' => $message
        ], $status);
    }

    /**
     * Lança exceção de validação
     */
    protected function throwValidationError(string $message, array $validationErrors = []): void
    {
        throw new ValidationException($message, $validationErrors);
    }

    /**
     * Lança exceção de recurso não encontrado
     */
    protected function throwNotFound(string $message = 'Recurso não encontrado', string $resource = null): void
    {
        throw new NotFoundException($message, $resource);
    }

    /**
     * Lança exceção de requisição inválida
     */
    protected function throwBadRequest(string $message = 'Requisição inválida', array $details = []): void
    {
        throw new BadRequestException($message, $details);
    }
}

