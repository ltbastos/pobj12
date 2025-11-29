<?php

namespace App\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class FPontos extends Model
{
    protected $table = 'f_pontos';
    
    public $timestamps = false;
    
    protected $fillable = [
        'funcional',
        'produto_id',
        'meta',
        'realizado',
        'data_realizado',
        'dt_atualizacao',
    ];
    
    protected $casts = [
        'produto_id' => 'integer',
        'meta' => 'decimal:2',
        'realizado' => 'decimal:2',
        'data_realizado' => 'date',
        'dt_atualizacao' => 'datetime',
    ];
    
    /**
     * Relacionamento com Estrutura
     */
    public function estrutura()
    {
        return $this->belongsTo(Estrutura::class, 'funcional', 'funcional');
    }
    
    /**
     * Relacionamento com DProduto
     */
    public function produto()
    {
        return $this->belongsTo(DProduto::class, 'produto_id');
    }
    
    /**
     * Relacionamento com DCalendario
     */
    public function calendario()
    {
        return $this->belongsTo(DCalendario::class, 'data_realizado', 'data');
    }
}

