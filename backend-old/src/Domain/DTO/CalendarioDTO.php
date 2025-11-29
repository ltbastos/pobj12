<?php

namespace App\Domain\DTO;

class CalendarioDTO extends BaseFactDTO
{
    private $data;
    private $competencia;
    private $ano;
    private $mes;
    private $mesNome;
    private $dia;
    private $diaDaSemana;
    private $semana;
    private $trimestre;
    private $semestre;
    private $ehDiaUtil;

    public function __construct($data = null, $competencia = null, $ano = null, $mes = null, $mesNome = null, $dia = null, $diaDaSemana = null, $semana = null, $trimestre = null, $semestre = null, $ehDiaUtil = null)
    {
        $this->data = $data;
        $this->competencia = $competencia;
        $this->ano = $ano;
        $this->mes = $mes;
        $this->mesNome = $mesNome;
        $this->dia = $dia;
        $this->diaDaSemana = $diaDaSemana;
        $this->semana = $semana;
        $this->trimestre = $trimestre;
        $this->semestre = $semestre;
        $this->ehDiaUtil = $ehDiaUtil;
    }

    public function toArray()
    {
        return [
            'data' => $this->data,
            'competencia' => $this->competencia,
            'ano' => $this->ano,
            'mes' => $this->mes,
            'mes_nome' => $this->mesNome,
            'dia' => $this->dia,
            'dia_da_semana' => $this->diaDaSemana,
            'semana' => $this->semana,
            'trimestre' => $this->trimestre,
            'semestre' => $this->semestre,
            'eh_dia_util' => $this->ehDiaUtil,
        ];
    }
}

