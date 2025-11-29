<?php

namespace App\Domain\DTO;

class MetaDTO extends BaseFactDTO
{
    private $registroId;
    private $segmento;
    private $segmentoId;
    private $diretoriaId;
    private $diretoriaNome;
    private $gerenciaId;
    private $gerenciaNome;
    private $regionalNome;
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
    private $subindicadorCodigo;
    private $familiaCodigo;
    private $indicadorCodigo;
    private $metaMensal;

    public function __construct(
        $registroId = null,
        $segmento = null,
        $segmentoId = null,
        $diretoriaId = null,
        $diretoriaNome = null,
        $gerenciaId = null,
        $gerenciaNome = null,
        $regionalNome = null,
        $agenciaId = null,
        $agenciaNome = null,
        $gerenteGestaoId = null,
        $gerenteGestaoNome = null,
        $gerenteId = null,
        $gerenteNome = null,
        $familiaId = null,
        $familiaNome = null,
        $idIndicador = null,
        $dsIndicador = null,
        $subproduto = null,
        $subindicadorCodigo = null,
        $familiaCodigo = null,
        $indicadorCodigo = null,
        $metaMensal = null
    )
    {
        $this->registroId = $registroId;
        $this->segmento = $segmento;
        $this->segmentoId = $segmentoId;
        $this->diretoriaId = $diretoriaId;
        $this->diretoriaNome = $diretoriaNome;
        $this->gerenciaId = $gerenciaId;
        $this->gerenciaNome = $gerenciaNome;
        $this->regionalNome = $regionalNome;
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
        $this->subindicadorCodigo = $subindicadorCodigo;
        $this->familiaCodigo = $familiaCodigo;
        $this->indicadorCodigo = $indicadorCodigo;
        $this->metaMensal = $metaMensal;
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
            'regional_nome' => $this->regionalNome,
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
            'subindicador_codigo' => $this->subindicadorCodigo,
            'familia_codigo' => $this->familiaCodigo,
            'indicador_codigo' => $this->indicadorCodigo,
            'meta_mensal' => $this->metaMensal,
        ];
    }
}

