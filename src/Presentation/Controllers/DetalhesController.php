<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\DetalhesUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class DetalhesController extends ControllerBase
{
    private $detalhesUseCase;

    public function __construct(DetalhesUseCase $detalhesUseCase)
    {
        $this->detalhesUseCase = $detalhesUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $filters = $request->getAttribute('filters');
        $result = $this->detalhesUseCase->handle($filters);
        
        return $this->success($response, $result);
    }
}

