<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exceção para erros de banco de dados
 */
class DatabaseException extends AppException
{
    public function __construct(
        string $message = 'Erro ao acessar o banco de dados',
        \Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            Response::HTTP_INTERNAL_SERVER_ERROR,
            'DATABASE_ERROR',
            [],
            $previous
        );
    }
}

