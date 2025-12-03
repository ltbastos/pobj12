<?php

namespace App\Security;

use App\Entity\Security\ApiKey;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * User representation para API Keys
 */
class ApiKeyUser implements UserInterface
{
    private $apiKey;

    public function __construct(ApiKey $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getRoles(): array
    {
        // Retorna roles baseadas na API key
        return $this->apiKey->getRoles() ?? ['ROLE_API'];
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getUsername(): string
    {
        return $this->apiKey->getName();
    }

    public function eraseCredentials(): void
    {
        // Não há credenciais para apagar
    }

    public function getApiKey(): ApiKey
    {
        return $this->apiKey;
    }
}

