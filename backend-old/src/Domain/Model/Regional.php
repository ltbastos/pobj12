<?php

namespace App\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class Regional extends Model
{
    protected $table = 'regionais';
    
    public $timestamps = true;
    
    protected $fillable = [
        'diretoria_id',
        'nome',
    ];
    
    /**
     * Relacionamento com Diretoria
     */
    public function diretoria()
    {
        return $this->belongsTo(Diretoria::class, 'diretoria_id');
    }
    
    /**
     * Relacionamento com Agencias
     */
    public function agencias()
    {
        return $this->hasMany(Agencia::class, 'regional_id');
    }
    
    /**
     * Relacionamento com Estrutura
     */
    public function estruturas()
    {
        return $this->hasMany(Estrutura::class, 'regional_id');
    }
}

