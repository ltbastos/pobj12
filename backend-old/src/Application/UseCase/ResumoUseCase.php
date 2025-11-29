<?php

namespace App\Application\UseCase;

use App\Domain\DTO\FilterDTO;
use DateInterval;
use DatePeriod;
use DateTimeImmutable;

class ResumoUseCase
{
    /** @var ProdutoUseCase */
    private $produtoUseCase;

    /** @var VariavelUseCase */
    private $variavelUseCase;

    /** @var CalendarioUseCase */
    private $calendarioUseCase;

    public function __construct(
        ProdutoUseCase $produtoUseCase,
        VariavelUseCase $variavelUseCase,
        CalendarioUseCase $calendarioUseCase
    ) {
        $this->produtoUseCase = $produtoUseCase;
        $this->variavelUseCase = $variavelUseCase;
        $this->calendarioUseCase = $calendarioUseCase;
    }

    /**
     * Retorna o payload completo consumido pelo ResumoView
     */
    public function handle(FilterDTO $filters = null): array
    {
        $produtos = $this->produtoUseCase->handle($filters);
        $produtosMensais = $this->produtoUseCase->handleMonthly($filters);
        $variavel = $this->variavelUseCase->handle($filters);
        $calendario = $this->calendarioUseCase->getAll();
        $businessSnapshot = $this->buildBusinessSnapshot($calendario);

        return [
            'produtos' => $produtos,
            'produtosMensais' => $produtosMensais,
            'variavel' => $variavel,
            'businessSnapshot' => $businessSnapshot,
        ];
    }

    /**
     * Calcula snapshot de dias úteis do mês corrente
     */
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

