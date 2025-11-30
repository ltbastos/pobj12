<?php

namespace App\Controller\Pobj;

use App\Application\UseCase\Pobj\ProdutoUseCase;
use App\Controller\ControllerBase;
use App\Domain\DTO\FilterDTO;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ProdutosController extends ControllerBase
{
    private $produtoUseCase;

    public function __construct(ProdutoUseCase $produtoUseCase)
    {
        $this->produtoUseCase = $produtoUseCase;
    }

    /** @Route("/api/produtos", name="api_produtos", methods={"GET"}) */
    public function handle(Request $request): JsonResponse
    {
        $filters = new FilterDTO($request->query->all());
        $result = $this->produtoUseCase->handle($filters);
        
        return $this->success($result);
    }

    /** @Route("/api/produtos/mensais", name="api_produtos_mensais", methods={"GET"}) */
    public function handleMonthly(Request $request): JsonResponse
    {
        $filters = new FilterDTO($request->query->all());
        $result = $this->produtoUseCase->handleMonthly($filters);
        
        return $this->success($result);
    }
}

