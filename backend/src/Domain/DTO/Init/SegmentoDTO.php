<?php

namespace App\Domain\DTO\Init;

class SegmentoDTO
{
    public $id;
    public $nome;

    public function __construct(int $id, string $nome)
    {
        $this->id = $id;
        $this->nome = $nome;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
        ];
    }
}





