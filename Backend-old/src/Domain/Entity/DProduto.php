<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="d_produtos")
 */
class DProduto
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="id_familia", type="integer")
     */
    private $idFamilia;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $familia;

    /**
     * @ORM\Column(name="id_indicador", type="integer")
     */
    private $idIndicador;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $indicador;

    /**
     * @ORM\Column(name="id_subindicador", type="integer", nullable=true)
     */
    private $idSubindicador;

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     */
    private $subindicador;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, options={"default"="0.00"})
     */
    private $peso;

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     */
    private $metrica;   

    public function getId()
    {
        return $this->id;
    }

    public function getIdFamilia()
    {
        return $this->idFamilia;
    }

    public function setIdFamilia($idFamilia)
    {
        $this->idFamilia = $idFamilia;
        return $this;
    }

    public function getFamilia()
    {
        return $this->familia;
    }

    public function setFamilia($familia)
    {
        $this->familia = $familia;
        return $this;
    }

    public function getIdIndicador()
    {
        return $this->idIndicador;
    }

    public function setIdIndicador($idIndicador)
    {
        $this->idIndicador = $idIndicador;
        return $this;
    }

    public function getIndicador()
    {
        return $this->indicador;
    }

    public function setIndicador($indicador)
    {
        $this->indicador = $indicador;
        return $this;
    }

    public function getIdSubindicador()
    {
        return $this->idSubindicador;
    }

    public function setIdSubindicador($idSubindicador)
    {
        $this->idSubindicador = $idSubindicador;
        return $this;
    }

    public function getSubindicador()
    {
        return $this->subindicador;
    }

    public function setSubindicador($subindicador)
    {
        $this->subindicador = $subindicador;
        return $this;
    }

    public function getPeso()
    {
        return $this->peso;
    }

    public function setPeso($peso)
    {
        $this->peso = $peso;
        return $this;
    }

    public function getMetrica()
    {
        return $this->metrica;
    }

    public function setMetrica($metrica)
    {
        $this->metrica = $metrica;
        return $this;
    }

    public static function fromArray(array $data): self
    {
        $entity = new self();
        $entity->id = isset($data['id']) ? (int)$data['id'] : null;
        $entity->idFamilia = isset($data['id_familia']) ? (int)$data['id_familia'] : null;
        $entity->familia = $data['familia'] ?? null;
        $entity->idIndicador = isset($data['id_indicador']) ? (int)$data['id_indicador'] : null;
        $entity->indicador = $data['indicador'] ?? null;
        $entity->idSubindicador = isset($data['id_subindicador']) ? (int)$data['id_subindicador'] : null;
        $entity->subindicador = $data['subindicador'] ?? null;
        $entity->peso = isset($data['peso']) ? (float)$data['peso'] : null;
        $entity->metrica = $data['metrica'] ?? null;
        return $entity;
    }

    public function toDTO(): \App\Domain\DTO\ProdutoDTO
    {
        return \App\Domain\DTO\ProdutoDTO::fromEntity($this);
    }
}
