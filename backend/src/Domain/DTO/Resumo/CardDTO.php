<?php

namespace App\Domain\DTO\Resumo;

class CardDTO
{
    public $id;
    public $id_familia;
    public $familia;
    public $id_indicador;
    public $indicador;
    public $id_subindicador;
    public $subindicador;
    public $metrica;
    public $peso;
    public $meta;
    public $realizado;
    public $pontos;
    public $pontos_meta;
    public $ating;
    public $atingido;
    public $ultima_atualizacao;

    public function __construct(
        string $id,
        string $id_familia,
        string $familia,
        string $id_indicador,
        string $indicador,
        ?string $id_subindicador = null,
        ?string $subindicador = null,
        string $metrica = 'valor',
        float $peso = 0,
        float $meta = 0,
        float $realizado = 0,
        float $pontos = 0,
        float $pontos_meta = 0,
        float $ating = 0,
        bool $atingido = false,
        ?string $ultima_atualizacao = null
    ) {
        $this->id = $id;
        $this->id_familia = $id_familia;
        $this->familia = $familia;
        $this->id_indicador = $id_indicador;
        $this->indicador = $indicador;
        $this->id_subindicador = $id_subindicador;
        $this->subindicador = $subindicador;
        $this->metrica = $metrica;
        $this->peso = $peso;
        $this->meta = $meta;
        $this->realizado = $realizado;
        $this->pontos = $pontos;
        $this->pontos_meta = $pontos_meta;
        $this->ating = $ating;
        $this->atingido = $atingido;
        $this->ultima_atualizacao = $ultima_atualizacao;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'id_familia' => $this->id_familia,
            'familia' => $this->familia,
            'id_indicador' => $this->id_indicador,
            'indicador' => $this->indicador,
            'id_subindicador' => $this->id_subindicador,
            'subindicador' => $this->subindicador,
            'metrica' => $this->metrica,
            'peso' => $this->peso,
            'meta' => $this->meta,
            'realizado' => $this->realizado,
            'pontos' => $this->pontos,
            'pontos_meta' => $this->pontos_meta,
            'ating' => $this->ating,
            'atingido' => $this->atingido,
            'ultima_atualizacao' => $this->ultima_atualizacao,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (string)($data['id'] ?? ''),
            (string)($data['id_familia'] ?? ''),
            (string)($data['familia'] ?? ''),
            (string)($data['id_indicador'] ?? ''),
            (string)($data['indicador'] ?? ''),
            isset($data['id_subindicador']) ? (string)$data['id_subindicador'] : null,
            $data['subindicador'] ?? null,
            $data['metrica'] ?? 'valor',
            (float)($data['peso'] ?? 0),
            (float)($data['meta'] ?? 0),
            (float)($data['realizado'] ?? 0),
            (float)($data['pontos'] ?? 0),
            (float)($data['pontos_meta'] ?? 0),
            (float)($data['ating'] ?? 0),
            (bool)($data['atingido'] ?? false),
            $data['ultima_atualizacao'] ?? null
        );
    }
}




