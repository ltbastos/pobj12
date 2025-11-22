<?php

namespace App\Infrastructure\Persistence;

use Doctrine\ORM\EntityManager;
use App\Domain\Entity\DEstrutura;
use App\Domain\ValueObject\Cargo;

class EstruturaRepository
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findAllSegmentos(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        
        return $qb->select('DISTINCT e.idSegmento AS id, e.segmento AS label')
            ->from(DEstrutura::class, 'e')
            ->where('e.idSegmento IS NOT NULL')
            ->andWhere('e.segmento IS NOT NULL')
            ->orderBy('e.segmento', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findAllDiretorias(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        
        return $qb->select('DISTINCT e.idDiretoria AS id, e.diretoria AS label')
            ->from(DEstrutura::class, 'e')
            ->where('e.idDiretoria IS NOT NULL')
            ->andWhere('e.diretoria IS NOT NULL')
            ->orderBy('e.diretoria', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findAllRegionais(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        
        return $qb->select('DISTINCT e.idRegional AS id, e.regional AS label')
            ->from(DEstrutura::class, 'e')
            ->where('e.idRegional IS NOT NULL')
            ->andWhere('e.regional IS NOT NULL')
            ->orderBy('e.regional', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findAllAgencias(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        
        return $qb->select('DISTINCT e.idAgencia AS id, e.agencia AS label, e.porte')
            ->from(DEstrutura::class, 'e')
            ->where('e.idAgencia IS NOT NULL')
            ->andWhere('e.agencia IS NOT NULL')
            ->orderBy('e.agencia', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findAllGGestoes(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        
        return $qb->select('DISTINCT e.funcional AS id, e.nome AS label')
            ->from(DEstrutura::class, 'e')
            ->where('e.idCargo = :idCargo')
            ->andWhere('e.funcional IS NOT NULL')
            ->andWhere('e.nome IS NOT NULL')
            ->andWhere("e.funcional != ''")
            ->andWhere("e.nome != ''")
            ->setParameter('idCargo', Cargo::GERENTE_GESTAO)
            ->orderBy('e.nome', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findAllGerentes(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        
        return $qb->select('DISTINCT e.funcional AS id, e.nome AS label')
            ->from(DEstrutura::class, 'e')
            ->where('e.idCargo = :idCargo')
            ->andWhere('e.funcional IS NOT NULL')
            ->andWhere('e.nome IS NOT NULL')
            ->andWhere("e.funcional != ''")
            ->andWhere("e.nome != ''")
            ->setParameter('idCargo', Cargo::GERENTE)
            ->orderBy('e.nome', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findGGestoesForFilter(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        
        return $qb->select('DISTINCT e.funcional AS id, e.nome AS label, e.cargo, id_cargo')
            ->from(DEstrutura::class, 'e')
            ->where('e.idCargo = :idCargo')
            ->andWhere('e.funcional IS NOT NULL')
            ->andWhere('e.nome IS NOT NULL')
            ->setParameter('idCargo', Cargo::GERENTE_GESTAO)
            ->orderBy('e.nome', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }

    public function findGerentesForFilter(): array
    {
        $qb = $this->entityManager->createQueryBuilder();
        
        return $qb->select('DISTINCT e.funcional AS id, e.nome AS label, e.cargo, e.id_cargo')
            ->from(DEstrutura::class, 'e')
            ->where('e.idCargo = :idCargo')
            ->andWhere('e.funcional IS NOT NULL')
            ->andWhere('e.nome IS NOT NULL')
            ->setParameter('idCargo', Cargo::GERENTE)
            ->orderBy('e.nome', 'ASC')
            ->getQuery()
            ->getArrayResult();
    }
}

