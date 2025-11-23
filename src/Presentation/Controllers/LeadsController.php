<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\LeadsUseCase;
use App\Domain\DTO\FilterDTO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LeadsController
{
    private $leadsUseCase;

    public function __construct(LeadsUseCase $leadsUseCase)
    {
        $this->leadsUseCase = $leadsUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $filters = new FilterDTO($queryParams);
        
        $result = $this->leadsUseCase->getAllLeads($filters);
        
        $response = $response->withHeader('Content-Type', 'application/json; charset=utf-8');
        $response->getBody()->write(json_encode($result, JSON_UNESCAPED_UNICODE));
        return $response;
    }
}

