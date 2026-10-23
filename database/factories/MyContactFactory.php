<?php

namespace Database\Factories;

use App\Models\MyContact;
use Illuminate\Database\Eloquent\Factories\Factory;

class MyContactFactory extends Factory
{
    protected $model = MyContact::class;

    public function definition(): array
    {
        return [
            'picture_url' => null,
            'gender' => 'Male',
            'name' => fake()->name(),
            'designation' => fake()->jobTitle(),
            'company_name' => fake()->company(),
            'official_email' => fake()->unique()->safeEmail(),
            'personal_email' => fake()->safeEmail(),
            'alternate_email' => fake()->safeEmail(),
            'mobile_number' => fake()->numerify('+91##########'),
            'whatsapp_number' => fake()->numerify('+91##########'),
            'alternate_mobile_number' => fake()->numerify('+91##########'),
            'country' => fake()->country(),
            'category' => 'Law Firm',
            'specialization' => 'Patent',
            'experience' => '20+ Years',
            'role' => 'Speaker',
            'speaker_tag' => 'Speaker',
            'event_name' => 'AI Summit 2026',
            'event_names' => "AI Summit 2026\nGlobal Forum",
            'is_subscribed' => true,
            'stars' => 4,
            'linkedin_profile' => 'https://www.linkedin.com/in/' . fake()->slug(),
            'web_url' => fake()->url(),
            'is_whitelisted' => false,
            'is_blacklisted' => false,
            'contact_status' => MyContact::STATUS_DEFAULT,
            'is_published' => true,
            'show_email' => false,
            'show_whatsapp' => false,
            'show_linkedin' => false,
            'show_website' => false,
            'show_category' => false,
            'show_expertise' => false,
        ];
    }
}
