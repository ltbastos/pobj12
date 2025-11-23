<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="f_pontos")
 */
class FPontos
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $funcional;

    /**
     * @ORM\Column(name="id_indicador", type="integer")
     */
    private $idIndicador;

    /**
     * @ORM\Column(name="id_familia", type="integer")
     */
    private $idFamilia;

    /**
     * @ORM\Column(type="string", length=120)
     */
    private $indicador;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $meta;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $realizado;

    /**
     * @ORM\Column(name="data_realizado", type="date", nullable=true)
     */
    private $dataRealizado;

    /**
     * @ORM\Column(name="dt_atualizacao", type="datetime", nullable=true)
     */
    private $dtAtualizacao;

    public function getId()
    {
        return $this->id;
    }

    public function getFuncional()
    {
        return $this->funcional;
    }

    public function setFuncional($funcional)
    {
        $this->funcional = $funcional;
        return $this;
    }

    public function getIdIndicador()
    {
        return $this->idIndicador;
    }

    public function setIdIndicador($idIndicador)
    {
        $this->idIndicador = $idIndicador;
        return $this;
    }

    public function getIdFamilia()
    {
        return $this->idFamilia;
    }

    public function setIdFamilia($idFamilia)
    {
        $this->idFamilia = $idFamilia;
        return $this;
    }

    public function getIndicador()
    {
        return $this->indicador;
    }

    public function setIndicador($indicador)
    {
        $this->indicador = $indicador;
        return $this;
    }

    public function getMeta()
    {
        return $this->meta;
    }

    public function setMeta($meta)
    {
        $this->meta = $meta;
        return $this;
    }

    public function getRealizado()
    {
        return $this->realizado;
    }

    public function setRealizado($realizado)
    {
        $this->realizado = $realizado;
        return $this;
    }

    public function getDataRealizado()
    {
        return $this->dataRealizado;
    }

    public function setDataRealizado($dataRealizado)
    {
        $this->dataRealizado = $dataRealizado;
        return $this;
    }

    public function getDtAtualizacao()
    {
        return $this->dtAtualizacao;
    }

    public function setDtAtualizacao($dtAtualizacao)
    {
        $this->dtAtualizacao = $dtAtualizacao;
        return $this;
    }

    public static function fromArray(array $data): self
    {
        $entity = new self();
        $entity->id = isset($data['id']) ? (int)$data['id'] : null;
        $entity->funcional = $data['funcional'] ?? null;
        $entity->idIndicador = isset($data['id_indicador']) ? (int)$data['id_indicador'] : null;
        $entity->idFamilia = isset($data['id_familia']) ? (int)$data['id_familia'] : null;
        $entity->indicador = $data['indicador'] ?? null;
        $entity->meta = isset($data['meta']) ? (float)$data['meta'] : null;
        $entity->realizado = isset($data['realizado']) ? (float)$data['realizado'] : null;
        $entity->dataRealizado = $data['data_realizado'] ?? null;
        $entity->dtAtualizacao = $data['dt_atualizacao'] ?? null;
        return $entity;
    }

    public function toDTO(): \App\Domain\DTO\PontosDTO
    {
        return new \App\Domain\DTO\PontosDTO(
            $this->id,
            $this->funcional,
            $this->idIndicador,
            $this->idFamilia,
            $this->indicador,
            $this->meta,
            $this->realizado,
            $this->dataRealizado,
            $this->dtAtualizacao
        );
    }
}

