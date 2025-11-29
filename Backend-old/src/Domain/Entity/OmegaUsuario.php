<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="omega_usuarios")
 */
class OmegaUsuario
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=40)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $nome;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $funcional;

    /**
     * @ORM\Column(type="string", length=20, nullable=true)
     */
    private $matricula;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $cargo;

    /**
     * @ORM\Column(type="boolean", options={"default"=true})
     */
    private $usuario = true;

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     */
    private $analista = false;

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     */
    private $supervisor = false;

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     */
    private $admin = false;

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     */
    private $encarteiramento = false;

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     */
    private $meta = false;

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     */
    private $orcamento = false;

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     */
    private $pobj = false;

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     */
    private $matriz = false;

    /**
     * @ORM\Column(type="boolean", options={"default"=false})
     */
    private $outros = false;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
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

    public function getFuncional()
    {
        return $this->funcional;
    }

    public function setFuncional($funcional)
    {
        $this->funcional = $funcional;
        return $this;
    }

    public function getMatricula()
    {
        return $this->matricula;
    }

    public function setMatricula($matricula)
    {
        $this->matricula = $matricula;
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

    public function isUsuario()
    {
        return $this->usuario;
    }

    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;
        return $this;
    }

    public function isAnalista()
    {
        return $this->analista;
    }

    public function setAnalista($analista)
    {
        $this->analista = $analista;
        return $this;
    }

    public function isSupervisor()
    {
        return $this->supervisor;
    }

    public function setSupervisor($supervisor)
    {
        $this->supervisor = $supervisor;
        return $this;
    }

    public function isAdmin()
    {
        return $this->admin;
    }

    public function setAdmin($admin)
    {
        $this->admin = $admin;
        return $this;
    }

    public function isEncarteiramento()
    {
        return $this->encarteiramento;
    }

    public function setEncarteiramento($encarteiramento)
    {
        $this->encarteiramento = $encarteiramento;
        return $this;
    }

    public function isMeta()
    {
        return $this->meta;
    }

    public function setMeta($meta)
    {
        $this->meta = $meta;
        return $this;
    }

    public function isOrcamento()
    {
        return $this->orcamento;
    }

    public function setOrcamento($orcamento)
    {
        $this->orcamento = $orcamento;
        return $this;
    }

    public function isPobj()
    {
        return $this->pobj;
    }

    public function setPobj($pobj)
    {
        $this->pobj = $pobj;
        return $this;
    }

    public function isMatriz()
    {
        return $this->matriz;
    }

    public function setMatriz($matriz)
    {
        $this->matriz = $matriz;
        return $this;
    }

    public function isOutros()
    {
        return $this->outros;
    }

    public function setOutros($outros)
    {
        $this->outros = $outros;
        return $this;
    }
}

