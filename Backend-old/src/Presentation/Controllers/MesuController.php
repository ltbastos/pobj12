<?php

namespace App\Presentation\Controllers;

use App\Application\UseCase\MesuUseCase;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MesuController extends ControllerBase
{
    private $mesuUseCase;

    public function __construct(MesuUseCase $mesuUseCase)
    {
        $this->mesuUseCase = $mesuUseCase;
    }

    public function handle(Request $request, Response $response): Response
    {
        $filters = $request->getAttribute('filters');
        $result = $this->mesuUseCase->handle($filters);

        return $this->success($response, $result);
    }
}

