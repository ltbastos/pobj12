<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exceção para erros de autenticação/autorização
 */
class UnauthorizedException extends AppException
{
    public function __construct(
        string $message = 'Não autorizado',
        \Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            Response::HTTP_UNAUTHORIZED,
            'UNAUTHORIZED',
            [],
            $previous
        );
    }
}

