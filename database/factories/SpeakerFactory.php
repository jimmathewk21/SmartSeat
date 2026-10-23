<?php

namespace Database\Factories;

use App\Models\Speaker;
use Illuminate\Database\Eloquent\Factories\Factory;

class SpeakerFactory extends Factory
{
    protected $model = Speaker::class;

    public function definition(): array
    {
        return [
            'event_id' => null,
            'name' => fake()->name(),
            'designation' => fake()->jobTitle(),
            'bio' => fake()->sentence(),
            'photo_url' => null,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 20),
            'company_name' => fake()->company(),
            'country' => fake()->country(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'show_contact_details' => false,
            'linkedin_url' => fake()->url(),
            'topic' => fake()->sentence(4),
            'category' => fake()->randomElement(['Moderator', 'Panel Expert', 'Keynote']),
            'topic_category' => fake()->randomElement(['Technology', 'Leadership', 'Innovation']),
            'biography_type' => 'text',
            'resource_url' => fake()->url(),
            'advert_url' => null,
            'presentation_file_url' => null,
            'show_on_homepage' => false,
            'show_on_scrollbar' => false,
            'is_key_figure' => false,
            'show_on_current_conference' => false,
            'show_on_future_conference' => false,
        ];
    }
}
