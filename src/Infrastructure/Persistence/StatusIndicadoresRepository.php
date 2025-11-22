<?php

namespace App\Infrastructure\Persistence;

use Doctrine\ORM\EntityManager;
use App\Domain\Entity\DStatusIndicador;
use App\Domain\ValueObject\StatusIndicador;

class StatusIndicadoresRepository
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findAll(): array
    {
        $entities = $this->entityManager
            ->getRepository(DStatusIndicador::class)
            ->createQueryBuilder('s')
            ->orderBy('s.id', 'ASC')
            ->getQuery()
            ->getResult();

        if (empty($entities)) {
            return [];
        }

        return $entities;
    }

    public function findAllAsArray(): array
    {
        $entities = $this->findAll();
        
        if (empty($entities)) {
            return StatusIndicador::getDefaults();
        }

        $result = [];
        foreach ($entities as $entity) {
            $result[] = [
                'id' => $entity->getId(),
                'label' => $entity->getStatus(),
            ];
        }

        return $result;
    }

    public function findAllForFilter(): array
    {
        $entities = $this->findAll();
        
        if (empty($entities)) {
            return StatusIndicador::getDefaultsForFilter();
        }

        $result = [];
        foreach ($entities as $entity) {
            $result[] = [
                'id' => $entity->getId(),
                'label' => $entity->getStatus(),
            ];
        }

        return $result;
    }
}

