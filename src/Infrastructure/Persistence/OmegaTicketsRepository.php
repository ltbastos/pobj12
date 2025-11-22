<?php

namespace App\Infrastructure\Persistence;

use Doctrine\ORM\EntityManager;
use App\Domain\DTO\OmegaTicketDTO;
use App\Domain\Entity\OmegaChamado;

class OmegaTicketsRepository
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findAll(): array
    {
        return $this->entityManager
            ->getRepository(OmegaChamado::class)
            ->createQueryBuilder('c')
            ->orderBy('c.updated', 'DESC')
            ->addOrderBy('c.opened', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllAsArray(): array
    {
        $entities = $this->findAll();
        
        return array_map(function (OmegaChamado $entity) {
            $opened = $entity->getOpened() ? $entity->getOpened()->format('Y-m-d H:i:s') : null;
            $updated = $entity->getUpdated() ? $entity->getUpdated()->format('Y-m-d H:i:s') : null;
            $dueDate = $entity->getDueDate() ? $entity->getDueDate()->format('Y-m-d H:i:s') : null;
            
            $dto = new OmegaTicketDTO(
                $entity->getId(),
                $entity->getSubject(),
                $entity->getCompany(),
                $entity->getProductId(),
                $entity->getProductLabel(),
                $entity->getFamily(),
                $entity->getSection(),
                $entity->getQueue(),
                $entity->getCategory(),
                $entity->getStatus(),
                $entity->getPriority(),
                $opened,
                $updated,
                $dueDate,
                $entity->getRequesterId(),
                $entity->getOwnerId(),
                $entity->getTeamId(),
                $entity->getHistory(),
                $entity->getDiretoria(),
                $entity->getGerencia(),
                $entity->getAgencia(),
                $entity->getGerenteGestao(),
                $entity->getGerente(),
                $entity->getCredit(),
                $entity->getAttachment()
            );
            
            return $dto->toArray();
        }, $entities);
    }
}

