<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Middleware para lidar com CORS (Cross-Origin Resource Sharing)
 */
class CorsMiddleware
{
    /**
     * Processa a requisição e adiciona headers CORS
     * 
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        // Obter origem da requisição
        $origin = $request->getHeaderLine('Origin');
        
        // Lista de origens permitidas (pode ser configurada via variável de ambiente)
        $allowedOrigins = $this->getAllowedOrigins();
        
        // Se não há origem na requisição ou origem não está na lista, usar a primeira permitida
        // Em produção, você pode querer retornar erro se origem não estiver permitida
        $allowedOrigin = '*';
        
        if (!empty($origin) && in_array($origin, $allowedOrigins)) {
            $allowedOrigin = $origin;
        } elseif (!empty($allowedOrigins)) {
            // Se '*' está na lista de origens permitidas, usar '*'
            if (in_array('*', $allowedOrigins)) {
                $allowedOrigin = '*';
            } else {
                // Usar a primeira origem permitida
                $allowedOrigin = $allowedOrigins[0];
            }
        }
        
        // Adicionar headers CORS
        $response = $response
            ->withHeader('Access-Control-Allow-Origin', $allowedOrigin)
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS, PATCH')
            ->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, Accept, Origin')
            ->withHeader('Access-Control-Allow-Credentials', 'true')
            ->withHeader('Access-Control-Max-Age', '3600');
        
        // Se for uma requisição OPTIONS (preflight), retornar resposta vazia
        if ($request->getMethod() === 'OPTIONS') {
            return $response->withStatus(200);
        }
        
        // Continuar com a requisição normal
        return $next($request, $response);
    }
    
    /**
     * Obtém as origens permitidas
     * 
     * @return array
     */
    private function getAllowedOrigins()
    {
        // Tentar obter da variável de ambiente
        $envOrigins = getenv('CORS_ALLOWED_ORIGINS');
        
        if ($envOrigins) {
            return array_map('trim', explode(',', $envOrigins));
        }
        
        // Valores padrão para desenvolvimento
        return [
            'http://localhost',       // XAMPP localhost
            'http://localhost:80',    // XAMPP localhost porta 80
            'http://localhost:5173',  // Vite dev server
            'http://localhost:3000',  // Outro dev server comum
            'http://127.0.0.1',
            'http://127.0.0.1:5173',
            'http://127.0.0.1:3000',
            '*'  // Permitir todas as origens em desenvolvimento (remover em produção)
        ];
    }
}

