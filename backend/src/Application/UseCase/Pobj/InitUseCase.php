<?php

namespace App\Application\UseCase\Pobj;

use App\Repository\Pobj\InitRepository;

class InitUseCase
{
    private $initRepository;

    public function __construct(
        InitRepository $initRepository
    ) {
        $this->initRepository = $initRepository;
    }

    public function handle(): array
    {
        return [
            'segmentos'       => $this->convertDtosToArray($this->initRepository->findSegmentos()),
            'diretorias'      => $this->convertDtosToArray($this->initRepository->findDiretorias()),
            'regionais'       => $this->convertDtosToArray($this->initRepository->findRegionais()),
            'agencias'        => $this->convertDtosToArray($this->initRepository->findAgencias()),
            'gerentes_gestao' => $this->convertDtosToArray($this->initRepository->findGerentesGestoesWithAgencia()),
            'gerentes'        => $this->convertDtosToArray($this->initRepository->findGerentesWithGestor()),
            'familias'        => $this->convertDtosToArray($this->initRepository->findFamilias()),
            'indicadores'     => $this->convertDtosToArray($this->initRepository->findIndicadores()),
            'subindicadores'  => $this->convertDtosToArray($this->initRepository->findSubindicadores()),
            'status_indicadores' => $this->convertDtosToArray($this->initRepository->findStatusIndicadores()),
        ];
    }

    /**
     * Converte array de DTOs para array de arrays
     * @param array $dtos
     * @return array
     */
    private function convertDtosToArray(array $dtos): array
    {
        $result = array_map(function ($dto) {
            return method_exists($dto, 'toArray') ? $dto->toArray() : $dto;
        }, $dtos);
        
        // Garante que retorna um array indexado numericamente (não associativo)
        return array_values($result);
    }
}


