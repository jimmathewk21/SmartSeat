<?php

namespace App\Services;

use Illuminate\Support\Collection;

class SeatRecommendations
{
    /** Pure deterministic allocator; it neither holds seats nor mutates selections. */
    public function suggest(array $people, Collection $inventory, bool $together = false): array
    {
        if ($people === [] || $inventory->count() < count($people)) {
            return [];
        }
        $seats = $inventory->map(fn ($item) => [
            'id' => $item->id, 'coach_id' => $item->seat->coach_id,
            'number' => $item->seat->number, 'berth' => $item->seat->berth_type,
            'accessible' => $item->seat->accessibility_status === 'accessible',
        ])->sortBy('id')->values()->all();
        $anchors = $together ? array_values(array_unique(array_column($seats, 'coach_id'))) : [null];
        $best = null;
        foreach ($anchors as $anchor) {
            $used = [];
            $plan = [];
            $score = 0;
            // Allocate hard accessibility needs before flexible passengers.
            $order = array_keys($people);
            usort($order, fn ($a, $b) => ((int) ($people[$b]['accessible_seat_required'] ?? false) <=> (int) ($people[$a]['accessible_seat_required'] ?? false)) ?: ($a <=> $b));
            foreach ($order as $index) {
                $person = $people[$index];
                $choice = null;
                foreach ($seats as $seat) {
                    if (isset($used[$seat['id']]) || (($person['accessible_seat_required'] ?? false) && ! $seat['accessible'])) {
                        continue;
                    }
                    [$points, $reasons] = $this->score($person, $seat, $anchor, $plan);
                    if ($choice === null || $points > $choice['score'] || ($points === $choice['score'] && $seat['id'] < $choice['id'])) {
                        $choice = $seat + ['score' => $points, 'reasons' => $reasons];
                    }
                }
                if ($choice === null) {
                    continue 2;
                }
                $used[$choice['id']] = true;
                $plan[$index] = $choice;
                $score += $choice['score'];
            }
            ksort($plan);
            $ids = array_column($plan, 'id');
            if ($best === null || $score > $best['score'] || ($score === $best['score'] && $ids < $best['ids'])) {
                $best = compact('plan', 'score', 'ids');
            }
        }

        return $best === null ? [] : array_values($best['plan']);
    }

    private function score(array $person, array $seat, ?int $anchor, array $chosen): array
    {
        $score = 0;
        $reasons = ['Available inventory in your selected travel class.'];
        $preference = $person['preferred_berth'] ?? 'any';
        if ($person['accessible_seat_required'] ?? false) {
            $score += 200;
            $reasons[] = 'Administrator-verified accessible seat, as requested.';
        } elseif ($seat['accessible']) {
            $score -= 10; // Preserve scarce accessible seats when preferences otherwise tie.
        }
        if ($preference !== 'any' && $seat['berth'] === $preference) {
            $score += 100;
            $reasons[] = 'Matches your explicit '.str_replace('_', ' ', $preference).' preference.';
        } elseif ($preference === 'any' && ($person['age'] ?? 0) >= 60 && in_array($seat['berth'], ['lower', 'side_lower'], true)) {
            $score += $seat['berth'] === 'lower' ? 30 : 20;
            $reasons[] = 'Lower berth may be convenient for a senior passenger; no medical need is assumed.';
        }
        if ($anchor !== null && $seat['coach_id'] === $anchor) {
            $score += 80;
            $reasons[] = 'Supports your preference to keep the group in one coach.';
            $near = array_filter($chosen, fn ($item) => $item['coach_id'] === $anchor);
            if ($near !== []) {
                $distance = min(array_map(fn ($item) => abs($item['number'] - $seat['number']), $near));
                $score += max(0, 20 - $distance);
                $reasons[] = 'Seat numbers are close within that coach; physical adjacency is not guaranteed.';
            }
        }

        return [$score, $reasons];
    }
}
