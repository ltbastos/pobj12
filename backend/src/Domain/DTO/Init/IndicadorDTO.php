<?php

namespace App\Domain\DTO\Init;

class IndicadorDTO
{
    public $id;
    public $nome;
    public $familia_id;

    public function __construct(int $id, string $nome, ?int $familia_id = null)
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->familia_id = $familia_id;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'familia_id' => $this->familia_id,
        ];
    }
}





