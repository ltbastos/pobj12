<?php

namespace App\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class DCalendario extends Model
{
    protected $table = 'd_calendario';
    
    protected $primaryKey = 'data';
    
    public $incrementing = false;
    
    protected $keyType = 'string';
    
    public $timestamps = false;
    
    protected $fillable = [
        'data',
        'ano',
        'mes',
        'mes_nome',
        'dia',
        'dia_da_semana',
        'semana',
        'trimestre',
        'semestre',
        'eh_dia_util',
    ];
    
    protected $casts = [
        'data' => 'date',
        'ano' => 'integer',
        'mes' => 'integer',
        'dia' => 'integer',
        'semana' => 'integer',
        'trimestre' => 'integer',
        'semestre' => 'integer',
        'eh_dia_util' => 'boolean',
    ];
    
    /**
     * Relacionamento com FDetalhes (competencia)
     */
    public function detalhesCompetencia()
    {
        return $this->hasMany(FDetalhes::class, 'competencia', 'data');
    }
    
    /**
     * Relacionamento com FDetalhes (dt_cadastro)
     */
    public function detalhesCadastro()
    {
        return $this->hasMany(FDetalhes::class, 'dt_cadastro', 'data');
    }
    
    /**
     * Relacionamento com FHistoricoRankingPobj
     */
    public function historicoRanking()
    {
        return $this->hasMany(FHistoricoRankingPobj::class, 'data', 'data');
    }
    
    /**
     * Relacionamento com FMeta
     */
    public function metas()
    {
        return $this->hasMany(FMeta::class, 'data_meta', 'data');
    }
    
    /**
     * Relacionamento com FPontos
     */
    public function pontos()
    {
        return $this->hasMany(FPontos::class, 'data_realizado', 'data');
    }
    
    /**
     * Relacionamento com FRealizados
     */
    public function realizados()
    {
        return $this->hasMany(FRealizados::class, 'data_realizado', 'data');
    }
    
    /**
     * Relacionamento com FVariavel
     */
    public function variaveis()
    {
        return $this->hasMany(FVariavel::class, 'dt_atualizacao', 'data');
    }
}

