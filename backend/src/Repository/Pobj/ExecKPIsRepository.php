<?php

namespace App\Repository\Pobj;

use App\Domain\DTO\FilterDTO;
use App\Entity\Pobj\FRealizados;
use App\Entity\Pobj\FMeta;
use App\Entity\Pobj\DEstrutura;
use App\Repository\Pobj\Helper\ExecFilterBuilder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class ExecKPIsRepository extends ServiceEntityRepository
{
    private $filterBuilder;

    public function __construct(ManagerRegistry $registry, ExecFilterBuilder $filterBuilder)
    {
        parent::__construct($registry, FRealizados::class);
        $this->filterBuilder = $filterBuilder;
    }

    public function findKPIs(?FilterDTO $filters = null): array
    {
        $fRealizadosTable = $this->getTableName(FRealizados::class);
        $fMetaTable = $this->getTableName(FMeta::class);
        $dEstruturaTable = $this->getTableName(DEstrutura::class);

        $params = [];
        $whereClause = $this->filterBuilder->buildWhereClause(
            $filters,
            $params,
            false,
            [
                'realizado' => 'r',
                'meta' => 'm'
            ]
        );

        $today = new \DateTime();
        $dataInicio = $filters ? $filters->getDataInicio() : null;
        $dataFim = $filters ? $filters->getDataFim() : null;

        if ($dataInicio && $dataFim) {
            $dateFilterRealizados = " AND r.data_realizado >= :dataInicio AND r.data_realizado <= :dataFim";
            $dateFilterMeta = " AND m.data_meta >= :dataInicio AND m.data_meta <= :dataFim";
            $params['dataInicio'] = $dataInicio;
            $params['dataFim'] = $dataFim;
        } else {
            $currentYear = $today->format('Y');
            $currentMonthNum = (int)$today->format('m');
            $dateFilterRealizados = " AND YEAR(r.data_realizado) = :ano AND MONTH(r.data_realizado) = :mes";
            $dateFilterMeta = " AND YEAR(m.data_meta) = :ano AND MONTH(m.data_meta) = :mes";
            $params['ano'] = $currentYear;
            $params['mes'] = $currentMonthNum;
        }

        if ($dataInicio && $dataFim) {
            $dateFilterAcum = " AND r.data_realizado >= :dataInicio AND r.data_realizado <= :dataFim";
            $dateFilterMetaAcum = " AND m.data_meta >= :dataInicio AND m.data_meta <= :dataFim";
        } else {
            $dateFilterAcum = " AND YEAR(r.data_realizado) = :ano AND MONTH(r.data_realizado) <= :mes";
            $dateFilterMetaAcum = " AND YEAR(m.data_meta) = :ano AND MONTH(m.data_meta) <= :mes";
        }

        $sqlRealMens = "SELECT COALESCE(SUM(r.realizado), 0) as total
            FROM {$fRealizadosTable} AS r
            INNER JOIN {$dEstruturaTable} AS est ON est.funcional = r.funcional
            WHERE 1=1 {$dateFilterRealizados} {$whereClause}";

        $sqlMetaMens = "SELECT COALESCE(SUM(m.meta_mensal), 0) as total
            FROM {$fMetaTable} AS m
            INNER JOIN {$dEstruturaTable} AS est ON est.funcional = m.funcional
            WHERE 1=1 {$dateFilterMeta} {$whereClause}";

        $sqlRealAcum = "SELECT COALESCE(SUM(r.realizado), 0) as total
            FROM {$fRealizadosTable} AS r
            INNER JOIN {$dEstruturaTable} AS est ON est.funcional = r.funcional
            WHERE 1=1 {$dateFilterAcum} {$whereClause}";

        $sqlMetaAcum = "SELECT COALESCE(SUM(m.meta_mensal), 0) as total
            FROM {$fMetaTable} AS m
            INNER JOIN {$dEstruturaTable} AS est ON est.funcional = m.funcional
            WHERE 1=1 {$dateFilterMetaAcum} {$whereClause}";

        $conn = $this->getEntityManager()->getConnection();

        $realMens = $conn->executeQuery($sqlRealMens, $params)->fetchOne();
        $metaMens = $conn->executeQuery($sqlMetaMens, $params)->fetchOne();
        $realAcum = $conn->executeQuery($sqlRealAcum, $params)->fetchOne();
        $metaAcum = $conn->executeQuery($sqlMetaAcum, $params)->fetchOne();

        return [
            'real_mens' => (float)($realMens ?? 0),
            'meta_mens' => (float)($metaMens ?? 0),
            'real_acum' => (float)($realAcum ?? 0),
            'meta_acum' => (float)($metaAcum ?? 0)
        ];
    }

    private function getTableName(string $entityClass): string
    {
        return $this->getEntityManager()
            ->getClassMetadata($entityClass)
            ->getTableName();
    }
}
