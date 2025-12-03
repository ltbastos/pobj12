<?php

namespace App\Domain\DTO\Init;

class AgenciaDTO
{
    public $id;
    public $nome;
    public $regional_id;

    public function __construct(int $id, string $nome, ?int $regional_id = null)
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->regional_id = $regional_id;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'regional_id' => $this->regional_id,
        ];
    }
}





