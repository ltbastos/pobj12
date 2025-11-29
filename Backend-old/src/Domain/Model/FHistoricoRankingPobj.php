<?php

namespace App\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class FHistoricoRankingPobj extends Model
{
    protected $table = 'f_historico_ranking_pobj';
    
    public $timestamps = false;
    
    protected $fillable = [
        'data',
        'funcional',
        'grupo',
        'ranking',
        'realizado',
    ];
    
    protected $casts = [
        'data' => 'date',
        'grupo' => 'integer',
        'ranking' => 'integer',
        'realizado' => 'decimal:2',
    ];
    
    /**
     * Relacionamento com DCalendario
     */
    public function calendario()
    {
        return $this->belongsTo(DCalendario::class, 'data', 'data');
    }
    
    /**
     * Relacionamento com Estrutura
     */
    public function estrutura()
    {
        return $this->belongsTo(Estrutura::class, 'funcional', 'funcional');
    }
}

