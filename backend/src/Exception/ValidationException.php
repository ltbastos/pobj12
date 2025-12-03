<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exceção para erros de validação
 * Usada quando dados de entrada são inválidos
 */
class ValidationException extends AppException
{
    public function __construct(
        string $message = 'Dados de entrada inválidos',
        array $validationErrors = [],
        \Throwable $previous = null
    ) {
        parent::__construct(
            $message,
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'VALIDATION_ERROR',
            ['validation_errors' => $validationErrors],
            $previous
        );
    }

    public function getValidationErrors(): array
    {
        return $this->details['validation_errors'] ?? [];
    }
}

