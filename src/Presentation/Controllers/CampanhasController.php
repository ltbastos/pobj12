<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\CampanhasUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class CampanhasController extends ControllerBase
{
    private $campanhasUseCase;

    public function __construct(CampanhasUseCase $campanhasUseCase)
    {
        $this->campanhasUseCase = $campanhasUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $filters = $request->getAttribute('filters');
        $result = $this->campanhasUseCase->handle($filters);
        
        return $this->success($response, $result);
    }
}

