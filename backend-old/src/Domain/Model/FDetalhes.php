<?php

namespace App\Domain\Model;

use Illuminate\Database\Eloquent\Model;

class FDetalhes extends Model
{
    protected $table = 'f_detalhes';
    
    public $timestamps = false;
    
    protected $fillable = [
        'contrato_id',
        'registro_id',
        'funcional',
        'id_produto',
        'dt_cadastro',
        'competencia',
        'valor_meta',
        'valor_realizado',
        'quantidade',
        'peso',
        'pontos',
        'dt_vencimento',
        'dt_cancelamento',
        'motivo_cancelamento',
        'canal_venda',
        'tipo_venda',
        'condicao_pagamento',
        'status_id',
    ];
    
    protected $casts = [
        'id_produto' => 'integer',
        'dt_cadastro' => 'date',
        'competencia' => 'date',
        'valor_meta' => 'decimal:2',
        'valor_realizado' => 'decimal:2',
        'quantidade' => 'decimal:4',
        'peso' => 'decimal:4',
        'pontos' => 'decimal:4',
        'dt_vencimento' => 'date',
        'dt_cancelamento' => 'date',
    ];
    
    /**
     * Relacionamento com Estrutura
     */
    public function estrutura()
    {
        return $this->belongsTo(Estrutura::class, 'funcional', 'funcional');
    }
    
    /**
     * Relacionamento com DProduto
     */
    public function produto()
    {
        return $this->belongsTo(DProduto::class, 'id_produto');
    }
    
    /**
     * Relacionamento com DCalendario (competencia)
     */
    public function calendarioCompetencia()
    {
        return $this->belongsTo(DCalendario::class, 'competencia', 'data');
    }
    
    /**
     * Relacionamento com DCalendario (dt_cadastro)
     */
    public function calendarioCadastro()
    {
        return $this->belongsTo(DCalendario::class, 'dt_cadastro', 'data');
    }
    
    /**
     * Relacionamento com DStatusIndicador
     */
    public function status()
    {
        return $this->belongsTo(DStatusIndicador::class, 'status_id', 'id');
    }
}

