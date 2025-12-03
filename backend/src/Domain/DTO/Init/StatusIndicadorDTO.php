<?php

namespace App\Domain\DTO\Init;

class StatusIndicadorDTO
{
    public $id;
    public $status;

    public function __construct(string $id, string $status)
    {
        $this->id = $id;
        $this->status = $status;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
        ];
    }
}





