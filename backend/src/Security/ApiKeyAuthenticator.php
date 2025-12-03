<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * Authenticator para API Keys
 * Valida API keys enviadas no header X-API-Key
 */
class ApiKeyAuthenticator extends AbstractAuthenticator
{
    private $apiKeyRepository;

    public function __construct(ApiKeyRepository $apiKeyRepository)
    {
        $this->apiKeyRepository = $apiKeyRepository;
    }

    public function supports(Request $request): ?bool
    {
        // Suporta apenas se houver header X-API-Key
        return $request->headers->has('X-API-Key');
    }

    public function authenticate(Request $request): Passport
    {
        $apiKey = $request->headers->get('X-API-Key');

        if (null === $apiKey) {
            throw new CustomUserMessageAuthenticationException('API Key não fornecida');
        }

        // Valida a API key
        $apiKeyEntity = $this->apiKeyRepository->findValidKey($apiKey);

        if (!$apiKeyEntity) {
            throw new CustomUserMessageAuthenticationException('API Key inválida ou expirada');
        }

        // Atualiza último uso
        $this->apiKeyRepository->updateLastUsed($apiKeyEntity);

        return new SelfValidatingPassport(
            new UserBadge($apiKey, function ($apiKey) use ($apiKeyEntity) {
                return new ApiKeyUser($apiKeyEntity);
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?JsonResponse
    {
        // Autenticação bem-sucedida, continua a requisição
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): JsonResponse
    {
        return new JsonResponse([
            'success' => false,
            'data' => [
                'error' => 'Falha na autenticação',
                'code' => 'UNAUTHORIZED',
                'details' => [
                    'message' => $exception->getMessageKey()
                ],
                'timestamp' => date('c')
            ]
        ], 401);
    }
}

