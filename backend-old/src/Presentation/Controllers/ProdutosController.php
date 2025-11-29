<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\ProdutoUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class ProdutosController extends ControllerBase
{
    private $produtoUseCase;

    public function __construct(ProdutoUseCase $produtoUseCase)
    {
        $this->produtoUseCase = $produtoUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $filters = $request->getAttribute('filters');
        $result = $this->produtoUseCase->handle($filters);
        
        return $this->success($response, $result);
    }

    public function handleMonthly(Request $request, Response $response): Response
    {
        $filters = $request->getAttribute('filters');
        $result = $this->produtoUseCase->handleMonthly($filters);
        
        return $this->success($response, $result);
    }
}

