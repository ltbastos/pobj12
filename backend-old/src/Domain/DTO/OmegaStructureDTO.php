<?php

namespace App\Domain\DTO;

class OmegaStructureDTO
{
    private $departamento;
    private $tipo;
    private $departamentoId;
    private $ordemDepartamento;
    private $ordemTipo;

    public function __construct($departamento = null, $tipo = null, $departamentoId = null, $ordemDepartamento = null, $ordemTipo = null)
    {
        $this->departamento = $departamento;
        $this->tipo = $tipo;
        $this->departamentoId = $departamentoId;
        $this->ordemDepartamento = $ordemDepartamento;
        $this->ordemTipo = $ordemTipo;
    }

    public function toArray()
    {
        return [
            'departamento' => $this->departamento,
            'tipo' => $this->tipo,
            'departamento_id' => $this->departamentoId,
            'ordem_departamento' => $this->ordemDepartamento,
            'ordem_tipo' => $this->ordemTipo,
        ];
    }
}

