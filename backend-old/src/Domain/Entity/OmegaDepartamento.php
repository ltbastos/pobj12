<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="omega_departamentos")
 */
class OmegaDepartamento
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=120)
     */
    private $departamento;

    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=120)
     */
    private $tipo;

    /**
     * @ORM\Column(name="departamento_id", type="string", length=30, unique=true)
     */
    private $departamentoId;

    /**
     * @ORM\Column(name="ordem_departamento", type="integer", nullable=true)
     */
    private $ordemDepartamento;

    /**
     * @ORM\Column(name="ordem_tipo", type="integer", nullable=true)
     */
    private $ordemTipo;

    public function getDepartamento()
    {
        return $this->departamento;
    }

    public function setDepartamento($departamento)
    {
        $this->departamento = $departamento;
        return $this;
    }

    public function getTipo()
    {
        return $this->tipo;
    }

    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
        return $this;
    }

    public function getDepartamentoId()
    {
        return $this->departamentoId;
    }

    public function setDepartamentoId($departamentoId)
    {
        $this->departamentoId = $departamentoId;
        return $this;
    }

    public function getOrdemDepartamento()
    {
        return $this->ordemDepartamento;
    }

    public function setOrdemDepartamento($ordemDepartamento)
    {
        $this->ordemDepartamento = $ordemDepartamento;
        return $this;
    }

    public function getOrdemTipo()
    {
        return $this->ordemTipo;
    }

    public function setOrdemTipo($ordemTipo)
    {
        $this->ordemTipo = $ordemTipo;
        return $this;
    }
}

