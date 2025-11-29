<?php

namespace App\Controller\Pobj;

use App\Application\UseCase\Pobj\SimuladorUseCase;
use App\Controller\ControllerBase;
use App\Domain\DTO\FilterDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SimuladorController extends ControllerBase
{
    /** @var SimuladorUseCase */
    private $simuladorUseCase;

    public function __construct(SimuladorUseCase $simuladorUseCase)
    {
        $this->simuladorUseCase = $simuladorUseCase;
    }

    /**
     * @Route("/api/pobj/simulador", name="api_pobj_simulador", methods={"GET"})
     */
    public function handle(Request $request): JsonResponse
    {
        $filters = new FilterDTO($request->query->all());
        $result = $this->simuladorUseCase->handle($filters);

        return $this->success($result);
    }
}

