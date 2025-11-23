<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\CalendarioUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller para operações relacionadas ao calendário
 */
class CalendarioController
{
    /** @var CalendarioUseCase */
    private $calendarioUseCase;

    /**
     * @param CalendarioUseCase $calendarioUseCase
     */
    public function __construct(CalendarioUseCase $calendarioUseCase)
    {
        $this->calendarioUseCase = $calendarioUseCase;
    }

    /**
     * Manipula requisições para buscar calendário
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function handle(Request $request, Response $response): Response
    {
        try {
            $result = $this->calendarioUseCase->getAllCalendario();
            
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

