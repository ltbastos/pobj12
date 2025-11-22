<?php

namespace App\Infrastructure\Persistence;

use Doctrine\ORM\EntityManager;
use App\Domain\DTO\CampanhasDTO;
use App\Domain\Entity\FCampanhas;
use App\Infrastructure\Helpers\DateFormatter;
use App\Infrastructure\Helpers\RowMapper;

class CampanhasRepository
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findAll(): array
    {
        return $this->entityManager
            ->getRepository(FCampanhas::class)
            ->createQueryBuilder('c')
            ->orderBy('c.data', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllAsArray(): array
    {
        $entities = $this->findAll();
        
        return array_map(function (FCampanhas $entity) {
            $dataIso = DateFormatter::toIsoDate($entity->getData());
            
            $dto = new CampanhasDTO(
                $entity->getCampanhaId(),
                $entity->getSprintId(),
                $entity->getSegmento(),
                $entity->getSegmentoId(),
                $entity->getDiretoriaId(),
                $entity->getDiretoriaNome(),
                $entity->getGerenciaRegionalId(),
                $entity->getRegionalNome(),
                $entity->getAgenciaId(),
                $entity->getAgenciaNome(),
                $entity->getGerenteGestaoId(),
                $entity->getGerenteGestaoNome(),
                $entity->getGerenteId(),
                $entity->getGerenteNome(),
                $entity->getFamiliaId(),
                $entity->getIdIndicador(),
                $entity->getDsIndicador(),
                $entity->getSubproduto(),
                $entity->getSubindicadorCodigo(),
                $entity->getFamiliaCodigo(),
                $entity->getIndicadorCodigo(),
                $entity->getCarteira(),
                $dataIso,
                $dataIso,
                RowMapper::toFloat($entity->getLinhas()),
                RowMapper::toFloat($entity->getCash()),
                RowMapper::toFloat($entity->getConquista()),
                $entity->getAtividade()
            );
            
            return $dto->toArray();
        }, $entities);
    }
}

