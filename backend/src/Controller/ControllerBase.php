<?php

namespace App\Controller;

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

    protected function error(string $message, int $status = 400): JsonResponse
    {
        return $this->json([
            'success' => false,
            'error' => $message
        ], $status);
    }
}

