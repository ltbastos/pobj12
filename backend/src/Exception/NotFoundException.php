<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exceção para recursos não encontrados
 */
class NotFoundException extends AppException
{
    public function __construct(
        string $message = 'Recurso não encontrado',
        string $resource = null,
        \Throwable $previous = null
    ) {
        $details = [];
        if ($resource) {
            $details['resource'] = $resource;
        }

        parent::__construct(
            $message,
            Response::HTTP_NOT_FOUND,
            'NOT_FOUND',
            $details,
            $previous
        );
    }
}

