<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="omega_status")
 */
class OmegaStatus
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=40)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $label;

    /**
     * @ORM\Column(type="string", length=20, options={"default"="neutral"})
     */
    private $tone = 'neutral';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $descricao;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $ordem;

    /**
     * @ORM\Column(name="departamento_id", type="string", length=20, nullable=true)
     */
    private $departamentoId;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    public function getTone()
    {
        return $this->tone;
    }

    public function setTone($tone)
    {
        $this->tone = $tone;
        return $this;
    }

    public function getDescricao()
    {
        return $this->descricao;
    }

    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
        return $this;
    }

    public function getOrdem()
    {
        return $this->ordem;
    }

    public function setOrdem($ordem)
    {
        $this->ordem = $ordem;
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
}

