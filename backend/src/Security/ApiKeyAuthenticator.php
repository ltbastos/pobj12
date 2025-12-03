<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Authenticator para API Key única do projeto
 * Valida API key enviada no header X-API-Key contra variável de ambiente
 */
class ApiKeyAuthenticator extends AbstractGuardAuthenticator
{
    private $apiKey;

    public function __construct(string $apiKey = null)
    {
        // Pega da variável de ambiente ou do parâmetro
        $this->apiKey = $apiKey ?? $_ENV['API_KEY'] ?? $_SERVER['API_KEY'] ?? null;
    }

    public function supports(Request $request): bool
    {
        // Não requer autenticação para rotas de documentação
        $path = $request->getPathInfo();
        if (preg_match('#^/api/doc#', $path)) {
            return false;
        }
        
        // Suporta apenas se houver header X-API-Key
        return $request->headers->has('X-API-Key');
    }

    public function getCredentials(Request $request)
    {
        return $request->headers->get('X-API-Key');
    }

    public function getUser($credentials, UserProviderInterface $userProvider): ?UserInterface
    {
        if (null === $credentials) {
            return null;
        }

        // Valida a API key contra a variável de ambiente
        if (!$this->apiKey) {
            throw new CustomUserMessageAuthenticationException('API Key não configurada no servidor');
        }

        if (!hash_equals($this->apiKey, $credentials)) {
            throw new CustomUserMessageAuthenticationException('API Key inválida');
        }

        // Retorna um usuário simples com role ROLE_API
        return new ApiKeyUser();
    }

    public function checkCredentials($credentials, UserInterface $user): bool
    {
        // A validação já foi feita no getUser
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
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

    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new JsonResponse([
            'success' => false,
            'data' => [
                'error' => 'Autenticação necessária',
                'code' => 'UNAUTHORIZED',
                'details' => [
                    'message' => 'API Key não fornecida'
                ],
                'timestamp' => date('c')
            ]
        ], 401);
    }

    public function supportsRememberMe(): bool
    {
        return false;
    }
}
