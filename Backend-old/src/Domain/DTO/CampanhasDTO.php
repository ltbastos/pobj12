<?php

namespace App\Domain\DTO;

class CampanhasDTO extends BaseFactDTO
{
    private $campanhaId;
    private $sprintId;
    private $segmento;
    private $segmentoId;
    private $diretoriaId;
    private $diretoriaNome;
    private $gerenciaRegionalId;
    private $regionalNome;
    private $agenciaId;
    private $agenciaNome;
    private $gerenteGestaoId;
    private $gerenteGestaoNome;
    private $gerenteId;
    private $gerenteNome;
    private $familiaId;
    private $idIndicador;
    private $dsIndicador;
    private $subproduto;
    private $idSubindicador;
    private $subindicadorCodigo;
    private $familiaCodigo;
    private $indicadorCodigo;
    private $carteira;
    private $data;
    private $competencia;
    private $linhas;
    private $cash;
    private $conquista;
    private $atividade;

    public function __construct($campanhaId = null, $sprintId = null, $segmento = null, $segmentoId = null, $diretoriaId = null, $diretoriaNome = null, $gerenciaRegionalId = null, $regionalNome = null, $agenciaId = null, $agenciaNome = null, $gerenteGestaoId = null, $gerenteGestaoNome = null, $gerenteId = null, $gerenteNome = null, $familiaId = null, $idIndicador = null, $dsIndicador = null, $subproduto = null, $idSubindicador = null, $subindicadorCodigo = null, $familiaCodigo = null, $indicadorCodigo = null, $carteira = null, $data = null, $competencia = null, $linhas = null, $cash = null, $conquista = null, $atividade = null)
    {
        $this->campanhaId = $campanhaId;
        $this->sprintId = $sprintId;
        $this->segmento = $segmento;
        $this->segmentoId = $segmentoId;
        $this->diretoriaId = $diretoriaId;
        $this->diretoriaNome = $diretoriaNome;
        $this->gerenciaRegionalId = $gerenciaRegionalId;
        $this->regionalNome = $regionalNome;
        $this->agenciaId = $agenciaId;
        $this->agenciaNome = $agenciaNome;
        $this->gerenteGestaoId = $gerenteGestaoId;
        $this->gerenteGestaoNome = $gerenteGestaoNome;
        $this->gerenteId = $gerenteId;
        $this->gerenteNome = $gerenteNome;
        $this->familiaId = $familiaId;
        $this->idIndicador = $idIndicador;
        $this->dsIndicador = $dsIndicador;
        $this->subproduto = $subproduto;
        $this->idSubindicador = $idSubindicador;
        $this->subindicadorCodigo = $subindicadorCodigo;
        $this->familiaCodigo = $familiaCodigo;
        $this->indicadorCodigo = $indicadorCodigo;
        $this->carteira = $carteira;
        $this->data = $data;
        $this->competencia = $competencia;
        $this->linhas = $linhas;
        $this->cash = $cash;
        $this->conquista = $conquista;
        $this->atividade = $atividade;
    }

    public function toArray()
    {
        return [
            'campanha_id' => $this->campanhaId,
            'sprint_id' => $this->sprintId,
            'segmento' => $this->segmento,
            'segmento_id' => $this->segmentoId,
            'diretoria_id' => $this->diretoriaId,
            'diretoria_nome' => $this->diretoriaNome,
            'gerencia_regional_id' => $this->gerenciaRegionalId,
            'regional_nome' => $this->regionalNome,
            'agencia_id' => $this->agenciaId,
            'agencia_nome' => $this->agenciaNome,
            'gerente_gestao_id' => $this->gerenteGestaoId,
            'gerente_gestao_nome' => $this->gerenteGestaoNome,
            'gerente_id' => $this->gerenteId,
            'gerente_nome' => $this->gerenteNome,
            'familia_id' => $this->familiaId,
            'id_indicador' => $this->idIndicador,
            'ds_indicador' => $this->dsIndicador,
            'subproduto' => $this->subproduto,
            'id_subindicador' => $this->idSubindicador,
            'subindicador_codigo' => $this->subindicadorCodigo,
            'familia_codigo' => $this->familiaCodigo,
            'indicador_codigo' => $this->indicadorCodigo,
            'carteira' => $this->carteira,
            'data' => $this->data,
            'competencia' => $this->competencia,
            'linhas' => $this->linhas,
            'cash' => $this->cash,
            'conquista' => $this->conquista,
            'atividade' => $this->atividade,
        ];
    }
}

