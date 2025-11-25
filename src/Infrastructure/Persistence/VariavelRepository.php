<?php

namespace App\Infrastructure\Persistence;

use PDO;
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
    /**
     * @param PDO $pdo
     */
    public function __construct(PDO $pdo)
    {
        parent::__construct($pdo, VariavelDTO::class);
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
                    COALESCE(e.segmento, '') AS segmento,
                    COALESCE(CAST(e.id_segmento AS CHAR), '') AS segmento_id,
                    COALESCE(e.diretoria, '') AS diretoria_nome,
                    COALESCE(CAST(e.id_diretoria AS CHAR), '') AS diretoria_id,
                    COALESCE(e.regional, '') AS regional_nome,
                    COALESCE(CAST(e.id_regional AS CHAR), '') AS gerencia_id,
                    COALESCE(e.agencia, '') AS agencia_nome,
                    COALESCE(CAST(e.id_agencia AS CHAR), '') AS agencia_id
                FROM " . Tables::F_VARIAVEL . " v
                LEFT JOIN " . Tables::D_ESTRUTURA . " e ON e.funcional = CAST(v.funcional AS CHAR)
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
            $sql .= " AND e.id_segmento = :segmento";
            $params[':segmento'] = $filters->getSegmento();
        }

        if ($filters->getDiretoria() !== null) {
            $sql .= " AND e.id_diretoria = :diretoria";
            $params[':diretoria'] = $filters->getDiretoria();
        }

        if ($filters->getRegional() !== null) {
            $sql .= " AND e.id_regional = :regional";
            $params[':regional'] = $filters->getRegional();
        }

        if ($filters->getAgencia() !== null) {
            $sql .= " AND e.id_agencia = :agencia";
            $params[':agencia'] = $filters->getAgencia();
        }

        if ($filters->getGerenteGestao() !== null) {
            $sql .= " AND EXISTS (
                SELECT 1 FROM " . Tables::D_ESTRUTURA . " ggestao 
                WHERE ggestao.funcional = :gerente_gestao
                AND ggestao.id_cargo = " . Cargo::GERENTE_GESTAO . "
                AND ggestao.id_segmento = e.id_segmento
                AND ggestao.id_diretoria = e.id_diretoria
                AND ggestao.id_regional = e.id_regional
                AND ggestao.id_agencia = e.id_agencia
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
