<?php

namespace App\Infrastructure\Persistence;

use Doctrine\ORM\EntityManager;
use App\Domain\DTO\OmegaUserDTO;
use App\Domain\Entity\OmegaUsuario;

class OmegaUsersRepository
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findAll(): array
    {
        return $this->entityManager
            ->getRepository(OmegaUsuario::class)
            ->createQueryBuilder('u')
            ->orderBy('u.nome', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllAsArray(): array
    {
        $entities = $this->findAll();
        
        return array_map(function (OmegaUsuario $entity) {
            $dto = new OmegaUserDTO(
                $entity->getId(),
                $entity->getNome(),
                $entity->getFuncional(),
                $entity->getMatricula(),
                $entity->getCargo(),
                $entity->isUsuario(),
                $entity->isAnalista(),
                $entity->isSupervisor(),
                $entity->isAdmin(),
                $entity->isEncarteiramento(),
                $entity->isMeta(),
                $entity->isOrcamento(),
                $entity->isPobj(),
                $entity->isMatriz(),
                $entity->isOutros()
            );
            
            return $dto->toArray();
        }, $entities);
    }
}

