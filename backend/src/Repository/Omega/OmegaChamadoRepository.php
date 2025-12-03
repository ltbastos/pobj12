<?php

namespace App\Repository\Omega;

use App\Entity\Omega\OmegaChamado;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OmegaChamadoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OmegaChamado::class);
    }

    /**
     * Lista todos os chamados ordenados por data de atualização
     * Otimizado com eager loading para evitar N+1 queries
     */
    public function findAllOrderedByUpdated(): array
    {
        return $this->createQueryBuilder('c')
                    ->leftJoin('c.status', 's')
                    ->addSelect('s')
                    ->leftJoin('c.requester', 'r')
                    ->addSelect('r')
                    ->leftJoin('c.owner', 'o')
                    ->addSelect('o')
                    ->leftJoin('c.team', 't')
                    ->addSelect('t')
                    ->orderBy('c.updated', 'DESC')
                    ->addOrderBy('c.opened', 'DESC')
                    ->getQuery()
                    ->getResult();
    }

    /**
     * Salva um chamado no banco de dados
     */
    public function save(OmegaChamado $chamado): void
    {
        $this->getEntityManager()->persist($chamado);
        $this->getEntityManager()->flush();
    }

    /**
     * Gera um ID único para o ticket no formato OME-YYYY-NNNN
     */
    public function generateNextTicketId(): string
    {
        $year = date('Y');
        
        // Busca o último ticket do ano
        $lastTicket = $this->createQueryBuilder('t')
            ->where('t.id LIKE :pattern')
            ->setParameter('pattern', "OME-{$year}-%")
            ->orderBy('t.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
        
        $nextNumber = 1;
        
        if ($lastTicket) {
            $lastId = $lastTicket->getId();
            if (preg_match('/OME-' . $year . '-(\d+)/', $lastId, $matches)) {
                $nextNumber = (int)$matches[1] + 1;
            }
        }
        
        return sprintf('OME-%s-%04d', $year, $nextNumber);
    }
}

