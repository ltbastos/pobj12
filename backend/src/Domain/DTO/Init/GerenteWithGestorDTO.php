<?php

namespace App\Domain\DTO\Init;

class GerenteWithGestorDTO
{
    public $id;
    public $funcional;
    public $nome;
    public $id_gestor;

    public function __construct(int $id, string $funcional, string $nome, ?int $id_gestor = null)
    {
        $this->id = $id;
        $this->funcional = $funcional;
        $this->nome = $nome;
        $this->id_gestor = $id_gestor;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'funcional' => $this->funcional,
            'nome' => $this->nome,
            'id_gestor' => $this->id_gestor,
        ];
    }
}





