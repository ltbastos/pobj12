<?php

namespace App\Repository\Pobj;

use App\Domain\DTO\FilterDTO;
use App\Entity\Pobj\FDetalhes;
use App\Entity\Pobj\FRealizados;
use App\Entity\Pobj\DCalendario;
use App\Entity\Pobj\DEstrutura;
use App\Entity\Pobj\DProduto;
use App\Entity\Pobj\FMeta;
use App\Entity\Pobj\Segmento;
use App\Entity\Pobj\Diretoria;
use App\Entity\Pobj\Regional;
use App\Entity\Pobj\Agencia;
use App\Entity\Pobj\Familia;
use App\Entity\Pobj\Indicador;
use App\Entity\Pobj\Subindicador;
use App\Domain\Enum\Cargo;
use App\Domain\DTO\Detalhes\DetalhesItemDTO;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FDetalhesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FDetalhes::class);
    }

    /**
     * Retorna o nome da tabela de uma entidade
     */
    private function getTableName(string $entityClass): string
    {
        return $this->getEntityManager()
            ->getClassMetadata($entityClass)
            ->getTableName();
    }

    /**
     * Busca detalhes com filtros opcionais, baseado no backend antigo
     */
    public function findDetalhes(?FilterDTO $filters = null): array
    {
        $fRealizadosTable = $this->getTableName(FRealizados::class);
        $dCalendarioTable = $this->getTableName(DCalendario::class);
        $dEstruturaTable = $this->getTableName(DEstrutura::class);
        $dProdutosTable = $this->getTableName(DProduto::class);
        $fMetaTable = $this->getTableName(FMeta::class);
        $fDetalhesTable = $this->getTableName(FDetalhes::class);
        $segmentoTable = $this->getTableName(Segmento::class);
        $diretoriaTable = $this->getTableName(Diretoria::class);
        $regionalTable = $this->getTableName(Regional::class);
        $agenciaTable = $this->getTableName(Agencia::class);
        $familiaTable = $this->getTableName(Familia::class);
        $indicadorTable = $this->getTableName(Indicador::class);
        $subindicadorTable = $this->getTableName(Subindicador::class);

        $params = [];
        $whereClause = '';
        
        // Adiciona os parâmetros do cargo para o CASE e subqueries (precisa estar antes da query)
        $params['cargoGerente'] = Cargo::GERENTE;
        $params['cargoGerenteGestao'] = Cargo::GERENTE_GESTAO;

        if ($filters) {
            // Filtros de estrutura (aplica apenas o mais específico da hierarquia)
            // Hierarquia: gerente > gerenteGestao > agencia > regional > diretoria > segmento
            $gerente = $filters->getGerente();
            $gerenteGestao = $filters->getGerenteGestao();
            $agencia = $filters->getAgencia();
            $regional = $filters->getRegional();
            $diretoria = $filters->getDiretoria();
            $segmento = $filters->getSegmento();

            // Se tiver gerente, filtra apenas por funcional
            if ($gerente !== null && $gerente !== '') {
                $whereClause .= " AND fr.funcional = :gerenteFuncional";
                $params['gerenteFuncional'] = $gerente;
            } elseif ($gerenteGestao !== null && $gerenteGestao !== '') {
                // Se tiver gerente gestão, filtra por todos os gerentes da mesma estrutura
                // (mesma agência, regional, diretoria e segmento)
                $dEstruturaTable = $this->getTableName(DEstrutura::class);
                $whereClause .= " AND EXISTS (
                    SELECT 1 FROM {$dEstruturaTable} AS ggestao 
                    WHERE ggestao.funcional = :gerenteGestaoFuncional
                    AND ggestao.cargo_id = :cargoGerenteGestao
                    AND ggestao.segmento_id = est.segmento_id
                    AND ggestao.diretoria_id = est.diretoria_id
                    AND ggestao.regional_id = est.regional_id
                    AND ggestao.agencia_id = est.agencia_id
                )";
                $params['gerenteGestaoFuncional'] = $gerenteGestao;
                $params['cargoGerenteGestao'] = Cargo::GERENTE_GESTAO;
            } else {
                // Aplica apenas o filtro mais específico da hierarquia de estrutura
                if ($agencia !== null && $agencia !== '') {
                    $whereClause .= " AND est.agencia_id = :agencia";
                    $params['agencia'] = $agencia;
                } elseif ($regional !== null && $regional !== '') {
                    $whereClause .= " AND est.regional_id = :regional";
                    $params['regional'] = $regional;
                } elseif ($diretoria !== null && $diretoria !== '') {
                    $whereClause .= " AND est.diretoria_id = :diretoria";
                    $params['diretoria'] = $diretoria;
                } elseif ($segmento !== null && $segmento !== '') {
                    $whereClause .= " AND est.segmento_id = :segmento";
                    $params['segmento'] = $segmento;
                }
            }

            // Filtros de produto
            $familia = $filters->getFamilia();
            $indicador = $filters->getIndicador();
            $subindicador = $filters->getSubindicador();

            if ($subindicador !== null && $subindicador !== '') {
                $whereClause .= " AND prod.subindicador_id = :subindicador";
                $params['subindicador'] = $subindicador;
            } elseif ($indicador !== null && $indicador !== '') {
                $whereClause .= " AND prod.indicador_id = :indicador";
                $params['indicador'] = $indicador;
            } elseif ($familia !== null && $familia !== '') {
                $whereClause .= " AND prod.familia_id = :familia";
                $params['familia'] = $familia;
            }

            // Filtros de data
            $dataInicio = $filters->getDataInicio();
            $dataFim = $filters->getDataFim();

            if ($dataInicio !== null && $dataInicio !== '') {
                $whereClause .= " AND fr.data_realizado >= :dataInicio";
                $params['dataInicio'] = $dataInicio;
            }

            if ($dataFim !== null && $dataFim !== '') {
                $whereClause .= " AND fr.data_realizado <= :dataFim";
                $params['dataFim'] = $dataFim;
            }
        }

        $sql = "SELECT
                    COALESCE(det.registro_id, fr.id_contrato) AS registro_id,
                    COALESCE(cal_comp.data, fr.data_realizado) AS data,
                    COALESCE(cal_comp.data, fr.data_realizado) AS competencia,
                    cal.ano,
                    cal.mes,
                    cal.mes_nome,
                    est.segmento_id,
                    seg.nome AS segmento,
                    est.diretoria_id,
                    dir.nome AS diretoria_nome,
                    est.regional_id AS gerencia_id,
                    reg.nome AS gerencia_nome,
                    est.agencia_id,
                    ag.nome AS agencia_nome,
                    CASE 
                        WHEN est.cargo_id = :cargoGerente THEN fr.funcional
                        WHEN est.cargo_id = :cargoGerenteGestao THEN NULL
                        ELSE NULL
                    END AS gerente_id,
                    CASE 
                        WHEN est.cargo_id = :cargoGerente THEN est.nome
                        WHEN est.cargo_id = :cargoGerenteGestao THEN NULL
                        ELSE NULL
                    END AS gerente_nome,
                    CASE 
                        WHEN est.cargo_id = :cargoGerente THEN ggestao.funcional
                        WHEN est.cargo_id = :cargoGerenteGestao THEN fr.funcional
                        ELSE NULL
                    END AS gerente_gestao_id,
                    CASE 
                        WHEN est.cargo_id = :cargoGerente THEN ggestao.nome
                        WHEN est.cargo_id = :cargoGerenteGestao THEN est.nome
                        ELSE NULL
                    END AS gerente_gestao_nome,
                    prod.familia_id,
                    fam.nm_familia AS familia_nome,
                    prod.indicador_id AS id_indicador,
                    ind.nm_indicador AS ds_indicador,
                    prod.subindicador_id AS id_subindicador,
                    sub.nm_subindicador AS subindicador,
                    prod.peso,
                    fr.realizado AS valor_realizado,
                    meta.meta_mensal AS valor_meta,
                    meta.meta_mensal AS meta_mensal,
                    det.canal_venda,
                    det.tipo_venda,
                    det.condicao_pagamento AS modalidade_pagamento,
                    det.dt_vencimento,
                    det.dt_cancelamento,
                    det.motivo_cancelamento,
                    det.status_id
                FROM {$fRealizadosTable} AS fr
                JOIN {$dCalendarioTable} AS cal
                    ON cal.data = fr.data_realizado
                JOIN {$dEstruturaTable} AS est
                    ON est.funcional = fr.funcional
                JOIN {$dProdutosTable} AS prod
                    ON prod.id = fr.produto_id
                LEFT JOIN {$segmentoTable} AS seg
                    ON seg.id = est.segmento_id
                LEFT JOIN {$diretoriaTable} AS dir
                    ON dir.id = est.diretoria_id
                LEFT JOIN {$regionalTable} AS reg
                    ON reg.id = est.regional_id
                LEFT JOIN {$agenciaTable} AS ag
                    ON ag.id = est.agencia_id
                LEFT JOIN (
                    SELECT 
                        g1.agencia_id,
                        g1.funcional,
                        g1.nome
                    FROM {$dEstruturaTable} AS g1
                    INNER JOIN (
                        SELECT agencia_id, MIN(id) AS min_id
                        FROM {$dEstruturaTable}
                        WHERE cargo_id = :cargoGerenteGestao
                        AND agencia_id IS NOT NULL
                        GROUP BY agencia_id
                    ) AS g2 ON g1.id = g2.min_id AND g1.agencia_id = g2.agencia_id
                    WHERE g1.cargo_id = :cargoGerenteGestao
                ) AS ggestao
                    ON ggestao.agencia_id = est.agencia_id
                LEFT JOIN {$familiaTable} AS fam
                    ON fam.id = prod.familia_id
                LEFT JOIN {$indicadorTable} AS ind
                    ON ind.id = prod.indicador_id
                LEFT JOIN {$subindicadorTable} AS sub
                    ON sub.id = prod.subindicador_id
                LEFT JOIN {$fMetaTable} AS meta
                    ON meta.funcional = fr.funcional
                    AND meta.produto_id = fr.produto_id
                    AND YEAR(meta.data_meta) = cal.ano
                    AND MONTH(meta.data_meta) = cal.mes
                LEFT JOIN {$fDetalhesTable} AS det
                    ON det.contrato_id = fr.id_contrato
                LEFT JOIN {$dCalendarioTable} AS cal_comp
                    ON cal_comp.data = det.competencia
                WHERE 1=1 {$whereClause}
                ORDER BY est.diretoria_id, est.regional_id, est.agencia_id, est.nome, prod.familia_id, prod.indicador_id, prod.subindicador_id, fr.id_contrato";

        $connection = $this->getEntityManager()->getConnection();
        $result = $connection->executeQuery($sql, $params);
        $rows = $result->fetchAllAssociative();

        // Formata os dados usando DTO
        $detalhes = [];
        foreach ($rows as $row) {
            // Formata datas
            $data = $row['data'] ?? null;
            if ($data instanceof \DateTimeInterface) {
                $data = $data->format('Y-m-d');
            } elseif (!is_string($data) || $data === '') {
                $data = null;
            }

            $competencia = $row['competencia'] ?? null;
            if ($competencia instanceof \DateTimeInterface) {
                $competencia = $competencia->format('Y-m-d');
            } elseif (!is_string($competencia) || $competencia === '') {
                $competencia = null;
            }

            $dtVencimento = $row['dt_vencimento'] ?? null;
            if ($dtVencimento instanceof \DateTimeInterface) {
                $dtVencimento = $dtVencimento->format('Y-m-d');
            } elseif (!is_string($dtVencimento) || $dtVencimento === '') {
                $dtVencimento = null;
            }

            $dtCancelamento = $row['dt_cancelamento'] ?? null;
            if ($dtCancelamento instanceof \DateTimeInterface) {
                $dtCancelamento = $dtCancelamento->format('Y-m-d');
            } elseif (!is_string($dtCancelamento) || $dtCancelamento === '') {
                $dtCancelamento = null;
            }

            $dto = new DetalhesItemDTO(
                $row['registro_id'] ?? null,
                $row['registro_id'] ?? null,
                $data,
                $competencia,
                $row['ano'] ?? null,
                $row['mes'] ?? null,
                $row['mes_nome'] ?? null,
                $row['segmento_id'] ? (string)$row['segmento_id'] : null,
                $row['segmento'] ?? null,
                $row['diretoria_id'] ? (string)$row['diretoria_id'] : null,
                $row['diretoria_nome'] ?? null,
                $row['gerencia_id'] ? (string)$row['gerencia_id'] : null,
                $row['gerencia_nome'] ?? null,
                $row['agencia_id'] ? (string)$row['agencia_id'] : null,
                $row['agencia_nome'] ?? null,
                $row['gerente_id'] ?? null,
                $row['gerente_nome'] ?? null,
                $row['gerente_gestao_id'] ?? null,
                $row['gerente_gestao_nome'] ?? null,
                $row['familia_id'] ? (string)$row['familia_id'] : null,
                $row['familia_nome'] ?? null,
                $row['id_indicador'] ? (string)$row['id_indicador'] : null,
                $row['ds_indicador'] ?? null,
                $row['id_subindicador'] ? (string)$row['id_subindicador'] : null,
                $row['subindicador'] ?? null,
                $row['peso'] !== null ? (float)$row['peso'] : null,
                $row['valor_realizado'] !== null ? (float)$row['valor_realizado'] : null,
                $row['valor_meta'] !== null ? (float)$row['valor_meta'] : null,
                $row['meta_mensal'] !== null ? (float)$row['meta_mensal'] : null,
                $row['canal_venda'] ?? null,
                $row['tipo_venda'] ?? null,
                $row['modalidade_pagamento'] ?? null,
                $dtVencimento,
                $dtCancelamento,
                $row['motivo_cancelamento'] ?? null,
                $row['status_id'] !== null ? (int)$row['status_id'] : null
            );

            $detalhes[] = $dto->toArray();
        }

        return $detalhes;
    }
}

