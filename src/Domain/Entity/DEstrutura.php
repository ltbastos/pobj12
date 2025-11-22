<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="d_estrutura")
 */
class DEstrutura
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=20)
     */
    private $funcional;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nome;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $cargo;

    /**
     * @ORM\Column(name="id_cargo", type="integer", nullable=true)
     */
    private $idCargo;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $agencia;

    /**
     * @ORM\Column(name="id_agencia", type="integer", nullable=true)
     */
    private $idAgencia;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $porte;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $regional;

    /**
     * @ORM\Column(name="id_regional", type="integer", nullable=true)
     */
    private $idRegional;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $diretoria;

    /**
     * @ORM\Column(name="id_diretoria", type="integer", nullable=true)
     */
    private $idDiretoria;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $segmento;

    /**
     * @ORM\Column(name="id_segmento", type="integer", nullable=true)
     */
    private $idSegmento;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $rede;

    /**
     * @ORM\Column(name="created_at", type="datetime", options={"default"="CURRENT_TIMESTAMP"})
     */
    private $createdAt;

    public function getFuncional()
    {
        return $this->funcional;
    }

    public function setFuncional($funcional)
    {
        $this->funcional = $funcional;
        return $this;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
        return $this;
    }

    public function getCargo()
    {
        return $this->cargo;
    }

    public function setCargo($cargo)
    {
        $this->cargo = $cargo;
        return $this;
    }

    public function getIdCargo()
    {
        return $this->idCargo;
    }

    public function setIdCargo($idCargo)
    {
        $this->idCargo = $idCargo;
        return $this;
    }

    public function getAgencia()
    {
        return $this->agencia;
    }

    public function setAgencia($agencia)
    {
        $this->agencia = $agencia;
        return $this;
    }

    public function getIdAgencia()
    {
        return $this->idAgencia;
    }

    public function setIdAgencia($idAgencia)
    {
        $this->idAgencia = $idAgencia;
        return $this;
    }

    public function getPorte()
    {
        return $this->porte;
    }

    public function setPorte($porte)
    {
        $this->porte = $porte;
        return $this;
    }

    public function getRegional()
    {
        return $this->regional;
    }

    public function setRegional($regional)
    {
        $this->regional = $regional;
        return $this;
    }

    public function getIdRegional()
    {
        return $this->idRegional;
    }

    public function setIdRegional($idRegional)
    {
        $this->idRegional = $idRegional;
        return $this;
    }

    public function getDiretoria()
    {
        return $this->diretoria;
    }

    public function setDiretoria($diretoria)
    {
        $this->diretoria = $diretoria;
        return $this;
    }

    public function getIdDiretoria()
    {
        return $this->idDiretoria;
    }

    public function setIdDiretoria($idDiretoria)
    {
        $this->idDiretoria = $idDiretoria;
        return $this;
    }

    public function getSegmento()
    {
        return $this->segmento;
    }

    public function setSegmento($segmento)
    {
        $this->segmento = $segmento;
        return $this;
    }

    public function getIdSegmento()
    {
        return $this->idSegmento;
    }

    public function setIdSegmento($idSegmento)
    {
        $this->idSegmento = $idSegmento;
        return $this;
    }

    public function getRede()
    {
        return $this->rede;
    }

    public function setRede($rede)
    {
        $this->rede = $rede;
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }
}

