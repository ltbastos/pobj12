<?php

namespace App\Entity\Pobj;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="d_estrutura")
 */
class DEstrutura
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="bigint")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20, unique=true)
     */
    private $funcional;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nome;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pobj\Cargo")
     * @ORM\JoinColumn(name="cargo_id", referencedColumnName="id", nullable=false)
     */
    private $cargo;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pobj\Segmento")
     * @ORM\JoinColumn(name="segmento_id", referencedColumnName="id", nullable=true)
     */
    private $segmento;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pobj\Diretoria")
     * @ORM\JoinColumn(name="diretoria_id", referencedColumnName="id", nullable=true)
     */
    private $diretoria;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pobj\Regional")
     * @ORM\JoinColumn(name="regional_id", referencedColumnName="id", nullable=true)
     */
    private $regional;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pobj\Agencia")
     * @ORM\JoinColumn(name="agencia_id", referencedColumnName="id", nullable=true)
     */
    private $agencia;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Pobj\Grupo")
     * @ORM\JoinColumn(name="grupo_id", referencedColumnName="id", nullable=true)
     */
    private $grupo;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFuncional(): ?string
    {
        return $this->funcional;
    }

    public function setFuncional(string $funcional): self
    {
        $this->funcional = $funcional;
        return $this;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): self
    {
        $this->nome = $nome;
        return $this;
    }

    public function getCargo(): ?Cargo
    {
        return $this->cargo;
    }

    public function setCargo(?Cargo $cargo): self
    {
        $this->cargo = $cargo;
        return $this;
    }

    public function getSegmento(): ?Segmento
    {
        return $this->segmento;
    }

    public function setSegmento(?Segmento $segmento): self
    {
        $this->segmento = $segmento;
        return $this;
    }

    public function getDiretoria(): ?Diretoria
    {
        return $this->diretoria;
    }

    public function setDiretoria(?Diretoria $diretoria): self
    {
        $this->diretoria = $diretoria;
        return $this;
    }

    public function getRegional(): ?Regional
    {
        return $this->regional;
    }

    public function setRegional(?Regional $regional): self
    {
        $this->regional = $regional;
        return $this;
    }

    public function getAgencia(): ?Agencia
    {
        return $this->agencia;
    }

    public function setAgencia(?Agencia $agencia): self
    {
        $this->agencia = $agencia;
        return $this;
    }

    public function getGrupo(): ?Grupo
    {
        return $this->grupo;
    }

    public function setGrupo(?Grupo $grupo): self
    {
        $this->grupo = $grupo;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }
}

