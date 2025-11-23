<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\PontosUseCase;
use App\Domain\DTO\FilterDTO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Exception;

class PontosController
{
    private $pontosUseCase;

    public function __construct(PontosUseCase $pontosUseCase)
    {
        $this->pontosUseCase = $pontosUseCase;
    }

    public function handle(Request $request, Response $response)
    {
        try {
            $queryParams = $request->getQueryParams();
            $filters = new FilterDTO($queryParams);
            
            $result = $this->pontosUseCase->getAllPontos($filters);
            
            $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));
            return $response;
        } catch (Exception $e) {
            $response = $response->withStatus(500);
            $response->getBody()->write(json_encode([
                'error' => 'Erro ao buscar pontos',
                'message' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));
            return $response;
        }
    }
}

