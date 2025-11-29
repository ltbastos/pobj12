<?php

namespace App\Repository\Pobj;

use App\Entity\Pobj\DEstrutura;
use App\Domain\Enum\Cargo;
use App\Domain\DTO\Init\GerenteWithGestorDTO;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class DEstruturaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DEstrutura::class);
    }

    /**
     * Busca estrutura por funcional
     */
    public function findByFuncional(string $funcional): ?DEstrutura
    {
        return $this->createQueryBuilder('e')
                    ->andWhere('e.funcional = :funcional')
                    ->setParameter('funcional', $funcional)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    public function findGerentes(): array
    {
        return $this->createQueryBuilder('e')
                    ->innerJoin('e.cargo', 'c')
                    ->andWhere('c.id = :cargo_id')
                    ->setParameter('cargo_id', Cargo::GERENTE)
                    ->getQuery()
                    ->getResult();
    }
    public function findGerentesGestoes(): array
    {
        return $this->createQueryBuilder('e')
                    ->innerJoin('e.cargo', 'c')
                    ->andWhere('c.id = :cargo_id')
                    ->setParameter('cargo_id', Cargo::GERENTE_GESTAO)
                    ->getQuery()
                    ->getResult();
    }

    /**
     * Busca gerentes com seus respectivos gerentes de gestão da mesma agência
     * @return GerenteWithGestorDTO[]
     */
    public function findGerentesWithGestor(): array
    {
        $gerentes = $this->createQueryBuilder('e')
            ->innerJoin('e.cargo', 'c')
            ->andWhere('c.id = :cargo_id')
            ->andWhere('e.funcional IS NOT NULL')
            ->andWhere('e.nome IS NOT NULL')
            ->setParameter('cargo_id', Cargo::GERENTE)
            ->orderBy('e.nome', 'ASC')
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($gerentes as $gerente) {
            $gestor = null;

            if ($gerente->getAgencia()) {
                $gestor = $this->createQueryBuilder('g')
                    ->innerJoin('g.cargo', 'cg')
                    ->andWhere('cg.id = :cargo_gestao_id')
                    ->andWhere('g.agencia = :agencia')
                    ->andWhere('g.funcional IS NOT NULL')
                    ->setParameter('cargo_gestao_id', Cargo::GERENTE_GESTAO)
                    ->setParameter('agencia', $gerente->getAgencia())
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();
            }

            $result[] = new GerenteWithGestorDTO(
                $gerente->getId(),
                $gerente->getFuncional(),
                $gerente->getNome(),
                $gestor ? $gestor->getId() : null
            );
        }

        return $result;
    }
}

