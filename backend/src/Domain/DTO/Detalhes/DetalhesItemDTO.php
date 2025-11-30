<?php

namespace App\Domain\DTO\Detalhes;

class DetalhesItemDTO
{
    public $registro_id;
    public $id_contrato;
    public $data;
    public $competencia;
    public $ano;
    public $mes;
    public $mes_nome;
    
    public $segmento_id;
    public $segmento;
    public $diretoria_id;
    public $diretoria_nome;
    public $gerencia_id;
    public $gerencia_nome;
    public $agencia_id;
    public $agencia_nome;
    public $gerente_id;
    public $gerente_nome;
    public $gerente_gestao_id;
    public $gerente_gestao_nome;
    
    public $familia_id;
    public $familia_nome;
    public $id_indicador;
    public $ds_indicador;
    public $id_subindicador;
    public $subindicador;
    public $peso;
    
    public $valor_realizado;
    public $valor_meta;
    public $meta_mensal;
    
    public $canal_venda;
    public $tipo_venda;
    public $modalidade_pagamento;
    public $dt_vencimento;
    public $dt_cancelamento;
    public $motivo_cancelamento;
    public $status_id;

    public function __construct(
        ?string $registro_id = null,
        ?string $id_contrato = null,
        ?string $data = null,
        ?string $competencia = null,
        ?string $ano = null,
        ?string $mes = null,
        ?string $mes_nome = null,
        ?string $segmento_id = null,
        ?string $segmento = null,
        ?string $diretoria_id = null,
        ?string $diretoria_nome = null,
        ?string $gerencia_id = null,
        ?string $gerencia_nome = null,
        ?string $agencia_id = null,
        ?string $agencia_nome = null,
        ?string $gerente_id = null,
        ?string $gerente_nome = null,
        ?string $gerente_gestao_id = null,
        ?string $gerente_gestao_nome = null,
        ?string $familia_id = null,
        ?string $familia_nome = null,
        ?string $id_indicador = null,
        ?string $ds_indicador = null,
        ?string $id_subindicador = null,
        ?string $subindicador = null,
        ?float $peso = null,
        ?float $valor_realizado = null,
        ?float $valor_meta = null,
        ?float $meta_mensal = null,
        ?string $canal_venda = null,
        ?string $tipo_venda = null,
        ?string $modalidade_pagamento = null,
        ?string $dt_vencimento = null,
        ?string $dt_cancelamento = null,
        ?string $motivo_cancelamento = null,
        ?int $status_id = null
    ) {
        $this->registro_id = $registro_id;
        $this->id_contrato = $id_contrato;
        $this->data = $data;
        $this->competencia = $competencia;
        $this->ano = $ano;
        $this->mes = $mes;
        $this->mes_nome = $mes_nome;
        $this->segmento_id = $segmento_id;
        $this->segmento = $segmento;
        $this->diretoria_id = $diretoria_id;
        $this->diretoria_nome = $diretoria_nome;
        $this->gerencia_id = $gerencia_id;
        $this->gerencia_nome = $gerencia_nome;
        $this->agencia_id = $agencia_id;
        $this->agencia_nome = $agencia_nome;
        $this->gerente_id = $gerente_id;
        $this->gerente_nome = $gerente_nome;
        $this->gerente_gestao_id = $gerente_gestao_id;
        $this->gerente_gestao_nome = $gerente_gestao_nome;
        $this->familia_id = $familia_id;
        $this->familia_nome = $familia_nome;
        $this->id_indicador = $id_indicador;
        $this->ds_indicador = $ds_indicador;
        $this->id_subindicador = $id_subindicador;
        $this->subindicador = $subindicador;
        $this->peso = $peso;
        $this->valor_realizado = $valor_realizado;
        $this->valor_meta = $valor_meta;
        $this->meta_mensal = $meta_mensal;
        $this->canal_venda = $canal_venda;
        $this->tipo_venda = $tipo_venda;
        $this->modalidade_pagamento = $modalidade_pagamento;
        $this->dt_vencimento = $dt_vencimento;
        $this->dt_cancelamento = $dt_cancelamento;
        $this->motivo_cancelamento = $motivo_cancelamento;
        $this->status_id = $status_id;
    }

    public function toArray(): array
    {
        return [
            'registro_id' => $this->registro_id,
            'id_contrato' => $this->id_contrato,
            'data' => $this->data,
            'competencia' => $this->competencia,
            'ano' => $this->ano,
            'mes' => $this->mes,
            'mes_nome' => $this->mes_nome,
            'segmento_id' => $this->segmento_id,
            'segmento' => $this->segmento,
            'diretoria_id' => $this->diretoria_id,
            'diretoria_nome' => $this->diretoria_nome,
            'gerencia_id' => $this->gerencia_id,
            'gerencia_nome' => $this->gerencia_nome,
            'agencia_id' => $this->agencia_id,
            'agencia_nome' => $this->agencia_nome,
            'gerente_id' => $this->gerente_id,
            'gerente_nome' => $this->gerente_nome,
            'gerente_gestao_id' => $this->gerente_gestao_id,
            'gerente_gestao_nome' => $this->gerente_gestao_nome,
            'familia_id' => $this->familia_id,
            'familia_nome' => $this->familia_nome,
            'id_indicador' => $this->id_indicador,
            'ds_indicador' => $this->ds_indicador,
            'id_subindicador' => $this->id_subindicador,
            'subindicador' => $this->subindicador,
            'peso' => $this->peso,
            'valor_realizado' => $this->valor_realizado,
            'valor_meta' => $this->valor_meta,
            'meta_mensal' => $this->meta_mensal,
            'canal_venda' => $this->canal_venda,
            'tipo_venda' => $this->tipo_venda,
            'modalidade_pagamento' => $this->modalidade_pagamento,
            'dt_vencimento' => $this->dt_vencimento,
            'dt_cancelamento' => $this->dt_cancelamento,
            'motivo_cancelamento' => $this->motivo_cancelamento,
            'status_id' => $this->status_id,
        ];
    }
}

