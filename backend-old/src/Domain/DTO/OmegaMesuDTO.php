<?php

namespace App\Domain\DTO;

class OmegaMesuDTO
{
    private $segmento;
    private $segmentoId;
    private $diretoria;
    private $diretoriaId;
    private $gerenciaRegional;
    private $gerenciaRegionalId;
    private $agencia;
    private $agenciaId;
    private $gerenteGestao;
    private $gerenteGestaoId;
    private $gerente;
    private $gerenteId;

    public function __construct($segmento = null, $segmentoId = null, $diretoria = null, $diretoriaId = null, $gerenciaRegional = null, $gerenciaRegionalId = null, $agencia = null, $agenciaId = null, $gerenteGestao = null, $gerenteGestaoId = null, $gerente = null, $gerenteId = null)
    {
        $this->segmento = $segmento;
        $this->segmentoId = $segmentoId;
        $this->diretoria = $diretoria;
        $this->diretoriaId = $diretoriaId;
        $this->gerenciaRegional = $gerenciaRegional;
        $this->gerenciaRegionalId = $gerenciaRegionalId;
        $this->agencia = $agencia;
        $this->agenciaId = $agenciaId;
        $this->gerenteGestao = $gerenteGestao;
        $this->gerenteGestaoId = $gerenteGestaoId;
        $this->gerente = $gerente;
        $this->gerenteId = $gerenteId;
    }

    public function toArray()
    {
        return [
            'segmento' => $this->segmento,
            'segmento_id' => $this->segmentoId,
            'Segmento' => $this->segmento,
            'Id Segmento' => $this->segmentoId,
            'ID Segmento' => $this->segmentoId,
            'diretoria' => $this->diretoria,
            'diretoria_id' => $this->diretoriaId,
            'Diretoria Regional' => $this->diretoria,
            'Diretoria' => $this->diretoria,
            'Id Diretoria' => $this->diretoriaId,
            'ID Diretoria' => $this->diretoriaId,
            'gerencia_regional' => $this->gerenciaRegional,
            'gerencia_regional_id' => $this->gerenciaRegionalId,
            'Gerencia Regional' => $this->gerenciaRegional,
            'Gerência Regional' => $this->gerenciaRegional,
            'Id Gerencia Regional' => $this->gerenciaRegionalId,
            'ID Gerencia Regional' => $this->gerenciaRegionalId,
            'agencia' => $this->agencia,
            'agencia_id' => $this->agenciaId,
            'Agencia' => $this->agencia,
            'Agência' => $this->agencia,
            'Id Agencia' => $this->agenciaId,
            'ID Agencia' => $this->agenciaId,
            'Id Agência' => $this->agenciaId,
            'gerente_gestao' => $this->gerenteGestao,
            'gerente_gestao_id' => $this->gerenteGestaoId,
            'Gerente de Gestao' => $this->gerenteGestao,
            'Gerente de Gestão' => $this->gerenteGestao,
            'Id Gerente de Gestao' => $this->gerenteGestaoId,
            'Id Gerente de Gestão' => $this->gerenteGestaoId,
            'gerente' => $this->gerente,
            'gerente_id' => $this->gerenteId,
            'Gerente' => $this->gerente,
            'Id Gerente' => $this->gerenteId,
        ];
    }
}

