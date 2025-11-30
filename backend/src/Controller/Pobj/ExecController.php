<?php

namespace App\Controller\Pobj;

use App\Application\UseCase\Pobj\ExecUseCase;
use App\Controller\ControllerBase;
use App\Domain\DTO\FilterDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ExecController extends ControllerBase
{
    private $execUseCase;

    public function __construct(ExecUseCase $execUseCase)
    {
        $this->execUseCase = $execUseCase;
    }

    /** @Route("/api/pobj/exec", name="api_pobj_exec", methods={"GET"}) */
    public function handle(Request $request): JsonResponse
    {
        $filters = new FilterDTO($request->query->all());
        $result = $this->execUseCase->handle($filters);

        return $this->success($result);
    }
}


