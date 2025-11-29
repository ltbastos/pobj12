<?php

namespace App\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class FRealizados extends Model
{
    protected $table = 'f_realizados';
    
    public $timestamps = false;
    
    protected $fillable = [
        'id_contrato',
        'funcional',
        'data_realizado',
        'realizado',
        'produto_id',
    ];
    
    protected $casts = [
        'data_realizado' => 'date',
        'realizado' => 'decimal:2',
        'produto_id' => 'integer',
    ];
    
    /**
     * Relacionamento com DCalendario
     */
    public function calendario()
    {
        return $this->belongsTo(DCalendario::class, 'data_realizado', 'data');
    }
    
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
}

