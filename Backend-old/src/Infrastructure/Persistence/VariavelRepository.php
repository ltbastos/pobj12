<?php

namespace App\Infrastructure\Persistence;

use App\Domain\DTO\FilterDTO;
use App\Domain\DTO\VariavelDTO;
use App\Domain\Enum\Cargo;
use App\Domain\Enum\Tables;
use App\Infrastructure\Helpers\DateFormatter;
use App\Infrastructure\Helpers\ValueFormatter;

/**
 * Repositório para buscar todos os registros de variáveis com filtros opcionais
 */
class VariavelRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(VariavelDTO::class);
    }

    /**
     * Retorna o SELECT completo da consulta
     * @return string
     */
    public function baseSelect(): string
    {
        return "SELECT 
                    v.id,
                    v.funcional,
                    v.meta,
                    v.variavel,
                    v.dt_atualizacao,
                    COALESCE(e.nome, '') AS nome_funcional,
                    COALESCE(s.nome, '') AS segmento,
                    COALESCE(CAST(e.segmento_id AS CHAR), '') AS segmento_id,
                    COALESCE(d.nome, '') AS diretoria_nome,
                    COALESCE(CAST(e.diretoria_id AS CHAR), '') AS diretoria_id,
                    COALESCE(r.nome, '') AS regional_nome,
                    COALESCE(CAST(e.regional_id AS CHAR), '') AS gerencia_id,
                    COALESCE(a.nome, '') AS agencia_nome,
                    COALESCE(CAST(e.agencia_id AS CHAR), '') AS agencia_id
                FROM " . Tables::F_VARIAVEL . " v
                LEFT JOIN " . Tables::D_ESTRUTURA . " e ON e.funcional = CAST(v.funcional AS CHAR)
                LEFT JOIN segmentos s ON s.id = e.segmento_id
                LEFT JOIN diretorias d ON d.id = e.diretoria_id
                LEFT JOIN regionais r ON r.id = e.regional_id
                LEFT JOIN agencias a ON a.id = e.agencia_id
                WHERE 1=1";
    }

    /**
     * Constrói os filtros WHERE baseado no FilterDTO
     * @param FilterDTO|null $filters
     * @return array ['sql' => string, 'params' => array]
     */
    public function builderFilter(FilterDTO $filters = null): array
    {
        $sql = "";
        $params = [];
        
        if ($filters === null || !$filters->hasAnyFilter()) {
            return ['sql' => $sql, 'params' => $params];
        }

        if ($filters->getSegmento() !== null) {
            $sql .= " AND e.segmento_id = :segmento";
            $params[':segmento'] = $filters->getSegmento();
        }

        if ($filters->getDiretoria() !== null) {
            $sql .= " AND e.diretoria_id = :diretoria";
            $params[':diretoria'] = $filters->getDiretoria();
        }

        if ($filters->getRegional() !== null) {
            $sql .= " AND e.regional_id = :regional";
            $params[':regional'] = $filters->getRegional();
        }

        if ($filters->getAgencia() !== null) {
            $sql .= " AND e.agencia_id = :agencia";
            $params[':agencia'] = $filters->getAgencia();
        }

        if ($filters->getGerenteGestao() !== null) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM " . Tables::D_ESTRUTURA . " ggestao 
                WHERE ggestao.funcional = :gerente_gestao
                AND ggestao.cargo_id = " . Cargo::GERENTE_GESTAO . "
                AND ggestao.segmento_id = e.segmento_id
                AND ggestao.diretoria_id = e.diretoria_id
                AND ggestao.regional_id = e.regional_id
                AND ggestao.agencia_id = e.agencia_id
            )";
            $params[':gerente_gestao'] = $filters->getGerenteGestao();
        }

        if ($filters->getGerente() !== null) {
            $sql .= " AND v.funcional = :gerente";
            $params[':gerente'] = $filters->getGerente();
        }
        if ($filters->getDataInicio() !== null) {
            $sql .= " AND v.dt_atualizacao >= :dataInicio";
            $params[':dataInicio'] = $filters->getDataInicio();
        }
        if ($filters->getDataFim() !== null) {
            $sql .= " AND v.dt_atualizacao <= :dataFim";
            $params[':dataFim'] = $filters->getDataFim();
        }

        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Retorna a cláusula ORDER BY
     * @return string
     */
    protected function getOrderBy(): string
    {
        return "ORDER BY v.dt_atualizacao DESC";
    }

    /**
     * Mapeia um array de resultados para VariavelDTO
     * @param array $row
     * @return VariavelDTO
     */
    public function mapToDto(array $row): VariavelDTO
    {
        $dataIso = DateFormatter::toIsoDate($row['dt_atualizacao'] ?? null);

        return new VariavelDTO(
            (string)($row['id'] ?? null),                    // registroId
            $row['funcional'] ?? null,                        // funcional
            null,                                             // idIndicador
            null,                                             // dsIndicador
            null,                                             // familiaId
            null,                                             // familiaNome
            null,                                             // familiaCodigo
            null,                                             // indicadorCodigo
            null,                                             // subindicadorCodigo
            $dataIso,                                         // data
            $dataIso,                                         // competencia
            ValueFormatter::toFloat($row['variavel'] ?? null), // variavelReal
            ValueFormatter::toFloat($row['meta'] ?? null),     // variavelMeta
            $row['nome_funcional'] ?? null,                   // nomeFuncional
            $row['segmento'] ?? null,                          // segmento
            $row['segmento_id'] ?? null,                       // segmentoId
            $row['diretoria_nome'] ?? null,                    // diretoriaNome
            $row['diretoria_id'] ?? null,                      // diretoriaId
            $row['regional_nome'] ?? null,                     // regionalNome
            $row['gerencia_id'] ?? null,                       // gerenciaId
            $row['agencia_nome'] ?? null,                      // agenciaNome
            $row['agencia_id'] ?? null                         // agenciaId
        );
    }
}
