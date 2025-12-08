<?php

namespace App\Repository\Pobj\Helper;

use App\Domain\DTO\FilterDTO;
use App\Domain\Enum\Cargo;
use App\Entity\Pobj\DEstrutura;
use App\Entity\Pobj\DProduto;
use Doctrine\ORM\EntityManagerInterface;

class ExecFilterBuilder
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Constrói a cláusula WHERE baseada nos filtros.
     * $productAliases permite aplicar filtros de produto/família/indicador quando as tabelas estão presentes na query.
     * Exemplo: ['produto' => 'prod', 'familia' => 'fam', 'indicador' => 'ind', 'subindicador' => 'sub']
     */
    public function buildWhereClause(?FilterDTO $filters, array &$params, bool $includeDateFilters = true, array $productAliases = []): string
    {
        $whereClause = '';

        if (!$filters) {
            return $whereClause;
        }

        $estruturaTable = $this->getTableName(DEstrutura::class);
        $gerente = $filters->getGerente();
        $gerenteGestao = $filters->getGerenteGestao();
        $agencia = $filters->getAgencia();
        $regional = $filters->getRegional();
        $diretoria = $filters->getDiretoria();
        $segmento = $filters->getSegmento();
        $familia = $filters->getFamilia();
        $indicador = $filters->getIndicador();
        $subindicador = $filters->getSubindicador();

        if ($gerente !== null && $gerente !== '') {
            $gerenteFuncional = $this->getFuncionalFromIdOrFuncional($gerente, Cargo::GERENTE);
            if ($gerenteFuncional) {
                $whereClause .= " AND est.funcional = :gerenteFuncional";
                $params['gerenteFuncional'] = $gerenteFuncional;
            }
        } elseif ($gerenteGestao !== null && $gerenteGestao !== '') {
            $gerenteGestaoFuncional = $this->getFuncionalFromIdOrFuncional($gerenteGestao, Cargo::GERENTE_GESTAO);
            if ($gerenteGestaoFuncional) {
                $whereClause .= " AND EXISTS (
                    SELECT 1 FROM {$estruturaTable} AS ggestao 
                    WHERE ggestao.funcional = :gerenteGestaoFuncional
                    AND ggestao.cargo_id = :cargoGerenteGestao
                    AND ggestao.segmento_id = est.segmento_id
                    AND ggestao.diretoria_id = est.diretoria_id
                    AND ggestao.regional_id = est.regional_id
                    AND ggestao.agencia_id = est.agencia_id
                )";
                $params['gerenteGestaoFuncional'] = $gerenteGestaoFuncional;
                $params['cargoGerenteGestao'] = Cargo::GERENTE_GESTAO;
            }
        } else {
            if ($agencia !== null && $agencia !== '') {
                $whereClause .= " AND est.agencia_id = :agencia";
                $params['agencia'] = $agencia;
            } elseif ($regional !== null && $regional !== '') {
                $whereClause .= " AND est.regional_id = :regional";
                $params['regional'] = $regional;
            } elseif ($diretoria !== null && $diretoria !== '') {
                $whereClause .= " AND est.diretoria_id = :diretoria";
                $params['diretoria'] = $diretoria;
            } elseif ($segmento !== null && $segmento !== '') {
                $whereClause .= " AND est.segmento_id = :segmento";
                $params['segmento'] = $segmento;
            }
        }

        if ($includeDateFilters) {
            $dataInicio = $filters->getDataInicio();
            $dataFim = $filters->getDataFim();

            if ($dataInicio !== null && $dataInicio !== '') {
                $whereClause .= " AND c.data >= :dataInicio";
                $params['dataInicio'] = $dataInicio;
            }

            if ($dataFim !== null && $dataFim !== '') {
                $whereClause .= " AND c.data <= :dataFim";
                $params['dataFim'] = $dataFim;
            }
        }

        // Filtros por produto/família/indicador (somente se aliases foram informados na query)
        $produtoTable = $this->getTableName(DProduto::class);

        if ($familia !== null && $familia !== '' ) {
            if (isset($productAliases['familia'])) {
                $whereClause .= " AND {$productAliases['familia']}.id = :familia";
                $params['familia'] = $familia;
            } elseif (isset($productAliases['realizado'])) {
                $whereClause .= " AND EXISTS (SELECT 1 FROM {$produtoTable} prod_fam WHERE prod_fam.id = {$productAliases['realizado']}.produto_id AND prod_fam.familia_id = :familia)";
                $params['familia'] = $familia;
            } elseif (isset($productAliases['meta'])) {
                $whereClause .= " AND EXISTS (SELECT 1 FROM {$produtoTable} prod_fam WHERE prod_fam.id = {$productAliases['meta']}.produto_id AND prod_fam.familia_id = :familia)";
                $params['familia'] = $familia;
            }
        }

        if ($indicador !== null && $indicador !== '' ) {
            if (isset($productAliases['indicador'])) {
                $whereClause .= " AND {$productAliases['indicador']}.id = :indicador";
                $params['indicador'] = $indicador;
            } elseif (isset($productAliases['realizado'])) {
                $whereClause .= " AND EXISTS (SELECT 1 FROM {$produtoTable} prod_ind WHERE prod_ind.id = {$productAliases['realizado']}.produto_id AND prod_ind.indicador_id = :indicador)";
                $params['indicador'] = $indicador;
            } elseif (isset($productAliases['meta'])) {
                $whereClause .= " AND EXISTS (SELECT 1 FROM {$produtoTable} prod_ind WHERE prod_ind.id = {$productAliases['meta']}.produto_id AND prod_ind.indicador_id = :indicador)";
                $params['indicador'] = $indicador;
            }
        }

        if ($subindicador !== null && $subindicador !== '' && isset($productAliases['subindicador'])) {
            $whereClause .= " AND {$productAliases['subindicador']}.id = :subindicador";
            $params['subindicador'] = $subindicador;
        }

        return $whereClause;
    }

    /**
     * Converte ID ou funcional para funcional baseado no cargo
     */
    public function getFuncionalFromIdOrFuncional($idOrFuncional, int $cargoId): ?string
    {
        if ($idOrFuncional === null || $idOrFuncional === '') {
            return null;
        }

        if (!is_numeric($idOrFuncional)) {
            return (string)$idOrFuncional;
        }

        $dEstruturaTable = $this->getTableName(DEstrutura::class);
        $conn = $this->entityManager->getConnection();
        
        $sql = "SELECT funcional FROM {$dEstruturaTable} 
                WHERE id = :id AND cargo_id = :cargoId 
                LIMIT 1";
        
        $result = $conn->executeQuery($sql, [
            'id' => (int)$idOrFuncional,
            'cargoId' => $cargoId
        ]);
        
        $row = $result->fetchAssociative();
        $result->free();
        
        return $row ? ($row['funcional'] ?? null) : null;
    }

    /**
     * Obtém o nome da tabela de uma entidade
     */
    private function getTableName(string $entityClass): string
    {
        return $this->entityManager
            ->getClassMetadata($entityClass)
            ->getTableName();
    }
}
