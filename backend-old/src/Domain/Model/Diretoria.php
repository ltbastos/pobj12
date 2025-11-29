<?php

namespace App\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class Diretoria extends Model
{
    protected $table = 'diretorias';
    
    public $timestamps = true;
    
    protected $fillable = [
        'segmento_id',
        'nome',
    ];
    
    /**
     * Relacionamento com Segmento
     */
    public function segmento()
    {
        return $this->belongsTo(Segmento::class, 'segmento_id');
    }
    
    /**
     * Relacionamento com Regionais
     */
    public function regionais()
    {
        return $this->hasMany(Regional::class, 'diretoria_id');
    }
    
    /**
     * Relacionamento com Estrutura
     */
    public function estruturas()
    {
        return $this->hasMany(Estrutura::class, 'diretoria_id');
    }
}

