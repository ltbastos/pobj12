<?php

namespace App\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class Familia extends Model
{
    protected $table = 'familia';
    
    public $timestamps = false;
    
    protected $fillable = [
        'nm_familia',
    ];
    
    /**
     * Relacionamento com Produtos
     */
    public function produtos()
    {
        return $this->hasMany(DProduto::class, 'familia_id');
    }
}

