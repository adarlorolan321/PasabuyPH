<?php

namespace App\Services;

class FareService
{
    /**
    * Core fare calculation with transparent breakdown and surge multiplier.
    *
    * @return array{
    *     base_fare: float,
    *     distance: float,
    *     per_km_rate: float,
    *     computed_fare: float,
    *     minimum_fare: float,
    *     final_fare: float,
    *     multiplier: float,
    *     adjustment_label: string|null
    * }
    */
    public function calculate(
        float $distanceKm,
        float $baseFare,
        float $perKmRate,
        float $minimumFare
    ): array {
        $computed = $baseFare + ($distanceKm * $perKmRate);
        $beforeMultiplier = max($computed, $minimumFare);

        [$multiplier, $label] = $this->currentMultiplier();

        $final = $beforeMultiplier * $multiplier;

        // Round to nearest whole peso to keep UI clean.
        $finalRounded = round($final);

        return [
            'base_fare' => $baseFare,
            'distance' => $distanceKm,
            'per_km_rate' => $perKmRate,
            'computed_fare' => $computed,
            'minimum_fare' => $minimumFare,
            'final_fare' => $finalRounded,
            'multiplier' => $multiplier,
            'adjustment_label' => $label,
        ];
    }

    /**
     * Determine the active multiplier and human label based on config.
     *
     * @return array{0: float, 1: string|null}
     */
    protected function currentMultiplier(): array
    {
        $config = config('fare.multiplier');
        $default = (float) ($config['default'] ?? 1.0);
        $rules = $config['time_rules'] ?? [];

        $nowHour = (int) now()->format('G'); // 0-23

        foreach ($rules as $rule) {
            $from = (int) ($rule['from_hour'] ?? 0);
            $to = (int) ($rule['to_hour'] ?? 0);
            $multiplier = (float) ($rule['multiplier'] ?? $default);
            $label = $rule['label'] ?? null;

            if ($nowHour >= $from && $nowHour < $to) {
                return [$multiplier, $label];
            }
        }

        return [$default, null];
    }
}

