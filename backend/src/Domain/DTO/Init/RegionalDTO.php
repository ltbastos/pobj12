<?php

namespace App\Domain\DTO\Init;

class RegionalDTO
{
    public $id;
    public $nome;
    public $diretoria_id;

    public function __construct(int $id, string $nome, ?int $diretoria_id = null)
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->diretoria_id = $diretoria_id;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'diretoria_id' => $this->diretoria_id,
        ];
    }
}





