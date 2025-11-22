<?php

namespace App\Infrastructure\Persistence;

use Doctrine\ORM\EntityManager;
use App\Domain\DTO\ProdutoDTO;
use App\Domain\Entity\DProduto;
use App\Infrastructure\Helpers\RowMapper;

class ProdutoRepository
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findAll(): array
    {
        return $this->entityManager
            ->getRepository(DProduto::class)
            ->createQueryBuilder('p')
            ->orderBy('p.familia', 'ASC')
            ->addOrderBy('p.indicador', 'ASC')
            ->addOrderBy('p.subindicador', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAllAsArray(): array
    {
        $entities = $this->findAll();
        
        return array_map(function (DProduto $entity) {
            $dto = new ProdutoDTO(
                $entity->getId(),
                (string)$entity->getIdFamilia(),
                $entity->getFamilia(),
                RowMapper::toString($entity->getIdIndicador()),
                $entity->getIndicador(),
                RowMapper::toString($entity->getIdSubindicador()),
                $entity->getSubindicador(),
                RowMapper::toFloat($entity->getPeso())
            );
            
            return $dto->toArray();
        }, $entities);
    }
}

