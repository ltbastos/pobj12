<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\RealizadoUseCase;
use App\Domain\DTO\FilterDTO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller para operações relacionadas a realizados
 */
class RealizadosController
{
    /** @var RealizadoUseCase */
    private $realizadoUseCase;

    /**
     * @param RealizadoUseCase $realizadoUseCase
     */
    public function __construct(RealizadoUseCase $realizadoUseCase)
    {
        $this->realizadoUseCase = $realizadoUseCase;
    }

    /**
     * Manipula requisições para buscar realizados
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function handle(Request $request, Response $response): Response
    {
        try {
            $queryParams = $request->getQueryParams();
            $filters = new FilterDTO($queryParams);
            
            $result = $this->realizadoUseCase->getAllRealizados($filters);
            
            $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));
            
            return $response;
        } catch (\Exception $e) {
            $response = $response->withStatus(500)
                ->withHeader('Content-Type', 'application/json; charset=utf-8');
            $response->getBody()->write(json_encode([
                'error' => 'Erro ao processar requisição',
                'message' => $e->getMessage()
            ], JSON_UNESCAPED_UNICODE));
            
            return $response;
        }
    }
}

