<?php

namespace App\Domain\DTO;

class OmegaTicketDTO
{
    private $id;
    private $subject;
    private $company;
    private $productId;
    private $productLabel;
    private $family;
    private $section;
    private $queue;
    private $category;
    private $status;
    private $priority;
    private $opened;
    private $updated;
    private $dueDate;
    private $requesterId;
    private $ownerId;
    private $teamId;
    private $history;
    private $diretoria;
    private $gerencia;
    private $agencia;
    private $gerenteGestao;
    private $gerente;
    private $credit;
    private $attachment;

    public function __construct($id = null, $subject = null, $company = null, $productId = null, $productLabel = null, $family = null, $section = null, $queue = null, $category = null, $status = null, $priority = null, $opened = null, $updated = null, $dueDate = null, $requesterId = null, $ownerId = null, $teamId = null, $history = null, $diretoria = null, $gerencia = null, $agencia = null, $gerenteGestao = null, $gerente = null, $credit = null, $attachment = null)
    {
        $this->id = $id;
        $this->subject = $subject;
        $this->company = $company;
        $this->productId = $productId;
        $this->productLabel = $productLabel;
        $this->family = $family;
        $this->section = $section;
        $this->queue = $queue;
        $this->category = $category;
        $this->status = $status;
        $this->priority = $priority;
        $this->opened = $opened;
        $this->updated = $updated;
        $this->dueDate = $dueDate;
        $this->requesterId = $requesterId;
        $this->ownerId = $ownerId;
        $this->teamId = $teamId;
        $this->history = $history;
        $this->diretoria = $diretoria;
        $this->gerencia = $gerencia;
        $this->agencia = $agencia;
        $this->gerenteGestao = $gerenteGestao;
        $this->gerente = $gerente;
        $this->credit = $credit;
        $this->attachment = $attachment;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'subject' => $this->subject,
            'company' => $this->company,
            'product_id' => $this->productId,
            'product_label' => $this->productLabel,
            'family' => $this->family,
            'section' => $this->section,
            'queue' => $this->queue,
            'category' => $this->category,
            'status' => $this->status,
            'priority' => $this->priority,
            'opened' => $this->opened,
            'updated' => $this->updated,
            'due_date' => $this->dueDate,
            'requester_id' => $this->requesterId,
            'owner_id' => $this->ownerId,
            'team_id' => $this->teamId,
            'history' => $this->history,
            'diretoria' => $this->diretoria,
            'gerencia' => $this->gerencia,
            'agencia' => $this->agencia,
            'gerente_gestao' => $this->gerenteGestao,
            'gerente' => $this->gerente,
            'credit' => $this->credit,
            'attachment' => $this->attachment,
        ];
    }
}

