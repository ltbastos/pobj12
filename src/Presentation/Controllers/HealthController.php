<?php

namespace App\Presentation\Controllers;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Container;

class HealthController
{
    protected $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function check(Request $request, Response $response): Response
    {
        $status = [
            'status' => 'ok',
            'database' => 'ok',
            'timestamp' => date('c'),
        ];

        try {
            $pdo = $this->container->get(PDO::class);
            
            $stmt = $pdo->query('SELECT 1 as test');
            $result = $stmt->fetch();
            
            if ($result === false || !isset($result['test'])) {
                $status['status'] = 'error';
                $status['database'] = 'error';
                $status['message'] = 'Banco de dados não respondeu corretamente';
                
                $response = $response->withStatus(503)
                    ->withHeader('Content-Type', 'application/json; charset=utf-8');
                $response->getBody()->write(json_encode($status, JSON_UNESCAPED_UNICODE));
                return $response;
            }
            
            $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode($status, JSON_UNESCAPED_UNICODE));
            return $response;
        } catch (\Throwable $e) {
            if ($this->container->has('logger')) {
                $logger = $this->container->get('logger');
                $logger->error('Health check failed', [
                    'endpoint' => 'health',
                    'error' => $e->getMessage(),
                ]);
            }
            
            $status['status'] = 'error';
            $status['database'] = 'error';
            $status['message'] = 'Não foi possível conectar ao banco de dados';
            $status['error'] = $e->getMessage();
            
            $response = $response->withStatus(503)
                ->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode($status, JSON_UNESCAPED_UNICODE));
            return $response;
        }
    }
}

