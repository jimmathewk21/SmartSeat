<?php

namespace Tests\Unit;

use App\Services\SeatRecommendations;
use PHPUnit\Framework\TestCase;

class SeatRecommendationsTest extends TestCase
{
    private function seat(int $id, int $coach, int $number, string $berth = 'lower', string $access = 'unknown'): object
    {
        return (object) ['id' => $id, 'seat' => (object) [
            'coach_id' => $coach, 'number' => $number, 'berth_type' => $berth, 'accessibility_status' => $access,
        ]];
    }

    public function test_explicit_preference_overrides_age_based_convenience(): void
    {
        $suggestions = (new SeatRecommendations)->suggest([['age' => 75, 'preferred_berth' => 'upper']], collect([
            $this->seat(1, 1, 1), $this->seat(2, 1, 2, 'upper'),
        ]));
        $this->assertSame(2, $suggestions[0]['id']);
        $this->assertStringContainsString('explicit upper', implode(' ', $suggestions[0]['reasons']));
    }

    public function test_accessibility_is_verified_and_never_inferred_from_age_or_lower_berth(): void
    {
        $service = new SeatRecommendations;
        $person = ['age' => 40, 'accessible_seat_required' => true];
        $this->assertSame([], $service->suggest([$person], collect([$this->seat(1, 1, 1)])));
        $result = $service->suggest([$person], collect([$this->seat(1, 1, 1), $this->seat(2, 1, 2, 'chair', 'accessible')]));
        $this->assertSame(2, $result[0]['id']);
        $this->assertStringContainsString('verified accessible', implode(' ', $result[0]['reasons']));
    }

    public function test_senior_convenience_is_a_soft_preference_with_an_explanation(): void
    {
        $result = (new SeatRecommendations)->suggest([['age' => 65]], collect([$this->seat(1, 1, 1, 'upper'), $this->seat(2, 1, 2)]));
        $this->assertSame(2, $result[0]['id']);
        $this->assertStringContainsString('no medical need is assumed', implode(' ', $result[0]['reasons']));
    }

    public function test_group_prefers_one_coach_and_uses_distinct_deterministic_seats(): void
    {
        $inventory = collect([$this->seat(1, 1, 1), $this->seat(2, 2, 10), $this->seat(3, 2, 11)]);
        $service = new SeatRecommendations;
        $people = [['age' => 25], ['age' => 26]];
        $plan = $service->suggest($people, $inventory, true);
        $this->assertSame([2, 3], array_column($plan, 'id'));
        $this->assertSame($plan, $service->suggest($people, $inventory->reverse(), true));
        $this->assertNotEmpty($plan[1]['reasons']);
    }

    public function test_accessibility_is_allocated_before_flexible_group_members(): void
    {
        $plan = (new SeatRecommendations)->suggest([['age' => 25], ['age' => 25, 'accessible_seat_required' => true]], collect([
            $this->seat(1, 1, 1, 'lower', 'accessible'), $this->seat(2, 1, 2),
        ]));
        $this->assertSame([2, 1], array_column($plan, 'id'));
    }
}
