<?php

namespace App\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class DEstrutura extends Model
{
    protected $table = 'd_estrutura';
    
    protected $primaryKey = 'funcional';
    
    public $incrementing = false;
    
    protected $keyType = 'string';
    
    public $timestamps = true;
    
    protected $fillable = [
        'funcional',
        'nome',
        'cargo_id',
        'segmento_id',
        'diretoria_id',
        'regional_id',
        'agencia_id',
    ];
    
    protected $casts = [
        'cargo_id' => 'integer',
        'segmento_id' => 'integer',
        'diretoria_id' => 'integer',
        'regional_id' => 'integer',
        'agencia_id' => 'integer',
    ];
    
    /**
     * Relacionamento com Cargo
     */
    public function cargo()
    {
        return $this->belongsTo(Cargo::class, 'cargo_id');
    }
    
    /**
     * Relacionamento com Segmento
     */
    public function segmento()
    {
        return $this->belongsTo(Segmento::class, 'segmento_id');
    }
    
    /**
     * Relacionamento com Diretoria
     */
    public function diretoria()
    {
        return $this->belongsTo(Diretoria::class, 'diretoria_id');
    }
    
    /**
     * Relacionamento com Regional
     */
    public function regional()
    {
        return $this->belongsTo(Regional::class, 'regional_id');
    }
    
    /**
     * Relacionamento com Agencia
     */
    public function agencia()
    {
        return $this->belongsTo(Agencia::class, 'agencia_id');
    }
    
    /**
     * Relacionamento com FDetalhes
     */
    public function detalhes()
    {
        return $this->hasMany(FDetalhes::class, 'funcional', 'funcional');
    }
    
    /**
     * Relacionamento com FHistoricoRankingPobj
     */
    public function historicoRanking()
    {
        return $this->hasMany(FHistoricoRankingPobj::class, 'funcional', 'funcional');
    }
    
    /**
     * Relacionamento com FMeta
     */
    public function metas()
    {
        return $this->hasMany(FMeta::class, 'funcional', 'funcional');
    }
    
    /**
     * Relacionamento com FPontos
     */
    public function pontos()
    {
        return $this->hasMany(FPontos::class, 'funcional', 'funcional');
    }
    
    /**
     * Relacionamento com FRealizados
     */
    public function realizados()
    {
        return $this->hasMany(FRealizados::class, 'funcional', 'funcional');
    }
    
    /**
     * Relacionamento com FVariavel
     */
    public function variaveis()
    {
        return $this->hasMany(FVariavel::class, 'funcional', 'funcional');
    }
}

