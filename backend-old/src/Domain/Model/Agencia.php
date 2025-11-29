<?php

namespace App\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class Agencia extends Model
{
    protected $table = 'agencias';
    
    public $timestamps = true;
    
    protected $fillable = [
        'regional_id',
        'nome',
        'porte',
    ];
    
    /**
     * Relacionamento com Regional
     */
    public function regional()
    {
        return $this->belongsTo(Regional::class, 'regional_id');
    }
    
    /**
     * Relacionamento com Estrutura
     */
    public function estruturas()
    {
        return $this->hasMany(Estrutura::class, 'agencia_id');
    }
}

