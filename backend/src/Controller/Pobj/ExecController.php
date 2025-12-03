<?php

namespace App\Controller\Pobj;

use App\Application\UseCase\Pobj\ExecUseCase;
use App\Controller\ControllerBase;
use App\Domain\DTO\FilterDTO;
use OpenApi\Annotations as OA;
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
    /**
     * Retorna dados executivos/consolidados
     * 
     * @Route("/api/pobj/exec", name="api_pobj_exec", methods={"GET"})
     * 
     * @OA\Get(
     *     path="/api/pobj/exec",
     *     summary="Dados executivos",
     *     description="Retorna dados consolidados e visão executiva do POBJ",
     *     tags={"POBJ", "Executivo"},
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
     *         name="segmentoId",
     *         in="query",
     *         description="ID do segmento",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="diretoriaId",
     *         in="query",
     *         description="ID da diretoria",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="regionalId",
     *         in="query",
     *         description="ID da regional",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="agenciaId",
     *         in="query",
     *         description="ID da agência",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Dados executivos retornados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="summary", type="object"),
     *                 @OA\Property(property="metrics", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="trends", type="array", @OA\Items(type="object"))
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
        $result = $this->execUseCase->handle($filters);

        return $this->success($result);
    }
}




