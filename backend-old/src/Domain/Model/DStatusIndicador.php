<?php

namespace App\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class DStatusIndicador extends Model
{
    protected $table = 'd_status_indicadores';
    
    protected $primaryKey = 'id';
    
    public $incrementing = false;
    
    protected $keyType = 'string';
    
    public $timestamps = false;
    
    protected $fillable = [
        'id',
        'status',
    ];
    
    /**
     * Relacionamento com FDetalhes
     */
    public function detalhes()
    {
        return $this->hasMany(FDetalhes::class, 'status_id', 'id');
    }
}

