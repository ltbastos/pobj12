<?php

namespace App\Controller\Pobj;

use App\Application\UseCase\Pobj\CalendarioUseCase;
use App\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller para operações relacionadas ao calendário
 */
class CalendarioController extends ControllerBase
{
    private $calendarioUseCase;

    public function __construct(CalendarioUseCase $calendarioUseCase)
    {
        $this->calendarioUseCase = $calendarioUseCase;
    }

    /**
     * @Route("/api/pobj/calendario", name="api_pobj_calendario", methods={"GET"})
     */
    public function handle(Request $request): JsonResponse
    {
        $result = $this->calendarioUseCase->getAll();
        
        return $this->success($result);
    }
}

