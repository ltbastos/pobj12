<?php

namespace App\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class DProduto extends Model
{
    protected $table = 'd_produtos';
    
    public $timestamps = false;
    
    protected $fillable = [
        'familia_id',
        'indicador_id',
        'subindicador_id',
        'peso',
        'metrica',
    ];
    
    protected $casts = [
        'familia_id' => 'integer',
        'indicador_id' => 'integer',
        'subindicador_id' => 'integer',
        'peso' => 'decimal:2',
    ];
    
    /**
     * Relacionamento com Familia
     */
    public function familia()
    {
        return $this->belongsTo(Familia::class, 'familia_id');
    }
    
    /**
     * Relacionamento com Indicador
     */
    public function indicador()
    {
        return $this->belongsTo(Indicador::class, 'indicador_id');
    }
    
    /**
     * Relacionamento com Subindicador
     */
    public function subindicador()
    {
        return $this->belongsTo(Subindicador::class, 'subindicador_id');
    }
    
    /**
     * Relacionamento com FDetalhes
     */
    public function detalhes()
    {
        return $this->hasMany(FDetalhes::class, 'id_produto');
    }
    
    /**
     * Relacionamento com FMeta
     */
    public function metas()
    {
        return $this->hasMany(FMeta::class, 'produto_id');
    }
    
    /**
     * Relacionamento com FPontos
     */
    public function pontos()
    {
        return $this->hasMany(FPontos::class, 'produto_id');
    }
    
    /**
     * Relacionamento com FRealizados
     */
    public function realizados()
    {
        return $this->hasMany(FRealizados::class, 'produto_id');
    }
}

