<?php

namespace App\Application\UseCase\Pobj;

use App\Domain\DTO\FilterDTO;
use App\Domain\DTO\Resumo\CardDTO;
use App\Domain\DTO\Resumo\ClassifiedCardDTO;
use App\Domain\DTO\Resumo\VariableCardDTO;
use App\Domain\DTO\Resumo\BusinessSnapshotDTO;
use App\Repository\Pobj\ResumoRepository;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;

class ResumoUseCase
{
    private $resumoRepository;

    public function __construct(
        ResumoRepository $resumoRepository
    ) {
        $this->resumoRepository = $resumoRepository;
    }

    public function handle(?FilterDTO $filters = null): array
    {
        $produtos = $this->resumoRepository->findProdutos($filters);
        $produtosMensais = $this->resumoRepository->findProdutosMensais($filters);
        $variavel = $this->resumoRepository->findVariavel($filters);
        $calendario = $this->resumoRepository->findCalendario();
        $businessSnapshot = $this->buildBusinessSnapshot($calendario);

        $cards = array_map(function($produto) {
            return CardDTO::fromArray($produto)->toArray();
        }, $produtos);

        $classifiedCards = array_map(function($produtoMensal) {
            return ClassifiedCardDTO::fromArray($produtoMensal)->toArray();
        }, $produtosMensais);

        $variableCard = array_map(function($var) {
            return VariableCardDTO::fromArray($var)->toArray();
        }, $variavel);

        $businessSnapshotDTO = BusinessSnapshotDTO::fromArray($businessSnapshot);

        return [
            'cards' => $cards,
            'classifiedCards' => $classifiedCards,
            'variableCard' => $variableCard,
            'businessSnapshot' => $businessSnapshotDTO->toArray(),
        ];
    }

    private function buildBusinessSnapshot(array $calendario): array
    {
        $today = new DateTimeImmutable('today');
        $monthStart = $today->modify('first day of this month');
        $monthEnd = $today->modify('last day of this month');
        $monthKey = $today->format('Y-m');

        $businessDays = array_filter($calendario, function ($entry) use ($monthKey) {
            if (!is_array($entry)) {
                return false;
            }

            $date = $entry['data'] ?? $entry['competencia'] ?? null;
            if (!$date || strpos($date, $monthKey) !== 0) {
                return false;
            }

            $flag = $entry['eh_dia_util'] ?? $entry['ehDiaUtil'] ?? $entry['ehDiaUtil'] ?? null;
            return $this->isBusinessDayFlagTrue($flag);
        });

        $todayStr = $today->format('Y-m-d');
        $total = count($businessDays);
        $elapsed = 0;

        if ($total > 0) {
            foreach ($businessDays as $entry) {
                $date = $entry['data'] ?? $entry['competencia'] ?? null;
                if ($date && $date <= $todayStr) {
                    $elapsed++;
                }
            }
        } else {
            $total = $this->countWeekdaysBetween($monthStart, $monthEnd);
            $cappedToday = $today;
            if ($today < $monthStart) {
                $cappedToday = $monthStart;
            } elseif ($today > $monthEnd) {
                $cappedToday = $monthEnd;
            }
            $elapsed = $this->countWeekdaysBetween($monthStart, $cappedToday);
        }

        $remaining = max(0, $total - $elapsed);

        return [
            'total' => $total,
            'elapsed' => $elapsed,
            'remaining' => $remaining,
            'monthStart' => $monthStart->format('Y-m-d'),
            'monthEnd' => $monthEnd->format('Y-m-d'),
            'today' => $todayStr,
        ];
    }

    private function isBusinessDayFlagTrue($flag): bool
    {
        if ($flag === null) {
            return false;
        }

        if (is_bool($flag)) {
            return $flag;
        }

        if (is_numeric($flag)) {
            return (int)$flag === 1;
        }

        if (is_string($flag)) {
            $normalized = strtolower(trim($flag));
            return in_array($normalized, ['1', 'sim', 's', 'true'], true);
        }

        return false;
    }

    private function countWeekdaysBetween(DateTimeImmutable $start, DateTimeImmutable $end): int
    {
        if ($end < $start) {
            return 0;
        }

        $period = new DatePeriod($start, new DateInterval('P1D'), $end->modify('+1 day'));
        $count = 0;

        foreach ($period as $date) {
            $weekday = (int)$date->format('w');
            if ($weekday !== 0 && $weekday !== 6) {
                $count++;
            }
        }

        return $count;
    }
}

