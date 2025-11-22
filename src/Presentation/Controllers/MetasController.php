<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\MetaUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MetasController
{
    private $metaUseCase;

    public function __construct(MetaUseCase $metaUseCase)
    {
        $this->metaUseCase = $metaUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $result = $this->metaUseCase->getAllMetas();
        
        $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));
        return $response;
    }
}

