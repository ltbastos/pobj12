<?php

namespace App\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class Indicador extends Model
{
    protected $table = 'indicador';
    
    public $timestamps = false;
    
    protected $fillable = [
        'nm_indicador',
        'familia_id',
    ];
    
    /**
     * Relacionamento com Subindicadores
     */
    public function subindicadores()
    {
        return $this->hasMany(Subindicador::class, 'indicador_id');
    }
    
    /**
     * Relacionamento com Produtos
     */
    public function produtos()
    {
        return $this->hasMany(DProduto::class, 'indicador_id');
    }

    public function familia()
    {
        return $this->belongsTo(Familia::class, 'familia_id');
    }
}

