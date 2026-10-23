<?php

namespace Tests\Unit;

use App\Services\MyContactStarService;
use PHPUnit\Framework\TestCase;

class MyContactStarServiceTest extends TestCase
{
    public function test_each_rule_adds_one_star(): void
    {
        $service = new MyContactStarService();

        $this->assertSame(1, $service->calculate(['category' => 'Corporate']));
        $this->assertSame(1, $service->calculate(['category' => 'Govt. Organisation']));
        $this->assertSame(1, $service->calculate(['experience' => '13-20 Years']));
        $this->assertSame(1, $service->calculate(['is_whitelisted' => true]));
        $this->assertSame(1, $service->calculate(['role' => 'Chief Executive Officer']));
        $this->assertSame(1, $service->calculate(['speaker_tag' => 'VIP']));
    }

    public function test_matching_rules_stack_and_cap_at_five(): void
    {
        $service = new MyContactStarService();

        $this->assertSame(5, $service->calculate([
            'category' => 'Corporate',
            'experience' => '20+ Years',
            'is_whitelisted' => true,
            'role' => 'Chief Executive Officer',
            'speaker_tag' => 'VIP',
        ]));

        $this->assertSame(5, $service->calculate([
            'category' => 'Judge',
            'experience' => 'Above 10 Years',
            'is_whitelisted' => true,
            'role' => 'Chief Judge',
            'speaker_tag' => 'VIP',
        ]));
    }

    public function test_conservative_experience_parsing_only_counts_clear_values_above_ten_years(): void
    {
        $service = new MyContactStarService();

        $this->assertSame(1, $service->calculate(['experience' => '11 Years']));
        $this->assertSame(1, $service->calculate(['experience' => '8-12 Years']));
        $this->assertSame(0, $service->calculate(['experience' => '10+ Years']));
        $this->assertSame(0, $service->calculate(['experience' => '4-7 Years']));
    }
}
