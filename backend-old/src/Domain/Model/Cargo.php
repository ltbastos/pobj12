<?php

namespace App\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class Cargo extends Model
{
    protected $table = 'cargos';
    
    public $timestamps = true;
    
    protected $fillable = [
        'nome',
    ];
    
    /**
     * Relacionamento com Estrutura
     */
    public function estruturas()
    {
        return $this->hasMany(Estrutura::class, 'cargo_id');
    }
}

