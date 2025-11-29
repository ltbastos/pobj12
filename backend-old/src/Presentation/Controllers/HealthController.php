<?php

namespace App\Presentation\Controllers;

use Illuminate\Database\Capsule\Manager as DB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class HealthController
{
    public function check(Request $request, Response $response): Response
    {
        $status = [
            'status' => 'ok',
            'database' => 'ok',
            'timestamp' => date('c'),
        ];

        try {
            $result = DB::select('SELECT 1 as test');
            
            if (empty($result) || !isset($result[0]->test)) {
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

