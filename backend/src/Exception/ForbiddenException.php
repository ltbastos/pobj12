<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exceção para acesso negado (sem permissão)
 */
class ForbiddenException extends AppException
{
    public function __construct(
        string $message = 'Acesso negado',
        \Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            Response::HTTP_FORBIDDEN,
            'FORBIDDEN',
            [],
            $previous
        );
    }
}

