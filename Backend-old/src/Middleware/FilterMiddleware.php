<?php

namespace App\Middleware;

use App\Domain\DTO\FilterDTO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Middleware para processar e adicionar filtros da requisição
 */
class FilterMiddleware
{
    /**
     * Processa os query params e adiciona FilterDTO como atributo da requisição
     * 
     * @param Request $request
     * @param Response $response
     * @param callable $next
     * @return Response
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        $filters = new FilterDTO($request->getQueryParams());
        $request = $request->withAttribute('filters', $filters);
        
        return $next($request, $response);
    }
}

