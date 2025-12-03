<?php

namespace App\Domain\DTO\Init;

class SubindicadorDTO
{
    public $id;
    public $nome;
    public $indicador_id;

    public function __construct(int $id, string $nome, ?int $indicador_id = null)
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->indicador_id = $indicador_id;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'indicador_id' => $this->indicador_id,
        ];
    }
}





