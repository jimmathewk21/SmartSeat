<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\BookingEngine;
use App\Services\Intelligence\FeatureVector;
use App\Services\Intelligence\WaitlistHistory;
use App\Services\Intelligence\WaitlistPrediction;
use App\Services\Intelligence\WaitlistTraining;
use Illuminate\Support\Facades\Storage;
use LogicException;
use Tests\BookingTestCase;

class WaitlistIntelligenceTest extends BookingTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local'); // Model artifacts never touch the application's model directory.
    }

    public function test_empty_and_insufficient_history_never_fabricates_a_probability_or_model(): void
    {
        $this->assertFalse(app(WaitlistTraining::class)->train()['trained']);
        $journey = $this->journey();
        $this->reserve($journey);
        $waiting = $this->wait($journey);
        $prediction = app(WaitlistPrediction::class)->forBooking($waiting);
        $this->assertFalse($prediction['available']);
        $this->assertSame(WaitlistPrediction::UNAVAILABLE, $prediction['message']);
        $this->assertArrayNotHasKey('probability', $prediction);
        $this->assertFalse(app(WaitlistTraining::class)->train()['trained']);
        Storage::disk('local')->assertMissing('intelligence/waitlist/active.json');
        $this->actingAs($waiting->user, 'web')->get(route('bookings.show', $waiting))->assertOk()->assertSee(WaitlistPrediction::UNAVAILABLE)->assertDontSee('% estimated chance');
    }

    public function test_join_features_are_immutable_as_of_facts_and_only_mature_outcomes_become_labels(): void
    {
        $journey = $this->journey();
        $reserved = $this->reserve($journey);
        $first = $this->wait($journey);
        $second = $this->wait($journey);
        $snapshot = $first->predictionSnapshot;
        $this->assertSame(FeatureVector::VERSION, $snapshot->feature_version);
        $this->assertTrue(FeatureVector::valid($snapshot->features));
        $this->assertEqualsWithDelta(log(2), $snapshot->features['occupied_log'], 1.0e-8);
        $this->assertSame(0, $snapshot->features['queue_log_people']);
        $this->assertEqualsWithDelta(log(2), $second->predictionSnapshot->features['queue_log_people'], 1.0e-8);
        $this->assertSame([], app(WaitlistHistory::class)->rows(now()->toImmutable()));
        app(BookingEngine::class)->cancel($reserved->user, $reserved);
        $rows = app(WaitlistHistory::class)->rows($snapshot->boarding_departure_at->addHours(25));
        $byId = collect($rows)->keyBy('id');
        $this->assertSame(1, $byId[$snapshot->id]['label']);
        $this->assertSame(0, $byId[$second->predictionSnapshot->id]['label']);
        $this->assertSame($snapshot->features, $snapshot->refresh()->features);
        $this->expectException(LogicException::class);
        $snapshot->update(['features' => []]);
    }

    public function test_labels_do_not_include_events_after_prediction_or_training_cutoff(): void
    {
        $journey = $this->journey();
        $reserved = $this->reserve($journey);
        $waiting = $this->wait($journey);
        $snapshot = $waiting->predictionSnapshot;
        app(BookingEngine::class)->cancel($reserved->user, $reserved);
        $event = $waiting->events()->where('action', 'promoted')->firstOrFail();
        $event->forceFill(['created_at' => $snapshot->boarding_departure_at->addDays(3)])->save();
        $rows = app(WaitlistHistory::class)->rows($snapshot->boarding_departure_at->addHours(25));
        $this->assertSame(0, $rows[0]['label']);
    }

    public function test_ml_administration_is_authorized_and_explicit(): void
    {
        $this->get(route('administrator.intelligence.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create(), 'web')->withSession(['auth.password_confirmed_at' => now()->timestamp]);
        $this->get(route('administrator.intelligence.index'))->assertForbidden();
        $this->post(route('administrator.intelligence.train'), ['confirm_training' => 1])->assertForbidden();
        $this->actingAs(User::factory()->administrator()->create(), 'web');
        $this->get(route('administrator.intelligence.index'))->assertOk()->assertSee(WaitlistPrediction::UNAVAILABLE);
        $this->post(route('administrator.intelligence.train'), ['confirm_training' => 1])->assertRedirect(route('administrator.intelligence.index'));
        $this->assertDatabaseCount('bookings', 0);
        Storage::disk('local')->assertMissing('intelligence/waitlist/active.json');
    }

    public function test_training_requires_recent_password_confirmation(): void
    {
        $this->actingAs(User::factory()->administrator()->create(), 'web');
        $this->post(route('administrator.intelligence.train'), ['confirm_training' => 1])->assertRedirect(route('password.confirm'));
        Storage::disk('local')->assertMissing('intelligence/waitlist/active.json');
    }
}
