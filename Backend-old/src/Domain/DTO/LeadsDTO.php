<?php

namespace App\Domain\DTO;

class LeadsDTO extends BaseFactDTO
{
    private $registroId;
    private $data;
    private $competencia;
    private $nomeEmpresa;
    private $cnae;
    private $segmento;
    private $segmentoId;
    private $produtoPropenso;
    private $familiaNome;
    private $subproduto;
    private $idIndicador;
    private $subindicadorCodigo;
    private $dataContato;
    private $comentario;
    private $responsavelContato;
    private $diretoriaNome;
    private $diretoriaId;
    private $regionalNome;
    private $gerenciaId;
    private $agenciaNome;
    private $agenciaId;
    private $gerenteGestaoNome;
    private $gerenteGestaoId;
    private $gerenteNome;
    private $gerenteId;
    private $creditoPreAprovado;
    private $origemLead;

    public function __construct($registroId = null, $data = null, $competencia = null, $nomeEmpresa = null, $cnae = null, $segmento = null, $segmentoId = null, $produtoPropenso = null, $familiaNome = null, $subproduto = null, $idIndicador = null, $subindicadorCodigo = null, $dataContato = null, $comentario = null, $responsavelContato = null, $diretoriaNome = null, $diretoriaId = null, $regionalNome = null, $gerenciaId = null, $agenciaNome = null, $agenciaId = null, $gerenteGestaoNome = null, $gerenteGestaoId = null, $gerenteNome = null, $gerenteId = null, $creditoPreAprovado = null, $origemLead = null)
    {
        $this->registroId = $registroId;
        $this->data = $data;
        $this->competencia = $competencia;
        $this->nomeEmpresa = $nomeEmpresa;
        $this->cnae = $cnae;
        $this->segmento = $segmento;
        $this->segmentoId = $segmentoId;
        $this->produtoPropenso = $produtoPropenso;
        $this->familiaNome = $familiaNome;
        $this->subproduto = $subproduto;
        $this->idIndicador = $idIndicador;
        $this->subindicadorCodigo = $subindicadorCodigo;
        $this->dataContato = $dataContato;
        $this->comentario = $comentario;
        $this->responsavelContato = $responsavelContato;
        $this->diretoriaNome = $diretoriaNome;
        $this->diretoriaId = $diretoriaId;
        $this->regionalNome = $regionalNome;
        $this->gerenciaId = $gerenciaId;
        $this->agenciaNome = $agenciaNome;
        $this->agenciaId = $agenciaId;
        $this->gerenteGestaoNome = $gerenteGestaoNome;
        $this->gerenteGestaoId = $gerenteGestaoId;
        $this->gerenteNome = $gerenteNome;
        $this->gerenteId = $gerenteId;
        $this->creditoPreAprovado = $creditoPreAprovado;
        $this->origemLead = $origemLead;
    }

    public function toArray()
    {
        return [
            'registro_id' => $this->registroId,
            'data' => $this->data,
            'competencia' => $this->competencia,
            'nome_empresa' => $this->nomeEmpresa,
            'cnae' => $this->cnae,
            'segmento' => $this->segmento,
            'segmento_id' => $this->segmentoId,
            'produto_propenso' => $this->produtoPropenso,
            'familia_nome' => $this->familiaNome,
            'subproduto' => $this->subproduto,
            'id_indicador' => $this->idIndicador,
            'subindicador_codigo' => $this->subindicadorCodigo,
            'data_contato' => $this->dataContato,
            'comentario' => $this->comentario,
            'responsavel_contato' => $this->responsavelContato,
            'diretoria_nome' => $this->diretoriaNome,
            'diretoria_id' => $this->diretoriaId,
            'regional_nome' => $this->regionalNome,
            'gerencia_id' => $this->gerenciaId,
            'agencia_nome' => $this->agenciaNome,
            'agencia_id' => $this->agenciaId,
            'gerente_gestao_nome' => $this->gerenteGestaoNome,
            'gerente_gestao_id' => $this->gerenteGestaoId,
            'gerente_nome' => $this->gerenteNome,
            'gerente_id' => $this->gerenteId,
            'credito_pre_aprovado' => $this->creditoPreAprovado,
            'origem_lead' => $this->origemLead,
        ];
    }
}

