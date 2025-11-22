<?php

namespace App\Infrastructure\Persistence;

use Doctrine\ORM\EntityManager;
use App\Domain\DTO\DetalhesDTO;
use App\Domain\Entity\FDetalhes;
use App\Infrastructure\Helpers\DateFormatter;
use App\Infrastructure\Helpers\RowMapper;

class DetalhesRepository
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findAll(): array
    {
        return $this->entityManager
            ->getRepository(FDetalhes::class)
            ->createQueryBuilder('d')
            ->orderBy('d.data', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllAsArray(): array
    {
        $entities = $this->findAll();
        
        return array_map(function (FDetalhes $entity) {
            $dataIso = DateFormatter::toIsoDate($entity->getData());
            $competenciaIso = DateFormatter::toIsoDate($entity->getCompetencia());
            
            $dto = new DetalhesDTO(
                $entity->getRegistroId(),
                $entity->getSegmento(),
                $entity->getSegmentoId(),
                $entity->getDiretoriaId(),
                $entity->getDiretoriaNome(),
                $entity->getGerenciaRegionalId(),
                $entity->getGerenciaRegionalNome(),
                $entity->getAgenciaId(),
                $entity->getAgenciaNome(),
                $entity->getGerenteGestaoId(),
                $entity->getGerenteGestaoNome(),
                $entity->getGerenteId(),
                $entity->getGerenteNome(),
                $entity->getFamiliaId(),
                $entity->getFamiliaNome(),
                $entity->getIdIndicador(),
                $entity->getDsIndicador(),
                $entity->getSubindicador(),
                $entity->getIdSubindicador(),
                $entity->getFamiliaId(),
                $entity->getIdIndicador(),
                $entity->getCarteira(),
                $entity->getCanalVenda(),
                $entity->getTipoVenda(),
                $entity->getModalidadePagamento(),
                $dataIso,
                $competenciaIso,
                RowMapper::toFloat($entity->getValorMeta()),
                RowMapper::toFloat($entity->getValorRealizado()),
                RowMapper::toFloat($entity->getQuantidade()),
                RowMapper::toFloat($entity->getPeso()),
                RowMapper::toFloat($entity->getPontos()),
                $entity->getStatusId()
            );
            
            return $dto->toArray();
        }, $entities);
    }
}

