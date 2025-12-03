<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exceção base da aplicação
 * Todas as exceções customizadas devem estender esta classe
 */
abstract class AppException extends \RuntimeException
{
    protected $statusCode;
    protected $errorCode;
    protected $details;

    public function __construct(
        string $message = '',
        int $statusCode = Response::HTTP_INTERNAL_SERVER_ERROR,
        string $errorCode = 'INTERNAL_ERROR',
        array $details = [],
        \Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
        $this->details = $details;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function toArray(): array
    {
        return [
            'error' => $this->getMessage(),
            'code' => $this->errorCode,
            'details' => $this->details,
        ];
    }
}

