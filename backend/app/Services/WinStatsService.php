<?php

namespace App\Services;

use App\Models\Win;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

class WinStatsService
{
    /**
     * @return array{total_wins: int, wins_this_week: int, current_streak: int}
     */
    public function summary(): array
    {
        $today = CarbonImmutable::today();
        $weekStart = $today->startOfWeek(CarbonInterface::MONDAY);

        return [
            'total_wins' => Win::count(),
            'wins_this_week' => Win::where('win_date', '>=', $weekStart->toDateString())
                ->where('win_date', '<', $weekStart->addWeek()->toDateString())
                ->count(),
            'current_streak' => $this->currentStreak($today),
        ];
    }

    private function currentStreak(CarbonImmutable $today): int
    {
        $dates = Win::whereDate('win_date', '<=', $today->toDateString())
            ->distinct()
            ->orderByDesc('win_date')
            ->pluck('win_date')
            ->map(fn (CarbonInterface $date): string => $date->toDateString())
            ->unique()
            ->values();

        $expectedDay = $dates->first() === $today->toDateString() ? $today : $today->subDay();
        $streak = 0;

        foreach ($dates as $date) {
            if ($date !== $expectedDay->toDateString()) {
                break;
            }

            $streak++;
            $expectedDay = $expectedDay->subDay();
        }

        return $streak;
    }
}
