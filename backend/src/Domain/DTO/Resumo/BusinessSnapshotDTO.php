<?php

namespace App\Domain\DTO\Resumo;

class BusinessSnapshotDTO
{
    public $total;
    public $elapsed;
    public $remaining;
    public $monthStart;
    public $monthEnd;
    public $today;

    public function __construct(
        int $total,
        int $elapsed,
        int $remaining,
        string $monthStart,
        string $monthEnd,
        string $today
    ) {
        $this->total = $total;
        $this->elapsed = $elapsed;
        $this->remaining = $remaining;
        $this->monthStart = $monthStart;
        $this->monthEnd = $monthEnd;
        $this->today = $today;
    }

    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'elapsed' => $this->elapsed,
            'remaining' => $this->remaining,
            'monthStart' => $this->monthStart,
            'monthEnd' => $this->monthEnd,
            'today' => $this->today,
        ];
    }

    public static function fromArray(array $data): self
    {
        return new self(
            (int)($data['total'] ?? 0),
            (int)($data['elapsed'] ?? 0),
            (int)($data['remaining'] ?? 0),
            (string)($data['monthStart'] ?? ''),
            (string)($data['monthEnd'] ?? ''),
            (string)($data['today'] ?? '')
        );
    }
}




