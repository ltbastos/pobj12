<?php

namespace App\Controller\Pobj;

use App\Application\UseCase\Pobj\RankingUseCase;
use App\Controller\ControllerBase;
use App\Domain\DTO\FilterDTO;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RankingController extends ControllerBase
{
    private $rankingUseCase;

    public function __construct(RankingUseCase $rankingUseCase)
    {
        $this->rankingUseCase = $rankingUseCase;
    }

    /**
     * Retorna ranking de colaboradores
     * 
     * @Route("/api/pobj/ranking", name="api_pobj_ranking", methods={"GET"})
     * 
     * @OA\Get(
     *     path="/api/pobj/ranking",
     *     summary="Ranking de colaboradores",
     *     description="Retorna ranking ordenado por pontuação",
     *     tags={"POBJ", "Ranking"},
     *     security={{"ApiKeyAuth": {}}},
     *
     *     @OA\Parameter(
     *         name="dataInicio",
     *         in="query",
     *         description="Data de início (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="dataFim",
     *         in="query",
     *         description="Data de fim (YYYY-MM-DD)",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Número da página",
     *         required=false,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Itens por página",
     *         required=false,
     *         @OA\Schema(type="integer", example=20)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Ranking retornado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="data", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="pagination", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Não autorizado"),
     *     @OA\Response(response=429, description="Rate limit excedido")
     * )
     */
    public function handle(Request $request): JsonResponse
    {
        $filters = new FilterDTO($request->query->all());
        $result = $this->rankingUseCase->handle($filters);
        
        return $this->success($result);
    }
}




