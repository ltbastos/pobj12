<?php

namespace App\Domain\DTO;

class PontosDTO
{
    private $id;
    private $funcional;
    private $idIndicador;
    private $idFamilia;
    private $indicador;
    private $meta;
    private $realizado;
    private $dataRealizado;
    private $dtAtualizacao;

    public function __construct(
        $id = null,
        $funcional = null,
        $idIndicador = null,
        $idFamilia = null,
        $indicador = null,
        $meta = null,
        $realizado = null,
        $dataRealizado = null,
        $dtAtualizacao = null
    ) {
        $this->id = $id;
        $this->funcional = $funcional;
        $this->idIndicador = $idIndicador;
        $this->idFamilia = $idFamilia;
        $this->indicador = $indicador;
        $this->meta = $meta;
        $this->realizado = $realizado;
        $this->dataRealizado = $dataRealizado;
        $this->dtAtualizacao = $dtAtualizacao;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'funcional' => $this->funcional,
            'id_indicador' => $this->idIndicador,
            'id_familia' => $this->idFamilia,
            'indicador' => $this->indicador,
            'meta' => $this->meta,
            'realizado' => $this->realizado,
            'data_realizado' => $this->dataRealizado,
            'dt_atualizacao' => $this->dtAtualizacao,
        ];
    }
}

