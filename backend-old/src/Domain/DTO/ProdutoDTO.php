<?php

namespace App\Domain\DTO;

use App\Domain\Entity\DProduto;
class ProdutoDTO extends BaseFactDTO
{
    private $id;
    private $idFamilia;
    private $familia;
    private $idIndicador;
    private $indicador;
    private $idSubindicador;
    private $subindicador;
    private $metrica;
    private $peso;

    public function __construct($id = null, $idFamilia = null, $familia = null, $idIndicador = null, $indicador = null, $idSubindicador = null, $subindicador = null, $metrica = null, $peso = null)
    {
        $this->id = $id;
        $this->idFamilia = $idFamilia;
        $this->familia = $familia;
        $this->idIndicador = $idIndicador;
        $this->indicador = $indicador;
        $this->idSubindicador = $idSubindicador;
        $this->subindicador = $subindicador;
        $this->metrica = $metrica;
        $this->peso = $peso;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'id_familia' => $this->idFamilia,
            'familia' => $this->familia,
            'id_indicador' => $this->idIndicador,
            'indicador' => $this->indicador,
            'id_subindicador' => $this->idSubindicador,
            'subindicador' => $this->subindicador,
            'metrica' => $this->metrica,
            'peso' => $this->peso,
        ];
    }

    public static function fromEntity(DProduto $entity): self
    {
        $peso = ($entity->getPeso() !== null && $entity->getPeso() !== '' && is_numeric($entity->getPeso())) 
            ? (float)$entity->getPeso() 
            : null;
        
        return new self(
            $entity->getId(),
            $entity->getIdFamilia() !== null ? (string)$entity->getIdFamilia() : null,
            $entity->getFamilia() ?? '',
            $entity->getIdIndicador() !== null ? (string)$entity->getIdIndicador() : null,
            $entity->getIndicador() ?? '',
            $entity->getIdSubindicador() !== null ? (string)$entity->getIdSubindicador() : null,
            $entity->getSubindicador() ?? '',
            $entity->getMetrica() ?? '',
            $entity->getPeso() ?? null
        );
    }

    public static function fromRows(array $rows): array
    {
        return array_map(function ($produto) {
            $peso = ($produto['peso'] !== null && $produto['peso'] !== '' && is_numeric($produto['peso'])) 
                ? (float)$produto['peso'] 
                : null;
            
            return (new self(
                $produto['id'] ?? null,
                $produto['id_familia'] !== null ? (string)$produto['id_familia'] : null,
                $produto['familia'] ?? '',
                $produto['id_indicador'] !== null ? (string)$produto['id_indicador'] : null,
                $produto['indicador'] ?? '',
                $produto['id_subindicador'] !== null ? (string)$produto['id_subindicador'] : null,
                $produto['subindicador'] ?? '',
                $produto['metrica'] ?? null,
                $peso
            ))->toArray();
        }, $rows);
    }
}

