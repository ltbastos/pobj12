<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\PontosUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class PontosController extends ControllerBase
{
    private $pontosUseCase;

    public function __construct(PontosUseCase $pontosUseCase)
    {
        $this->pontosUseCase = $pontosUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $filters = $request->getAttribute('filters');
        $result = $this->pontosUseCase->handle($filters);
        
        return $this->success($response, $result);
    }
}

