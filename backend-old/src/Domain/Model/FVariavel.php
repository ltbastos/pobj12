<?php

namespace App\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class FVariavel extends Model
{
    protected $table = 'f_variavel';
    
    public $timestamps = false;
    
    protected $fillable = [
        'funcional',
        'meta',
        'variavel',
        'dt_atualizacao',
    ];
    
    protected $casts = [
        'meta' => 'decimal:2',
        'variavel' => 'decimal:2',
        'dt_atualizacao' => 'date',
    ];
    
    /**
     * Relacionamento com DCalendario
     */
    public function calendario()
    {
        return $this->belongsTo(DCalendario::class, 'dt_atualizacao', 'data');
    }
    
    /**
     * Relacionamento com Estrutura
     */
    public function estrutura()
    {
        return $this->belongsTo(Estrutura::class, 'funcional', 'funcional');
    }
}

