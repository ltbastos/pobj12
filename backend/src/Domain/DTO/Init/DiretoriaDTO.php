<?php

namespace App\Domain\DTO\Init;

class DiretoriaDTO
{
    public $id;
    public $nome;
    public $segmento_id;

    public function __construct(int $id, string $nome, ?int $segmento_id = null)
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->segmento_id = $segmento_id;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'segmento_id' => $this->segmento_id,
        ];
    }
}





