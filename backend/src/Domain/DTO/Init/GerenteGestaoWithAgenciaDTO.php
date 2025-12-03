<?php

namespace App\Domain\DTO\Init;

class GerenteGestaoWithAgenciaDTO
{
    public $id;
    public $funcional;
    public $nome;
    public $agencia_id;

    public function __construct(int $id, string $funcional, string $nome, ?int $agencia_id = null)
    {
        $this->id = $id;
        $this->funcional = $funcional;
        $this->nome = $nome;
        $this->agencia_id = $agencia_id;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'funcional' => $this->funcional,
            'nome' => $this->nome,
            'agencia_id' => $this->agencia_id,
        ];
    }
}





