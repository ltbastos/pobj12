<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\EstruturaUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Controller para operações relacionadas à estrutura organizacional
 */
class EstruturaController extends ControllerBase
{
    private $estruturaUseCase;

    public function __construct(EstruturaUseCase $estruturaUseCase)
    {
        $this->estruturaUseCase = $estruturaUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $result = $this->estruturaUseCase->handle();
        
        return $this->success($response, $result);
    }
}

