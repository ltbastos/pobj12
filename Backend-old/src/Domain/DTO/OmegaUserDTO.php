<?php

namespace App\Domain\DTO;

class OmegaUserDTO
{
    private $id;
    private $nome;
    private $funcional;
    private $matricula;
    private $cargo;
    private $usuario;
    private $analista;
    private $supervisor;
    private $admin;
    private $encarteiramento;
    private $meta;
    private $orcamento;
    private $pobj;
    private $matriz;
    private $outros;

    public function __construct($id = null, $nome = null, $funcional = null, $matricula = null, $cargo = null, $usuario = true, $analista = false, $supervisor = false, $admin = false, $encarteiramento = false, $meta = false, $orcamento = false, $pobj = false, $matriz = false, $outros = false)
    {
        $this->id = $id;
        $this->nome = $nome;
        $this->funcional = $funcional;
        $this->matricula = $matricula;
        $this->cargo = $cargo;
        $this->usuario = $usuario;
        $this->analista = $analista;
        $this->supervisor = $supervisor;
        $this->admin = $admin;
        $this->encarteiramento = $encarteiramento;
        $this->meta = $meta;
        $this->orcamento = $orcamento;
        $this->pobj = $pobj;
        $this->matriz = $matriz;
        $this->outros = $outros;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'funcional' => $this->funcional,
            'matricula' => $this->matricula,
            'cargo' => $this->cargo,
            'usuario' => $this->usuario ? 1 : 0,
            'analista' => $this->analista ? 1 : 0,
            'supervisor' => $this->supervisor ? 1 : 0,
            'admin' => $this->admin ? 1 : 0,
            'encarteiramento' => $this->encarteiramento ? 1 : 0,
            'meta' => $this->meta ? 1 : 0,
            'orcamento' => $this->orcamento ? 1 : 0,
            'pobj' => $this->pobj ? 1 : 0,
            'matriz' => $this->matriz ? 1 : 0,
            'outros' => $this->outros ? 1 : 0,
        ];
    }
}

