<?php

namespace App\Security;

use App\Entity\Security\ApiKey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ApiKeyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ApiKey::class);
    }

    /**
     * Busca uma API key válida (não expirada e ativa)
     */
    public function findValidKey(string $key): ?ApiKey
    {
        return $this->createQueryBuilder('k')
            ->where('k.key = :key')
            ->andWhere('k.active = :active')
            ->andWhere('k.expiresAt IS NULL OR k.expiresAt > :now')
            ->setParameter('key', $key)
            ->setParameter('active', true)
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Atualiza o último uso da API key
     */
    public function updateLastUsed(ApiKey $apiKey): void
    {
        $apiKey->setLastUsedAt(new \DateTime());
        $this->getEntityManager()->flush();
    }

    /**
     * Gera uma nova API key
     */
    public function generateKey(string $name, string $description = null, \DateTime $expiresAt = null): ApiKey
    {
        $key = bin2hex(random_bytes(32)); // 64 caracteres hexadecimais

        $apiKey = new ApiKey();
        $apiKey->setName($name);
        $apiKey->setDescription($description);
        $apiKey->setKey($key);
        $apiKey->setActive(true);
        $apiKey->setExpiresAt($expiresAt);
        $apiKey->setCreatedAt(new \DateTime());

        $this->getEntityManager()->persist($apiKey);
        $this->getEntityManager()->flush();

        return $apiKey;
    }
}

