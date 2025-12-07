<?php

namespace App\Repository\Pobj\Helper;

use App\Domain\DTO\FilterDTO;
use App\Domain\Enum\Cargo;
use App\Entity\Pobj\DEstrutura;
use Doctrine\ORM\EntityManagerInterface;

class ExecFilterBuilder
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Constrói a cláusula WHERE baseada nos filtros
     */
    public function buildWhereClause(?FilterDTO $filters, array &$params, bool $includeDateFilters = true): string
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
