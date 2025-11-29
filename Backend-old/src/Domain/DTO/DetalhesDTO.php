<?php

namespace App\Domain\DTO;

class DetalhesDTO extends BaseFactDTO
{
    private $registroId;
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
    private $familiaId;
    private $familiaNome;
    private $idIndicador;
    private $dsIndicador;
    private $subproduto;
    private $idSubindicador;
    private $subindicadorCodigo;
    private $familiaCodigo;
    private $indicadorCodigo;
    private $carteira;
    private $canalVenda;
    private $tipoVenda;
    private $modalidadePagamento;
    private $data;
    private $competencia;
    private $metaMensal;
    private $realizadoMensal;
    private $quantidade;
    private $peso;
    private $pontos;
    private $statusId;

    public function __construct($registroId = null, $segmento = null, $segmentoId = null, $diretoriaId = null, $diretoriaNome = null, $gerenciaId = null, $gerenciaNome = null, $agenciaId = null, $agenciaNome = null, $gerenteGestaoId = null, $gerenteGestaoNome = null, $gerenteId = null, $gerenteNome = null, $familiaId = null, $familiaNome = null, $idIndicador = null, $dsIndicador = null, $subproduto = null, $idSubindicador = null, $subindicador = null, $subindicadorCodigo = null, $familiaCodigo = null, $indicadorCodigo = null, $carteira = null, $canalVenda = null, $tipoVenda = null, $modalidadePagamento = null, $data = null, $competencia = null, $metaMensal = null, $realizadoMensal = null, $quantidade = null, $peso = null, $pontos = null, $statusId = null)
    {
        $this->registroId = $registroId;
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
        $this->familiaId = $familiaId;
        $this->familiaNome = $familiaNome;
        $this->idIndicador = $idIndicador;
        $this->dsIndicador = $dsIndicador;
        $this->subproduto = $subproduto;
        $this->idSubindicador = $idSubindicador;
        $this->subindicador = $subindicador;
        $this->subindicadorCodigo = $subindicadorCodigo;
        $this->familiaCodigo = $familiaCodigo;
        $this->indicadorCodigo = $indicadorCodigo;
        $this->carteira = $carteira;
        $this->canalVenda = $canalVenda;
        $this->tipoVenda = $tipoVenda;
        $this->modalidadePagamento = $modalidadePagamento;
        $this->data = $data;
        $this->competencia = $competencia;
        $this->metaMensal = $metaMensal;
        $this->realizadoMensal = $realizadoMensal;
        $this->quantidade = $quantidade;
        $this->peso = $peso;
        $this->pontos = $pontos;
        $this->statusId = $statusId;
    }

    public function toArray()
    {
        return [
            'registro_id' => $this->registroId,
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
            'familia_id' => $this->familiaId,
            'familia_nome' => $this->familiaNome,
            'id_indicador' => $this->idIndicador,
            'ds_indicador' => $this->dsIndicador,
            'subproduto' => $this->subproduto,
            'id_subindicador' => $this->idSubindicador,
            'subindicador' => $this->subindicador,
            'subindicador_codigo' => $this->subindicadorCodigo,
            'familia_codigo' => $this->familiaCodigo,
            'indicador_codigo' => $this->indicadorCodigo,
            'carteira' => $this->carteira,
            'canal_venda' => $this->canalVenda,
            'tipo_venda' => $this->tipoVenda,
            'modalidade_pagamento' => $this->modalidadePagamento,
            'data' => $this->data,
            'competencia' => $this->competencia,
            'meta_mensal' => $this->metaMensal,
            'realizado_mensal' => $this->realizadoMensal,
            'quantidade' => $this->quantidade,
            'peso' => $this->peso,
            'pontos' => $this->pontos,
            'status_id' => $this->statusId,
        ];
    }
}

