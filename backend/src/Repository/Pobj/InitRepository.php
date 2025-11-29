<?php

namespace App\Repository\Pobj;

use App\Entity\Pobj\DEstrutura;
use App\Entity\Pobj\Agencia;
use App\Entity\Pobj\Regional;
use App\Entity\Pobj\Diretoria;
use App\Entity\Pobj\Segmento;
use App\Entity\Pobj\Familia;
use App\Entity\Pobj\Indicador;
use App\Entity\Pobj\Subindicador;
use App\Entity\Pobj\DStatusIndicador;
use App\Domain\Enum\Cargo;
use App\Domain\DTO\Init\GerenteWithGestorDTO;
use App\Domain\DTO\Init\GerenteGestaoWithAgenciaDTO;
use App\Domain\DTO\Init\AgenciaDTO;
use App\Domain\DTO\Init\RegionalDTO;
use App\Domain\DTO\Init\DiretoriaDTO;
use App\Domain\DTO\Init\SegmentoDTO;
use App\Domain\DTO\Init\FamiliaDTO;
use App\Domain\DTO\Init\IndicadorDTO;
use App\Domain\DTO\Init\SubindicadorDTO;
use App\Domain\DTO\Init\StatusIndicadorDTO;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class InitRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DEstrutura::class);
    }

    /**
     * @return GerenteGestaoWithAgenciaDTO[]
     */
    public function findGerentesGestoesWithAgencia(): array
    {
        $results = $this->createQueryBuilder('e')
                    ->select('e.id, e.funcional, e.nome, a.id as agencia_id')
                    ->innerJoin('e.cargo', 'c')
                    ->leftJoin('e.agencia', 'a')
                    ->andWhere('c.id = :cargo_id')
                    ->setParameter('cargo_id', Cargo::GERENTE_GESTAO)
                    ->getQuery()
                    ->getArrayResult();

        $dtos = [];
        foreach ($results as $result) {
            $dtos[] = new GerenteGestaoWithAgenciaDTO(
                $result['id'],
                $result['funcional'],
                $result['nome'],
                $result['agencia_id'] ?? null
            );
        }

        return $dtos;
    }

    /**
     * Busca gerentes com seus respectivos gerentes de gestão da mesma agência
     * @return GerenteWithGestorDTO[]
     */
    public function findGerentesWithGestor(): array
    {
        $gerentes = $this->createQueryBuilder('e')
            ->innerJoin('e.cargo', 'c')
            ->andWhere('c.id = :cargo_id')
            ->andWhere('e.funcional IS NOT NULL')
            ->andWhere('e.nome IS NOT NULL')
            ->setParameter('cargo_id', Cargo::GERENTE)
            ->orderBy('e.nome', 'ASC')
            ->getQuery()
            ->getResult();

        $result = [];
        foreach ($gerentes as $gerente) {
            $gestorId = null;

            if ($gerente->getAgencia()) {
                $gestor = $this->createQueryBuilder('g')
                    ->innerJoin('g.cargo', 'cg')
                    ->andWhere('cg.id = :cargo_gestao_id')
                    ->andWhere('g.agencia = :agencia')
                    ->andWhere('g.funcional IS NOT NULL')
                    ->setParameter('cargo_gestao_id', Cargo::GERENTE_GESTAO)
                    ->setParameter('agencia', $gerente->getAgencia())
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();

                $gestorId = $gestor ? $gestor->getId() : null;
            }

            $result[] = new GerenteWithGestorDTO(
                $gerente->getId(),
                $gerente->getFuncional(),
                $gerente->getNome(),
                $gestorId
            );
        }

        return $result;
    }


    /**
     * @return AgenciaDTO[]
     */
    public function findAgencias(): array
    {
        $results = $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('a.id, a.nome, r.id as regional_id')
                    ->from(Agencia::class, 'a')
                    ->leftJoin('a.regional', 'r')
                    ->getQuery()
                    ->getArrayResult();

        $dtos = [];
        foreach ($results as $result) {
            $dtos[] = new AgenciaDTO(
                $result['id'],
                $result['nome'],
                $result['regional_id'] ?? null
            );
        }

        return $dtos;
    }
    /**
     * @return RegionalDTO[]
     */
    public function findRegionais(): array
    {
        $results = $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('r.id, r.nome, d.id as diretoria_id')
                    ->from(Regional::class, 'r')
                    ->leftJoin('r.diretoria', 'd')
                    ->getQuery()
                    ->getArrayResult();

        $dtos = [];
        foreach ($results as $result) {
            $dtos[] = new RegionalDTO(
                $result['id'],
                $result['nome'],
                $result['diretoria_id'] ?? null
            );
        }

        return $dtos;
    }
    /**
     * @return DiretoriaDTO[]
     */
    public function findDiretorias(): array
    {
        $results = $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('d.id, d.nome, s.id as segmento_id')
                    ->from(Diretoria::class, 'd')
                    ->leftJoin('d.segmento', 's')
                    ->getQuery()
                    ->getArrayResult();

        $dtos = [];
        foreach ($results as $result) {
            $dtos[] = new DiretoriaDTO(
                $result['id'],
                $result['nome'],
                $result['segmento_id'] ?? null
            );
        }

        return $dtos;
    }
    /**
     * @return SegmentoDTO[]
     */
    public function findSegmentos(): array
    {
        $results = $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('s.id, s.nome')
                    ->from(Segmento::class, 's')
                    ->getQuery()
                    ->getArrayResult();

        $dtos = [];
        foreach ($results as $result) {
            $dtos[] = new SegmentoDTO(
                $result['id'],
                $result['nome']
            );
        }

        return $dtos;
    }
    /**
     * @return FamiliaDTO[]
     */
    public function findFamilias(): array
    {
        $results = $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('f.id, f.nmFamilia as nome')
                    ->from(Familia::class, 'f')
                    ->getQuery()
                    ->getArrayResult();

        $dtos = [];
        foreach ($results as $result) {
            $dtos[] = new FamiliaDTO(
                $result['id'],
                $result['nome']
            );
        }

        return $dtos;
    }
    /**
     * @return IndicadorDTO[]
     */
    public function findIndicadores(): array
    {
        $results = $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('i.id, i.nmIndicador as nome, f.id as familia_id')
                    ->from(Indicador::class, 'i')
                    ->leftJoin('i.familia', 'f')
                    ->getQuery()
                    ->getArrayResult();

        $dtos = [];
        foreach ($results as $result) {
            $dtos[] = new IndicadorDTO(
                $result['id'],
                $result['nome'],
                $result['familia_id'] ?? null
            );
        }

        return $dtos;
    }
    /**
     * @return SubindicadorDTO[]
     */
    public function findSubindicadores(): array
    {
        $results = $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('si.id, si.nmSubindicador as nome, i.id as indicador_id')
                    ->from(Subindicador::class, 'si')
                    ->leftJoin('si.indicador', 'i')
                    ->getQuery()
                    ->getArrayResult();

        $dtos = [];
        foreach ($results as $result) {
            $dtos[] = new SubindicadorDTO(
                $result['id'],
                $result['nome'],
                $result['indicador_id'] ?? null
            );
        }

        return $dtos;
    }
    /**
     * @return StatusIndicadorDTO[]
     */
    public function findStatusIndicadores(): array
    {
        $results = $this->getEntityManager()
                    ->createQueryBuilder()
                    ->select('s.id, s.status')
                    ->from(DStatusIndicador::class, 's')
                    ->getQuery()
                    ->getArrayResult();

        $dtos = [];
        foreach ($results as $result) {
            $dtos[] = new StatusIndicadorDTO(
                $result['id'],
                $result['status']
            );
        }

        return $dtos;
    }
}

