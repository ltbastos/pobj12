<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\MetaUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MetasController extends ControllerBase
{
    private $metaUseCase;

    public function __construct(MetaUseCase $metaUseCase)
    {
        $this->metaUseCase = $metaUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $filters = $request->getAttribute('filters');
        
        $result = $this->metaUseCase->handle($filters);
        
        return $this->success($response, $result);
    }
}

