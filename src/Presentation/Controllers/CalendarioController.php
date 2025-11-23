<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\CalendarioUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller para operações relacionadas ao calendário
 */
class CalendarioController extends ControllerBase
{
    private $calendarioUseCase;

    public function __construct(CalendarioUseCase $calendarioUseCase)
    {
        $this->calendarioUseCase = $calendarioUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $filters = $request->getAttribute('filters');
        $result = $this->calendarioUseCase->handle($filters);
        
        return $this->success($response, $result);
    }
}

