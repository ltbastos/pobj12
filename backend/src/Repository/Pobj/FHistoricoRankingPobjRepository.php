<?php

namespace App\Repository\Pobj;

use App\Domain\DTO\FilterDTO;
use App\Domain\Enum\Cargo;
use App\Entity\Pobj\FPontos;
use App\Entity\Pobj\DEstrutura;
use App\Entity\Pobj\DCalendario;
use App\Entity\Pobj\Segmento;
use App\Entity\Pobj\Diretoria;
use App\Entity\Pobj\Regional;
use App\Entity\Pobj\Agencia;
use App\Entity\Pobj\Grupo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class FHistoricoRankingPobjRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FPontos::class);
    }

    
    private function getTableName(string $entityClass): string
    {
        return $this->getEntityManager()
            ->getClassMetadata($entityClass)
            ->getTableName();
    }

    
    public function findAllOrderedByRanking(): array
    {
        $fPontosTable = $this->getTableName(FPontos::class);
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = "SELECT 
                    funcional,
                    SUM(realizado) as total_pontos
                FROM {$fPontosTable}
                GROUP BY funcional
                ORDER BY total_pontos DESC";
        
        $result = $conn->executeQuery($sql);
        return $result->fetchAllAssociative();
    }

    
    public function findRankingWithFilters(?FilterDTO $filters = null): array
    {
                $fPontosTable = $this->getTableName(FPontos::class);
        $conn = $this->getEntityManager()->getConnection();
        $countResult = $conn->executeQuery("SELECT COUNT(*) as total FROM {$fPontosTable}");
        $count = $countResult->fetchOne();
        
                if ($count == 0) {
            return [];
        }
        
        $fPontosTable = $this->getTableName(FPontos::class);
        $estruturaTable = $this->getTableName(DEstrutura::class);
        $calendarioTable = $this->getTableName(DCalendario::class);
        $segmentoTable = $this->getTableName(Segmento::class);
        $diretoriaTable = $this->getTableName(Diretoria::class);
        $regionalTable = $this->getTableName(Regional::class);
        $agenciaTable = $this->getTableName(Agencia::class);
        $grupoTable = $this->getTableName(Grupo::class);

                $params = [
            'cargoGerente' => Cargo::GERENTE,
            'cargoGerenteGestao' => Cargo::GERENTE_GESTAO
        ];
        $whereClause = '';
        $userGrupo = null;

        if ($filters) {
                                                            
            $dataInicio = $filters->getDataInicio();
            $dataFim = $filters->getDataFim();

            if ($dataInicio !== null && $dataInicio !== '') {
                $whereClause .= " AND p.data_realizado >= :dataInicio";
                $params['dataInicio'] = $dataInicio;
            }

            if ($dataFim !== null && $dataFim !== '') {
                $whereClause .= " AND p.data_realizado <= :dataFim";
                $params['dataFim'] = $dataFim;
            }

            // Verifica se há um filtro de grupo explícito
            $grupoFilterValue = $filters->getGrupo();
            if ($grupoFilterValue !== null && $grupoFilterValue !== '') {
                // Se o filtro for numérico, assume que é o ID do grupo
                if (is_numeric($grupoFilterValue)) {
                    $userGrupo = (int)$grupoFilterValue;
                } else {
                    // Se for string, busca o ID do grupo pelo nome
                    $conn = $this->getEntityManager()->getConnection();
                    $grupoQuery = "SELECT id FROM {$grupoTable} WHERE nome = :grupoNome LIMIT 1";
                    $grupoResult = $conn->executeQuery($grupoQuery, ['grupoNome' => $grupoFilterValue]);
                    $grupoRow = $grupoResult->fetchAssociative();
                    $grupoResult->free();
                    if ($grupoRow && isset($grupoRow['id'])) {
                        $userGrupo = (int)$grupoRow['id'];
                    }
                }
            } else {
                // Obtém o grupo do usuário atual se o funcional foi fornecido
                $userFuncional = $filters->get('userFuncional');
                if ($userFuncional) {
                    $conn = $this->getEntityManager()->getConnection();
                    $userGrupoQuery = "SELECT grupo_id FROM {$estruturaTable} WHERE funcional = :userFuncional LIMIT 1";
                    $userGrupoResult = $conn->executeQuery($userGrupoQuery, ['userFuncional' => $userFuncional]);
                    $userGrupoRow = $userGrupoResult->fetchAssociative();
                    $userGrupoResult->free();
                    if ($userGrupoRow && isset($userGrupoRow['grupo_id']) && $userGrupoRow['grupo_id'] !== null) {
                        $userGrupo = $userGrupoRow['grupo_id'];
                    }
                }
            }
        }

                // Adiciona filtro por grupo se houver um grupo definido
                $grupoFilter = '';
                if ($userGrupo !== null) {
                    $grupoFilter = " AND est.grupo_id = :userGrupo";
                    $params['userGrupo'] = $userGrupo;
                }

                $subquery = "SELECT
                    MAX(p.data_realizado) AS data,
                    DATE_FORMAT(MAX(p.data_realizado), '%Y-%m') AS competencia,
                    CAST(seg.id AS CHAR) AS segmento_id,
                    seg.nome AS segmento,
                    CAST(dir.id AS CHAR) AS diretoria_id,
                    dir.nome AS diretoria_nome,
                    CAST(reg.id AS CHAR) AS gerencia_id,
                    reg.nome AS gerencia_nome,
                    CAST(ag.id AS CHAR) AS agencia_id,
                    ag.nome AS agencia_nome,
                    CAST(grupo.id AS CHAR) AS grupo_id,
                    grupo.nome AS grupo,
                    CASE 
                        WHEN est.cargo_id = :cargoGerente THEN est.funcional
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
                        WHEN est.cargo_id = :cargoGerenteGestao THEN est.funcional
                        ELSE NULL
                    END AS gerente_gestao_id,
                    CASE 
                        WHEN est.cargo_id = :cargoGerente THEN CAST(ggestao.id AS CHAR)
                        WHEN est.cargo_id = :cargoGerenteGestao THEN CAST(est.id AS CHAR)
                        ELSE NULL
                    END AS gerente_gestao_id_num,
                    CASE 
                        WHEN est.cargo_id = :cargoGerente THEN ggestao.nome
                        WHEN est.cargo_id = :cargoGerenteGestao THEN est.nome
                        ELSE NULL
                    END AS gerente_gestao_nome,
                    SUM(p.realizado) AS realizado_mensal,
                    SUM(p.meta) AS meta_mensal,
                    SUM(p.realizado) AS pontos
                FROM {$fPontosTable} AS p
                INNER JOIN {$estruturaTable} AS est
                    ON est.funcional = p.funcional
                LEFT JOIN {$segmentoTable} AS seg
                    ON seg.id = est.segmento_id
                LEFT JOIN {$diretoriaTable} AS dir
                    ON dir.id = est.diretoria_id
                LEFT JOIN {$regionalTable} AS reg
                    ON reg.id = est.regional_id
                LEFT JOIN {$agenciaTable} AS ag
                    ON ag.id = est.agencia_id
                LEFT JOIN {$grupoTable} AS grupo
                    ON grupo.id = est.grupo_id
                LEFT JOIN (
                    SELECT
                        g1.id,
                        g1.agencia_id,
                        g1.funcional,
                        g1.nome
                    FROM {$estruturaTable} AS g1
                    INNER JOIN (
                        SELECT agencia_id, MIN(id) AS min_id
                        FROM {$estruturaTable}
                        WHERE cargo_id = :cargoGerenteGestao
                        AND agencia_id IS NOT NULL
                        GROUP BY agencia_id
                    ) AS g2 ON g1.id = g2.min_id AND g1.agencia_id = g2.agencia_id
                    WHERE g1.cargo_id = :cargoGerenteGestao
                ) AS ggestao
                    ON ggestao.agencia_id = est.agencia_id
                WHERE 1=1 {$whereClause} {$grupoFilter}
                GROUP BY est.funcional, seg.id, dir.id, reg.id, ag.id, est.cargo_id, est.grupo_id, grupo.id, grupo.nome, ggestao.funcional, ggestao.nome, ggestao.id, est.id";

                        $sql = "SELECT 
                    ranked.*,
                    @rank := CASE 
                        WHEN @prev_realizado = ranked.realizado_mensal THEN @rank
                        ELSE @rank + 1
                    END AS ranking_mensal,
                    @prev_realizado := ranked.realizado_mensal AS _prev
                FROM (
                    {$subquery}
                ) AS ranked
                CROSS JOIN (SELECT @rank := 0, @prev_realizado := NULL) AS r
                ORDER BY ranked.realizado_mensal DESC, ranked.pontos DESC";

        $conn = $this->getEntityManager()->getConnection();
        $result = $conn->executeQuery($sql, $params);
        
                $formatted = [];
        while ($row = $result->fetchAssociative()) {
            $formatted[] = [
                'data' => $row['data'] ? (new \DateTime($row['data']))->format('Y-m-d') : null,
                'competencia' => $row['competencia'] ?? null,
                'segmento_id' => $row['segmento_id'] ?? null,
                'segmento' => $row['segmento'] ?? null,
                'diretoria_id' => $row['diretoria_id'] ?? null,
                'diretoria_nome' => $row['diretoria_nome'] ?? null,
                'gerencia_id' => $row['gerencia_id'] ?? null,
                'gerencia_nome' => $row['gerencia_nome'] ?? null,
                'agencia_id' => $row['agencia_id'] ?? null,
                'agencia_nome' => $row['agencia_nome'] ?? null,
                'grupo_id' => $row['grupo_id'] ?? null,
                'grupo' => $row['grupo'] ?? null,
                'gerente_gestao_id' => $row['gerente_gestao_id'] ?? null,
                'gerente_gestao_id_num' => $row['gerente_gestao_id_num'] ?? null,
                'gerente_gestao_nome' => $row['gerente_gestao_nome'] ?? null,
                'gerente_id' => $row['gerente_id'] ?? null,
                'gerente_nome' => $row['gerente_nome'] ?? null,
                'rank' => $row['ranking_mensal'] !== null ? (int)$row['ranking_mensal'] : null,                 'realizado_mensal' => $row['realizado_mensal'] !== null ? (float)$row['realizado_mensal'] : null,
                'meta_mensal' => $row['meta_mensal'] !== null ? (float)$row['meta_mensal'] : null,
                'pontos' => $row['pontos'] !== null ? (float)$row['pontos'] : null,
            ];
        }
        $result->free();

        return $formatted;
    }

    
    public function getFuncionalFromIdOrFuncional($idOrFuncional, int $cargoId): ?string
    {
        if ($idOrFuncional === null || $idOrFuncional === '') {
            return null;
        }

                if (!is_numeric($idOrFuncional)) {
            return (string)$idOrFuncional;
        }

                $dEstruturaTable = $this->getTableName(DEstrutura::class);
        $conn = $this->getEntityManager()->getConnection();
        
        $sql = "SELECT funcional FROM {$dEstruturaTable} 
                WHERE id = :id AND cargo_id = :cargoId 
                LIMIT 1";
        
        $result = $conn->executeQuery($sql, [
            'id' => (int)$idOrFuncional,
            'cargoId' => $cargoId
        ]);
        
        $row = $result->fetchAssociative();
        $result->free();
        
        return $row ? ($row['funcional'] ?? null) : null;
    }
}

