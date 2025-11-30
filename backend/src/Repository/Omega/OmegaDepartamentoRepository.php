<?php

namespace App\Repository\Omega;

use App\Entity\Omega\OmegaDepartamento;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class OmegaDepartamentoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OmegaDepartamento::class);
    }

    /**
     * Busca departamento por nome
     */
    public function findByNome(string $nome): ?OmegaDepartamento
    {
        return $this->createQueryBuilder('d')
                    ->andWhere('d.departamento = :nome')
                    ->setParameter('nome', $nome)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    /**
     * Lista todos os departamentos ordenados por departamento
     */
    public function findAllOrderedByNome(): array
    {
        return $this->createQueryBuilder('d')
                    ->orderBy('d.ordemDepartamento', 'ASC')
                    ->addOrderBy('d.departamento', 'ASC')
                    ->addOrderBy('d.ordemTipo', 'ASC')
                    ->addOrderBy('d.tipo', 'ASC')
                    ->getQuery()
                    ->getResult();
    }
}

