<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="f_campanhas")
 */
class FCampanhas
{
    /**
     * @ORM\Id
     * @ORM\Column(name="campanha_id", type="string", length=60)
     */
    private $campanhaId;

    /**
     * @ORM\Column(name="sprint_id", type="string", length=60)
     */
    private $sprintId;

    /**
     * @ORM\Column(name="diretoria_id", type="string", length=50)
     */
    private $diretoriaId;

    /**
     * @ORM\Column(name="diretoria_nome", type="string", length=150)
     */
    private $diretoriaNome;

    /**
     * @ORM\Column(name="gerencia_regional_id", type="string", length=50)
     */
    private $gerenciaRegionalId;

    /**
     * @ORM\Column(name="regional_nome", type="string", length=150)
     */
    private $regionalNome;

    /**
     * @ORM\Column(name="agencia_id", type="string", length=50)
     */
    private $agenciaId;

    /**
     * @ORM\Column(name="agencia_nome", type="string", length=150)
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
     * @ORM\Column(type="string", length=100)
     */
    private $segmento;

    /**
     * @ORM\Column(name="segmento_id", type="string", length=50)
     */
    private $segmentoId;

    /**
     * @ORM\Column(name="familia_id", type="string", length=20)
     */
    private $familiaId;

    /**
     * @ORM\Column(name="id_indicador", type="string", length=80)
     */
    private $idIndicador;

    /**
     * @ORM\Column(name="ds_indicador", type="string", length=150)
     */
    private $dsIndicador;

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $subproduto;

    /**
     * @ORM\Column(name="id_subindicador", type="string", length=80, options={"default"="0"})
     */
    private $idSubindicador = '0';

    /**
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    private $carteira;

    /**
     * @ORM\Column(type="decimal", precision=18, scale=2, nullable=true)
     */
    private $linhas;

    /**
     * @ORM\Column(type="decimal", precision=18, scale=2, nullable=true)
     */
    private $cash;

    /**
     * @ORM\Column(type="decimal", precision=18, scale=2, nullable=true)
     */
    private $conquista;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $atividade;

    /**
     * @ORM\Column(type="date")
     */
    private $data;

    /**
     * @ORM\Column(name="familia_codigo", type="string", length=20, nullable=true)
     */
    private $familiaCodigo;

    /**
     * @ORM\Column(name="indicador_codigo", type="string", length=20, nullable=true)
     */
    private $indicadorCodigo;

    /**
     * @ORM\Column(name="subindicador_codigo", type="string", length=20, nullable=true)
     */
    private $subindicadorCodigo;

    public function getCampanhaId()
    {
        return $this->campanhaId;
    }

    public function setCampanhaId($campanhaId)
    {
        $this->campanhaId = $campanhaId;
        return $this;
    }

    public function getSprintId()
    {
        return $this->sprintId;
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

    public function getRegionalNome()
    {
        return $this->regionalNome;
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

    public function getSegmento()
    {
        return $this->segmento;
    }

    public function getSegmentoId()
    {
        return $this->segmentoId;
    }

    public function getFamiliaId()
    {
        return $this->familiaId;
    }

    public function getIdIndicador()
    {
        return $this->idIndicador;
    }

    public function getDsIndicador()
    {
        return $this->dsIndicador;
    }

    public function getSubproduto()
    {
        return $this->subproduto;
    }

    public function getIdSubindicador()
    {
        return $this->idSubindicador;
    }

    public function getCarteira()
    {
        return $this->carteira;
    }

    public function getLinhas()
    {
        return $this->linhas;
    }

    public function getCash()
    {
        return $this->cash;
    }

    public function getConquista()
    {
        return $this->conquista;
    }

    public function getAtividade()
    {
        return $this->atividade;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getFamiliaCodigo()
    {
        return $this->familiaCodigo;
    }

    public function getIndicadorCodigo()
    {
        return $this->indicadorCodigo;
    }

    public function getSubindicadorCodigo()
    {
        return $this->subindicadorCodigo;
    }
}

