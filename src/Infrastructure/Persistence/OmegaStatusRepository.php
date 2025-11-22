<?php

namespace App\Infrastructure\Persistence;

use Doctrine\ORM\EntityManager;
use App\Domain\DTO\OmegaStatusDTO;
use App\Domain\Entity\OmegaStatus;

class OmegaStatusRepository
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findAll(): array
    {
        return $this->entityManager
            ->getRepository(OmegaStatus::class)
            ->createQueryBuilder('s')
            ->orderBy('s.ordem', 'ASC')
            ->addOrderBy('s.label', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllAsArray(): array
    {
        $entities = $this->findAll();
        
        return array_map(function (OmegaStatus $entity) {
            $dto = new OmegaStatusDTO(
                $entity->getId(),
                $entity->getLabel(),
                $entity->getTone(),
                $entity->getDescricao(),
                $entity->getOrdem(),
                $entity->getDepartamentoId()
            );
            
            return $dto->toArray();
        }, $entities);
    }
}

