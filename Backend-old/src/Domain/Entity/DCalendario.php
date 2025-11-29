<?php

namespace App\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="d_calendario")
 */
class DCalendario
{
    /**
     * @ORM\Id
     * @ORM\Column(type="date_immutable")
     */
    private $data;

    /**
     * @ORM\Column(type="integer")
     */
    private $ano;

    /**
     * @ORM\Column(type="smallint")
     */
    private $mes;

    /**
     * @ORM\Column(name="mes_nome", type="string", length=20)
     */
    private $mesNome;

    /**
     * @ORM\Column(type="smallint")
     */
    private $dia;

    /**
     * @ORM\Column(name="dia_da_semana", type="string", length=20)
     */
    private $diaDaSemana;

    /**
     * @ORM\Column(type="smallint")
     */
    private $semana;

    /**
     * @ORM\Column(type="smallint")
     */
    private $trimestre;

    /**
     * @ORM\Column(type="smallint")
     */
    private $semestre;

    /**
     * @ORM\Column(name="eh_dia_util", type="boolean", options={"default"=false})
     */
    private $ehDiaUtil = false;

    public function getData()
    {
        return $this->data;
    }

    public function setData(\DateTimeImmutable $data)
    {
        $this->data = $data;
        return $this;
    }

    public function getAno()
    {
        return $this->ano;
    }

    public function setAno($ano)
    {
        $this->ano = $ano;
        return $this;
    }

    public function getMes()
    {
        return $this->mes;
    }

    public function setMes($mes)
    {
        $this->mes = $mes;
        return $this;
    }

    public function getMesNome()
    {
        return $this->mesNome;
    }

    public function setMesNome($mesNome)
    {
        $this->mesNome = $mesNome;
        return $this;
    }

    public function getDia()
    {
        return $this->dia;
    }

    public function setDia($dia)
    {
        $this->dia = $dia;
        return $this;
    }

    public function getDiaDaSemana()
    {
        return $this->diaDaSemana;
    }

    public function setDiaDaSemana($diaDaSemana)
    {
        $this->diaDaSemana = $diaDaSemana;
        return $this;
    }

    public function getSemana()
    {
        return $this->semana;
    }

    public function setSemana($semana)
    {
        $this->semana = $semana;
        return $this;
    }

    public function getTrimestre()
    {
        return $this->trimestre;
    }

    public function setTrimestre($trimestre)
    {
        $this->trimestre = $trimestre;
        return $this;
    }

    public function getSemestre()
    {
        return $this->semestre;
    }

    public function setSemestre($semestre)
    {
        $this->semestre = $semestre;
        return $this;
    }

    public function isEhDiaUtil()
    {
        return $this->ehDiaUtil;
    }

    public function setEhDiaUtil($ehDiaUtil)
    {
        $this->ehDiaUtil = $ehDiaUtil;
        return $this;
    }

    public function __toString()
    {
        return $this->data ? $this->data->format('Y-m-d') : '';
    }
}

