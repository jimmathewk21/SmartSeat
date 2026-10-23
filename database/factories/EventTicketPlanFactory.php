<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventTicketPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class EventTicketPlanFactory extends Factory
{
    protected $model = EventTicketPlan::class;

    public function definition(): array
    {
        return [
            'event_id' => Event::factory(),
            'plan_index' => 1,
            'sort_order' => 1,
            'name' => 'Economy',
            'price_paise' => 29900,
            'mrp_inr_paise' => 34900,
            'sale_inr_paise' => 29900,
            'mrp_usd_cents' => 499,
            'sale_usd_cents' => 399,
            'benefits' => "Access to keynotes\nNetworking",
            'is_active' => true,
            'is_featured' => false,
            'valid_till_text' => 'Valid till June 30',
        ];
    }
}
