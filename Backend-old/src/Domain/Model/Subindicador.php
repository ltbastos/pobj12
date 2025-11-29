<?php

namespace App\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class Subindicador extends Model
{
    protected $table = 'subindicador';
    
    public $timestamps = false;
    
    protected $fillable = [
        'indicador_id',
        'nm_subindicador',
    ];
    
    /**
     * Relacionamento com Indicador
     */
    public function indicador()
    {
        return $this->belongsTo(Indicador::class, 'indicador_id');
    }
    
    /**
     * Relacionamento com Produtos
     */
    public function produtos()
    {
        return $this->hasMany(DProduto::class, 'subindicador_id');
    }
}

