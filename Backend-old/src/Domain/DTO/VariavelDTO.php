<?php

namespace App\Domain\DTO;

class VariavelDTO extends BaseFactDTO
{
    private $registroId;
    private $funcional;
    private $idIndicador;
    private $dsIndicador;
    private $familiaId;
    private $familiaNome;
    private $familiaCodigo;
    private $indicadorCodigo;
    private $subindicadorCodigo;
    private $data;
    private $competencia;
    private $variavelReal;
    private $variavelMeta;
    private $nomeFuncional;
    private $segmento;
    private $segmentoId;
    private $diretoriaNome;
    private $diretoriaId;
    private $regionalNome;
    private $gerenciaId;
    private $agenciaNome;
    private $agenciaId;

    public function __construct($registroId = null, $funcional = null, $idIndicador = null, $dsIndicador = null, $familiaId = null, $familiaNome = null, $familiaCodigo = null, $indicadorCodigo = null, $subindicadorCodigo = null, $data = null, $competencia = null, $variavelReal = null, $variavelMeta = null, $nomeFuncional = null, $segmento = null, $segmentoId = null, $diretoriaNome = null, $diretoriaId = null, $regionalNome = null, $gerenciaId = null, $agenciaNome = null, $agenciaId = null)
    {
        $this->registroId = $registroId;
        $this->funcional = $funcional;
        $this->idIndicador = $idIndicador;
        $this->dsIndicador = $dsIndicador;
        $this->familiaId = $familiaId;
        $this->familiaNome = $familiaNome;
        $this->familiaCodigo = $familiaCodigo;
        $this->indicadorCodigo = $indicadorCodigo;
        $this->subindicadorCodigo = $subindicadorCodigo;
        $this->data = $data;
        $this->competencia = $competencia;
        $this->variavelReal = $variavelReal;
        $this->variavelMeta = $variavelMeta;
        $this->nomeFuncional = $nomeFuncional;
        $this->segmento = $segmento;
        $this->segmentoId = $segmentoId;
        $this->diretoriaNome = $diretoriaNome;
        $this->diretoriaId = $diretoriaId;
        $this->regionalNome = $regionalNome;
        $this->gerenciaId = $gerenciaId;
        $this->agenciaNome = $agenciaNome;
        $this->agenciaId = $agenciaId;
    }

    public function toArray()
    {
        return [
            'registro_id' => $this->registroId,
            'funcional' => $this->funcional,
            'id_indicador' => $this->idIndicador,
            'ds_indicador' => $this->dsIndicador,
            'familia_id' => $this->familiaId,
            'familia_nome' => $this->familiaNome,
            'familia_codigo' => $this->familiaCodigo,
            'indicador_codigo' => $this->indicadorCodigo,
            'subindicador_codigo' => $this->subindicadorCodigo,
            'data' => $this->data,
            'competencia' => $this->competencia,
            'variavel_real' => $this->variavelReal,
            'variavel_meta' => $this->variavelMeta,
            'nome_funcional' => $this->nomeFuncional,
            'segmento' => $this->segmento,
            'segmento_id' => $this->segmentoId,
            'diretoria_nome' => $this->diretoriaNome,
            'diretoria_id' => $this->diretoriaId,
            'regional_nome' => $this->regionalNome,
            'gerencia_id' => $this->gerenciaId,
            'agencia_nome' => $this->agenciaNome,
            'agencia_id' => $this->agenciaId,
        ];
    }
}

