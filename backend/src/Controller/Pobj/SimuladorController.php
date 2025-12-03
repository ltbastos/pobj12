<?php

namespace App\Controller\Pobj;

use App\Application\UseCase\Pobj\SimuladorUseCase;
use App\Controller\ControllerBase;
use App\Domain\DTO\FilterDTO;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SimuladorController extends ControllerBase
{
    private $simuladorUseCase;

    public function __construct(SimuladorUseCase $simuladorUseCase)
    {
        $this->simuladorUseCase = $simuladorUseCase;
    }

    /**
     * Retorna dados para simulação de cenários
     * 
     * @Route("/api/pobj/simulador", name="api_pobj_simulador", methods={"GET"})
     * 
     * @OA\Get(
     *     path="/api/pobj/simulador",
     *     summary="Simulador de cenários",
     *     description="Retorna dados para simulação de diferentes cenários e projeções",
     *     tags={"POBJ", "Simulador"},
     *     security={{"ApiKeyAuth": {}}},
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
     *     @OA\Response(
     *         response=200,
     *         description="Dados do simulador retornados com sucesso",
     *         @OA\Schema(
     *             
     *             @OA\Property(property="success",  example=true),
     *             @OA\Property(property="data", 
     *                 @OA\Property(property="scenarios",  @OA\Items(type="object")),
     *                 @OA\Property(property="projections",  @OA\Items(type="object"))
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
        $result = $this->simuladorUseCase->handle($filters);

        return $this->success($result);
    }
}




