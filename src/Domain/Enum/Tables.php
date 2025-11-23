<?php
namespace App\Domain\Enum;

class Tables
{
    // Tabelas de fatos (f_*)
    const F_CAMPANHAS = 'f_campanhas';
    const F_PONTOS = 'f_pontos';
    const F_META = 'f_meta';
    const F_DETALHES = 'f_detalhes';
    const F_REALIZADOS = 'f_realizados';
    const F_VARIAVEL = 'f_variavel';
    const F_LEADS_PROPENSOS = 'f_leads_propensos';
    const F_HISTORICO_RANKING_POBJ = 'f_historico_ranking_pobj';

    // Tabelas de dimensão (d_*)
    const D_ESTRUTURA = 'd_estrutura';
    const D_PRODUTOS = 'd_produtos';
    const D_STATUS_INDICADORES = 'd_status_indicadores';
    const D_CALENDARIO = 'd_calendario';

    // Tabelas Omega
    const OMEGA_STATUS = 'omega_status';
    const OMEGA_DEPARTAMENTOS = 'omega_departamentos';
    const OMEGA_CHAMADOS = 'omega_chamados';
    const OMEGA_USUARIOS = 'omega_usuarios';

    public static function all(): array
    {
        return [
            self::F_CAMPANHAS,
            self::F_PONTOS,
            self::F_META,
            self::F_DETALHES,
            self::F_REALIZADOS,
            self::F_VARIAVEL,
            self::F_LEADS_PROPENSOS,
            self::F_HISTORICO_RANKING_POBJ,
            self::D_ESTRUTURA,
            self::D_PRODUTOS,
            self::D_STATUS_INDICADORES,
            self::D_CALENDARIO,
            self::OMEGA_STATUS,
            self::OMEGA_DEPARTAMENTOS,
            self::OMEGA_CHAMADOS,
            self::OMEGA_USUARIOS,
        ];
    }
}