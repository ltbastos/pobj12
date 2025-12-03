<?php

namespace App\Application\UseCase\Omega;

use App\Repository\Omega\OmegaDepartamentoRepository;
use App\Repository\Omega\OmegaStatusRepository;
use App\Repository\Omega\OmegaUsuarioRepository;
use App\Repository\Omega\OmegaChamadoRepository;

/**
 * UseCase para inicialização do Omega
 */
class OmegaInitUseCase
{
    private $departamentoRepository;
    private $statusRepository;
    private $usuarioRepository;
    private $chamadoRepository;

    public function __construct(
        OmegaDepartamentoRepository $departamentoRepository,
        OmegaStatusRepository $statusRepository,
        OmegaUsuarioRepository $usuarioRepository,
        OmegaChamadoRepository $chamadoRepository
    ) {
        $this->departamentoRepository = $departamentoRepository;
        $this->statusRepository = $statusRepository;
        $this->usuarioRepository = $usuarioRepository;
        $this->chamadoRepository = $chamadoRepository;
    }

    /**
     * Retorna todos os dados necessários para inicializar o Omega
     * @return array
     */
    public function handle(): array
    {
        return [
            'structure' => $this->convertDtosToArray($this->departamentoRepository->findAllOrderedByNome()),
            'statuses' => $this->convertDtosToArray($this->statusRepository->findAllOrdered()),
            'users' => $this->convertDtosToArray($this->usuarioRepository->findAllOrderedByNome()),
            'tickets' => $this->convertDtosToArray($this->chamadoRepository->findAllOrderedByUpdated()),
        ];
    }

    /**
     * Converte DTOs/Entidades para arrays
     */
    private function convertDtosToArray(array $items): array
    {
        $result = array_map(function ($item) {
            return $this->entityToArray($item);
        }, $items);
        
        return array_values($result);
    }

    /**
     * Converte uma entidade para array
     */
    private function entityToArray($entity): array
    {
        if (method_exists($entity, 'toArray')) {
            return $entity->toArray();
        }

        // Conversão manual baseada no tipo de entidade
        $className = get_class($entity);
        
        if (strpos($className, 'OmegaDepartamento') !== false) {
            return [
                'departamento_id' => $entity->getDepartamentoId(),
                'departamento' => $entity->getDepartamento(),
                'ordem_departamento' => $entity->getOrdemDepartamento(),
                'tipo' => $entity->getTipo(),
                'ordem_tipo' => $entity->getOrdemTipo(),
            ];
        }
        
        if (strpos($className, 'OmegaStatus') !== false) {
            $departamento = $entity->getDepartamento();
            return [
                'id' => $entity->getId(),
                'label' => $entity->getLabel(),
                'tone' => $entity->getTone(),
                'descricao' => $entity->getDescricao(),
                'ordem' => $entity->getOrdem(),
                'departamento_id' => $departamento ? $departamento->getDepartamentoId() : null,
            ];
        }
        
        if (strpos($className, 'OmegaUsuario') !== false) {
            return [
                'id' => $entity->getId(),
                'nome' => $entity->getNome(),
                'funcional' => $entity->getFuncional(),
                'cargo' => $entity->getCargo(),
                'usuario' => $entity->getUsuario(),
                'analista' => $entity->getAnalista(),
                'supervisor' => $entity->getSupervisor(),
                'admin' => $entity->getAdmin(),
                'encarteiramento' => $entity->getEncarteiramento(),
                'meta' => $entity->getMeta(),
                'orcamento' => $entity->getOrcamento(),
                'pobj' => $entity->getPobj(),
                'matriz' => $entity->getMatriz(),
                'outros' => $entity->getOutros(),
            ];
        }
        
        if (strpos($className, 'OmegaChamado') !== false) {
            $status = $entity->getStatus();
            $requester = $entity->getRequester();
            $owner = $entity->getOwner();
            $team = $entity->getTeam();
            
            return [
                'id' => $entity->getId(),
                'subject' => $entity->getSubject(),
                'company' => $entity->getCompany(),
                'product_id' => $entity->getProductId(),
                'product_label' => $entity->getProductLabel(),
                'family' => $entity->getFamily(),
                'section' => $entity->getSection(),
                'queue' => $entity->getQueue(),
                'category' => $entity->getCategory(),
                'status' => $status ? $status->getId() : null,
                'priority' => $entity->getPriority(),
                'opened' => $entity->getOpened() ? $entity->getOpened()->format('Y-m-d H:i:s') : null,
                'updated' => $entity->getUpdated() ? $entity->getUpdated()->format('Y-m-d H:i:s') : null,
                'due_date' => $entity->getDueDate() ? $entity->getDueDate()->format('Y-m-d H:i:s') : null,
                'requester_id' => $requester ? $requester->getId() : null,
                'owner_id' => $owner ? $owner->getId() : null,
                'team_id' => $team ? $team->getDepartamentoId() : null,
                'history' => $entity->getHistory(),
                'diretoria' => $entity->getDiretoria(),
                'gerencia' => $entity->getGerencia(),
                'agencia' => $entity->getAgencia(),
                'gerente_gestao' => $entity->getGerenteGestao(),
                'gerente' => $entity->getGerente(),
                'credit' => $entity->getCredit(),
                'attachment' => $entity->getAttachment(),
            ];
        }
        
        // Fallback genérico
        return [];
    }
}



