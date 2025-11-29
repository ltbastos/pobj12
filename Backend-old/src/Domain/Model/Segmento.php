<?php

namespace App\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class Segmento extends Model
{
    protected $table = 'segmentos';
    
    public $timestamps = true;
    
    protected $fillable = [
        'nome',
    ];
    
    /**
     * Relacionamento com Diretorias
     */
    public function diretorias()
    {
        return $this->hasMany(Diretoria::class, 'segmento_id');
    }
    
    /**
     * Relacionamento com Estrutura
     */
    public function estruturas()
    {
        return $this->hasMany(Estrutura::class, 'segmento_id');
    }
}

