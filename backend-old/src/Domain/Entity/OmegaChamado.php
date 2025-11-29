<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="omega_chamados")
 */
class OmegaChamado
{
    /**
     * @ORM\Id
     * @ORM\Column(type="string", length=60)
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $subject;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    private $company;

    /**
     * @ORM\Column(name="product_id", type="string", length=80, nullable=true)
     */
    private $productId;

    /**
     * @ORM\Column(name="product_label", type="string", length=150, nullable=true)
     */
    private $productLabel;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $family;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $section;

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     */
    private $queue;

    /**
     * @ORM\Column(type="string", length=120, nullable=true)
     */
    private $category;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $status;

    /**
     * @ORM\Column(type="string", length=40, nullable=true)
     */
    private $priority;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $opened;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @ORM\Column(name="due_date", type="datetime", nullable=true)
     */
    private $dueDate;

    /**
     * @ORM\Column(name="requester_id", type="string", length=60, nullable=true)
     */
    private $requesterId;

    /**
     * @ORM\Column(name="owner_id", type="string", length=60, nullable=true)
     */
    private $ownerId;

    /**
     * @ORM\Column(name="team_id", type="string", length=60, nullable=true)
     */
    private $teamId;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $history;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $diretoria;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $gerencia;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $agencia;

    /**
     * @ORM\Column(name="gerente_gestao", type="string", length=150, nullable=true)
     */
    private $gerenteGestao;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $gerente;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $credit;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $attachment;

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    public function getCompany()
    {
        return $this->company;
    }

    public function setCompany($company)
    {
        $this->company = $company;
        return $this;
    }

    public function getProductId()
    {
        return $this->productId;
    }

    public function setProductId($productId)
    {
        $this->productId = $productId;
        return $this;
    }

    public function getProductLabel()
    {
        return $this->productLabel;
    }

    public function setProductLabel($productLabel)
    {
        $this->productLabel = $productLabel;
        return $this;
    }

    public function getFamily()
    {
        return $this->family;
    }

    public function setFamily($family)
    {
        $this->family = $family;
        return $this;
    }

    public function getSection()
    {
        return $this->section;
    }

    public function setSection($section)
    {
        $this->section = $section;
        return $this;
    }

    public function getQueue()
    {
        return $this->queue;
    }

    public function setQueue($queue)
    {
        $this->queue = $queue;
        return $this;
    }

    public function getCategory()
    {
        return $this->category;
    }

    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function getPriority()
    {
        return $this->priority;
    }

    public function setPriority($priority)
    {
        $this->priority = $priority;
        return $this;
    }

    public function getOpened()
    {
        return $this->opened;
    }

    public function setOpened($opened)
    {
        $this->opened = $opened;
        return $this;
    }

    public function getUpdated()
    {
        return $this->updated;
    }

    public function setUpdated($updated)
    {
        $this->updated = $updated;
        return $this;
    }

    public function getDueDate()
    {
        return $this->dueDate;
    }

    public function setDueDate($dueDate)
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    public function getRequesterId()
    {
        return $this->requesterId;
    }

    public function setRequesterId($requesterId)
    {
        $this->requesterId = $requesterId;
        return $this;
    }

    public function getOwnerId()
    {
        return $this->ownerId;
    }

    public function setOwnerId($ownerId)
    {
        $this->ownerId = $ownerId;
        return $this;
    }

    public function getTeamId()
    {
        return $this->teamId;
    }

    public function setTeamId($teamId)
    {
        $this->teamId = $teamId;
        return $this;
    }

    public function getHistory()
    {
        return $this->history;
    }

    public function setHistory($history)
    {
        $this->history = $history;
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

    public function getGerencia()
    {
        return $this->gerencia;
    }

    public function setGerencia($gerencia)
    {
        $this->gerencia = $gerencia;
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

    public function getGerenteGestao()
    {
        return $this->gerenteGestao;
    }

    public function setGerenteGestao($gerenteGestao)
    {
        $this->gerenteGestao = $gerenteGestao;
        return $this;
    }

    public function getGerente()
    {
        return $this->gerente;
    }

    public function setGerente($gerente)
    {
        $this->gerente = $gerente;
        return $this;
    }

    public function getCredit()
    {
        return $this->credit;
    }

    public function setCredit($credit)
    {
        $this->credit = $credit;
        return $this;
    }

    public function getAttachment()
    {
        return $this->attachment;
    }

    public function setAttachment($attachment)
    {
        $this->attachment = $attachment;
        return $this;
    }
}

