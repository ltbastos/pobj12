<?php

namespace App\Domain\DTO;

class OmegaStatusDTO
{
    private $id;
    private $label;
    private $tone;
    private $descricao;
    private $ordem;
    private $departamentoId;

    public function __construct($id = null, $label = null, $tone = 'neutral', $descricao = null, $ordem = null, $departamentoId = null)
    {
        $this->id = $id;
        $this->label = $label;
        $this->tone = $tone;
        $this->descricao = $descricao;
        $this->ordem = $ordem;
        $this->departamentoId = $departamentoId;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'tone' => $this->tone,
            'descricao' => $this->descricao,
            'ordem' => $this->ordem,
            'departamento_id' => $this->departamentoId,
        ];
    }
}

