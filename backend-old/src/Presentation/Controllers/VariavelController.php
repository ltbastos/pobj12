<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\VariavelUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class VariavelController extends ControllerBase
{
    private $variavelUseCase;

    public function __construct(VariavelUseCase $variavelUseCase)
    {
        $this->variavelUseCase = $variavelUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $filters = $request->getAttribute('filters');
        $result = $this->variavelUseCase->handle($filters);
        
        return $this->success($response, $result);
    }
}

