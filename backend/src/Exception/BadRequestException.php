<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exceção para requisições malformadas
 */
class BadRequestException extends AppException
{
    public function __construct(
        string $message = 'Requisição inválida',
        array $details = [],
        \Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            Response::HTTP_BAD_REQUEST,
            'BAD_REQUEST',
            $details,
            $previous
        );
    }
}

