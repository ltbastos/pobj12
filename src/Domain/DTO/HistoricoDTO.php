<?php

namespace App\Domain\DTO;

class HistoricoDTO extends BaseFactDTO
{
    private $data;
    private $competencia;
    private $segmento;
    private $segmentoId;
    private $diretoriaId;
    private $diretoriaNome;
    private $gerenciaId;
    private $gerenciaNome;
    private $agenciaId;
    private $agenciaNome;
    private $gerenteGestaoId;
    private $gerenteGestaoNome;
    private $gerenteId;
    private $gerenteNome;
    private $participantes;
    private $rank;
    private $pontos;
    private $realizadoMensal;
    private $metaMensal;

    public function __construct($ano = null, $data = null, $competencia = null, $segmento = null, $segmentoId = null, $diretoriaId = null, $diretoriaNome = null, $gerenciaId = null, $gerenciaNome = null, $agenciaId = null, $agenciaNome = null, $gerenteGestaoId = null, $gerenteGestaoNome = null, $gerenteId = null, $gerenteNome = null, $participantes = null, $rank = null, $pontos = null, $realizadoMensal = null, $metaMensal = null)
    {
        $this->data = $data;
        $this->competencia = $competencia;
        $this->segmento = $segmento;
        $this->segmentoId = $segmentoId;
        $this->diretoriaId = $diretoriaId;
        $this->diretoriaNome = $diretoriaNome;
        $this->gerenciaId = $gerenciaId;
        $this->gerenciaNome = $gerenciaNome;
        $this->agenciaId = $agenciaId;
        $this->agenciaNome = $agenciaNome;
        $this->gerenteGestaoId = $gerenteGestaoId;
        $this->gerenteGestaoNome = $gerenteGestaoNome;
        $this->gerenteId = $gerenteId;
        $this->gerenteNome = $gerenteNome;
        $this->participantes = $participantes;
        $this->rank = $rank;
        $this->pontos = $pontos;
        $this->realizadoMensal = $realizadoMensal;
        $this->metaMensal = $metaMensal;
    }

    public function toArray()
    {
        return [
            'data' => $this->data,
            'competencia' => $this->competencia,
            'segmento' => $this->segmento,
            'segmento_id' => $this->segmentoId,
            'diretoria_id' => $this->diretoriaId,
            'diretoria_nome' => $this->diretoriaNome,
            'gerencia_id' => $this->gerenciaId,
            'gerencia_nome' => $this->gerenciaNome,
            'agencia_id' => $this->agenciaId,
            'agencia_nome' => $this->agenciaNome,
            'gerente_gestao_id' => $this->gerenteGestaoId,
            'gerente_gestao_nome' => $this->gerenteGestaoNome,
            'gerente_id' => $this->gerenteId,
            'gerente_nome' => $this->gerenteNome,
            'participantes' => $this->participantes,
            'rank' => $this->rank,
            'pontos' => $this->pontos,
            'realizado_mensal' => $this->realizadoMensal,
            'meta_mensal' => $this->metaMensal,
        ];
    }
}

