<?php

namespace App\Middleware;

class ErrorMiddleware
{
    public function __invoke($request, $response, $next)
    {
        try {
            return $next($request, $response);
        } catch (\Throwable $e) {

            $payload = [
                'error' => true,
                'message' => $e->getMessage(),
                'type' => (new \ReflectionClass($e))->getShortName(),
                'code' => $e->getCode(),
            ];

            $response = $response
                ->withStatus(500)
                ->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode($payload, JSON_UNESCAPED_UNICODE));
            return $response;
        }
    }
}
