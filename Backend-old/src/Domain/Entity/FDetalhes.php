<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="f_detalhes")
 */
class FDetalhes
{
    /**
     * @ORM\Id
     * @ORM\Column(name="contrato_id", type="string", length=80)
     */
    private $contratoId;

    /**
     * @ORM\Column(name="registro_id", type="string", length=60)
     */
    private $registroId;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $segmento;

    /**
     * @ORM\Column(name="segmento_id", type="string", length=50)
     */
    private $segmentoId;

    /**
     * @ORM\Column(name="diretoria_id", type="string", length=50)
     */
    private $diretoriaId;

    /**
     * @ORM\Column(name="diretoria_nome", type="string", length=150, nullable=true)
     */
    private $diretoriaNome;

    /**
     * @ORM\Column(name="gerencia_regional_id", type="string", length=50)
     */
    private $gerenciaRegionalId;

    /**
     * @ORM\Column(name="gerencia_regional_nome", type="string", length=150, nullable=true)
     */
    private $gerenciaRegionalNome;

    /**
     * @ORM\Column(name="agencia_id", type="string", length=50)
     */
    private $agenciaId;

    /**
     * @ORM\Column(name="agencia_nome", type="string", length=150, nullable=true)
     */
    private $agenciaNome;

    /**
     * @ORM\Column(name="gerente_gestao_id", type="string", length=50, nullable=true)
     */
    private $gerenteGestaoId;

    /**
     * @ORM\Column(name="gerente_gestao_nome", type="string", length=150, nullable=true)
     */
    private $gerenteGestaoNome;

    /**
     * @ORM\Column(name="gerente_id", type="string", length=50, nullable=true)
     */
    private $gerenteId;

    /**
     * @ORM\Column(name="gerente_nome", type="string", length=150, nullable=true)
     */
    private $gerenteNome;

    /**
     * @ORM\Column(name="familia_id", type="string", length=20)
     */
    private $familiaId;

    /**
     * @ORM\Column(name="familia_nome", type="string", length=150, nullable=true)
     */
    private $familiaNome;

    /**
     * @ORM\Column(name="id_indicador", type="string", length=80)
     */
    private $idIndicador;

    /**
     * @ORM\Column(name="ds_indicador", type="string", length=150, nullable=true)
     */
    private $dsIndicador;

    /**
     * @ORM\Column(name="id_subindicador", type="string", length=80, options={"default"="0"})
     */
    private $idSubindicador = '0';

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $subindicador;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $carteira;

    /**
     * @ORM\Column(name="canal_venda", type="string", length=150, nullable=true)
     */
    private $canalVenda;

    /**
     * @ORM\Column(name="tipo_venda", type="string", length=100, nullable=true)
     */
    private $tipoVenda;

    /**
     * @ORM\Column(name="modalidade_pagamento", type="string", length=100, nullable=true)
     */
    private $modalidadePagamento;

    /**
     * @ORM\Column(type="date")
     */
    private $data;

    /**
     * @ORM\Column(type="date")
     */
    private $competencia;

    /**
     * @ORM\Column(name="valor_meta", type="decimal", precision=18, scale=2, nullable=true)
     */
    private $valorMeta;

    /**
     * @ORM\Column(name="valor_realizado", type="decimal", precision=18, scale=2, nullable=true)
     */
    private $valorRealizado;

    /**
     * @ORM\Column(type="decimal", precision=18, scale=4, nullable=true)
     */
    private $quantidade;

    /**
     * @ORM\Column(type="decimal", precision=18, scale=4, nullable=true)
     */
    private $peso;

    /**
     * @ORM\Column(type="decimal", precision=18, scale=4, nullable=true)
     */
    private $pontos;

    /**
     * @ORM\Column(name="data_vencimento", type="date", nullable=true)
     */
    private $dataVencimento;

    /**
     * @ORM\Column(name="data_cancelamento", type="date", nullable=true)
     */
    private $dataCancelamento;

    /**
     * @ORM\Column(name="motivo_cancelamento", type="string", length=255, nullable=true)
     */
    private $motivoCancelamento;

    /**
     * @ORM\Column(name="status_id", type="string", length=20, nullable=true)
     */
    private $statusId;

    public function getContratoId()
    {
        return $this->contratoId;
    }

    public function setContratoId($contratoId)
    {
        $this->contratoId = $contratoId;
        return $this;
    }

    public function getRegistroId()
    {
        return $this->registroId;
    }

    public function getSegmento()
    {
        return $this->segmento;
    }

    public function getSegmentoId()
    {
        return $this->segmentoId;
    }

    public function getDiretoriaId()
    {
        return $this->diretoriaId;
    }

    public function getDiretoriaNome()
    {
        return $this->diretoriaNome;
    }

    public function getGerenciaRegionalId()
    {
        return $this->gerenciaRegionalId;
    }

    public function getGerenciaRegionalNome()
    {
        return $this->gerenciaRegionalNome;
    }

    public function getAgenciaId()
    {
        return $this->agenciaId;
    }

    public function getAgenciaNome()
    {
        return $this->agenciaNome;
    }

    public function getGerenteGestaoId()
    {
        return $this->gerenteGestaoId;
    }

    public function getGerenteGestaoNome()
    {
        return $this->gerenteGestaoNome;
    }

    public function getGerenteId()
    {
        return $this->gerenteId;
    }

    public function getGerenteNome()
    {
        return $this->gerenteNome;
    }

    public function getFamiliaId()
    {
        return $this->familiaId;
    }

    public function getFamiliaNome()
    {
        return $this->familiaNome;
    }

    public function getIdIndicador()
    {
        return $this->idIndicador;
    }

    public function getDsIndicador()
    {
        return $this->dsIndicador;
    }

    public function getIdSubindicador()
    {
        return $this->idSubindicador;
    }

    public function getSubindicador()
    {
        return $this->subindicador;
    }

    public function getCarteira()
    {
        return $this->carteira;
    }

    public function getCanalVenda()
    {
        return $this->canalVenda;
    }

    public function getTipoVenda()
    {
        return $this->tipoVenda;
    }

    public function getModalidadePagamento()
    {
        return $this->modalidadePagamento;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getCompetencia()
    {
        return $this->competencia;
    }

    public function getValorMeta()
    {
        return $this->valorMeta;
    }

    public function getValorRealizado()
    {
        return $this->valorRealizado;
    }

    public function getQuantidade()
    {
        return $this->quantidade;
    }

    public function getPeso()
    {
        return $this->peso;
    }

    public function getPontos()
    {
        return $this->pontos;
    }

    public function getStatusId()
    {
        return $this->statusId;
    }
}

