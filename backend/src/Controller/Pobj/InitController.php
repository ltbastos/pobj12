<?php

namespace App\Controller\Pobj;

use App\Application\UseCase\Pobj\InitUseCase;
use App\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller para operações relacionadas à estrutura organizacional
 */
class InitController extends ControllerBase
{
    private $initUseCase;

    public function __construct(InitUseCase $initUseCase)
    {
        $this->initUseCase = $initUseCase;
    }

    /**
     * @Route("/api/pobj/init", name="api_pobj_init", methods={"GET"})
     */
    public function handle(Request $request): JsonResponse
    {
        $result = $this->initUseCase->handle();
        
        return $this->success($result);
    }
}

