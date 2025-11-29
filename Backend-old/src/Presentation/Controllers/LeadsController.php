<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\LeadsUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class LeadsController extends ControllerBase
{
    private $leadsUseCase;

    public function __construct(LeadsUseCase $leadsUseCase)
    {
        $this->leadsUseCase = $leadsUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $filters = $request->getAttribute('filters');
        $result = $this->leadsUseCase->handle($filters);
        
        return $this->success($response, $result);
    }
}

