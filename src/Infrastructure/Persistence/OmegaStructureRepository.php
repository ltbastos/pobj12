<?php

namespace App\Infrastructure\Persistence;

use Doctrine\ORM\EntityManager;
use App\Domain\DTO\OmegaStructureDTO;
use App\Domain\Entity\OmegaDepartamento;

class OmegaStructureRepository
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findAll(): array
    {
        return $this->entityManager
            ->getRepository(OmegaDepartamento::class)
            ->createQueryBuilder('d')
            ->orderBy('d.ordemDepartamento', 'ASC')
            ->addOrderBy('d.ordemTipo', 'ASC')
            ->addOrderBy('d.departamento', 'ASC')
            ->addOrderBy('d.tipo', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllAsArray(): array
    {
        $entities = $this->findAll();
        
        return array_map(function (OmegaDepartamento $entity) {
            $dto = new OmegaStructureDTO(
                $entity->getDepartamento(),
                $entity->getTipo(),
                $entity->getDepartamentoId(),
                $entity->getOrdemDepartamento(),
                $entity->getOrdemTipo()
            );
            
            return $dto->toArray();
        }, $entities);
    }
}

