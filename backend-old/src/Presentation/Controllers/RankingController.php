<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\RankingUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class RankingController extends ControllerBase
{
    private $rankingUseCase;

    public function __construct(RankingUseCase $rankingUseCase)
    {
        $this->rankingUseCase = $rankingUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $filters = $request->getAttribute('filters');
        $result = $this->rankingUseCase->handle($filters);
        
        return $this->success($response, $result);
    }
}

