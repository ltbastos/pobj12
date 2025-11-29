<?php

namespace App\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class FMeta extends Model
{
    protected $table = 'f_meta';
    
    public $timestamps = true;
    
    protected $fillable = [
        'data_meta',
        'funcional',
        'produto_id',
        'meta_mensal',
    ];
    
    protected $casts = [
        'data_meta' => 'date',
        'produto_id' => 'integer',
        'meta_mensal' => 'decimal:2',
    ];
    
    /**
     * Relacionamento com DCalendario
     */
    public function calendario()
    {
        return $this->belongsTo(DCalendario::class, 'data_meta', 'data');
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

