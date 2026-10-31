<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Passenger;
use App\Models\SeatAssignment;
use App\Models\Train;
use App\Models\TrainTrip;
use App\Models\TripSeatInventory;
use App\Models\User;
use App\Models\WaitlistEntry;
use App\Services\Intelligence\WaitlistFeatures;
use App\Support\BookingRules;
use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Database\DeadlockException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use PDOException;

class BookingEngine
{
    public function details(#[\SensitiveParameter] array $input): array
    {
        $data = Validator::make($input, BookingRules::details())->validate();
        $data['boarding_order'] = (int) $data['boarding_order'];
        $data['alighting_order'] = (int) $data['alighting_order'];
        $data['keep_group_together'] = (bool) ($data['keep_group_together'] ?? false);
        $data['passengers'] = array_values(array_map(static fn ($person) => [
            'name' => trim($person['name']), 'age' => (int) $person['age'], 'gender' => $person['gender'],
            'preferred_berth' => $person['preferred_berth'] ?? 'any',
            'accessible_seat_required' => (bool) ($person['accessible_seat_required'] ?? false),
        ], $data['passengers']));

        return $data;
    }

    public function itinerary(TrainTrip $trip, int $boarding, int $alighting, bool $requireFuture = true): array
    {
        $trip->loadMissing('train');
        $stops = $trip->train->routeStops()->with('station')->orderBy('stop_order')->limit(100)->get();
        $origin = $stops->firstWhere('stop_order', $boarding);
        $destination = $stops->firstWhere('stop_order', $alighting);
        if (! $origin || ! $destination || $boarding >= $alighting) {
            $this->reject('journey', 'Choose two stations in the correct order on this train.');
        }
        $midnight = CarbonImmutable::parse($trip->service_date->toDateString(), Config::get('smartseat.railway_timezone'));
        $departure = $midnight->addMinutes($origin->departure_offset_minutes);
        $arrival = $midnight->addMinutes($destination->arrival_offset_minutes);
        if ($requireFuture && ($trip->status !== 'scheduled' || ! $trip->train->is_active || $departure->lessThanOrEqualTo(CarbonImmutable::now($departure->timezone)))) {
            $this->reject('journey', 'This journey is unavailable or has already departed from your boarding station.');
        }

        return compact('origin', 'destination', 'departure', 'arrival', 'stops');
    }

    public function available(TrainTrip $trip, string $class): Builder
    {
        return $trip->inventory()->getQuery()->where('status', 'available')
            ->whereHas('seat.coach', fn ($query) => $query->where('travel_class', $class))
            ->whereDoesntHave('activeAssignment');
    }

    public function preview(TrainTrip $trip, array $details, string $mode, array $seats): array
    {
        $details = $this->details($details);
        $this->itinerary($trip, $details['boarding_order'], $details['alighting_order']);
        $this->selection($details, $mode, $seats);
        $this->assertClass($trip, $details['travel_class'], count($details['passengers']));
        $this->assertAccessibilityCapacity($trip, $details);
        if ($mode === 'reserve') {
            $available = $this->available($trip, $details['travel_class'])->whereIn('id', $seats)->count();
            if ($available !== count($details['passengers'])) {
                $this->reject('seats', 'Some selected seats are unavailable or belong to another trip or class.');
            }
            $this->assertPreferences($details['passengers'], $seats, $this->available($trip, $details['travel_class'])->whereIn('id', $seats)->with('seat.coach')->get());
        }

        return ['details' => $details, 'mode' => $mode, 'seats' => array_values(array_map('intval', $seats))];
    }

    public function book(User $user, TrainTrip $trip, #[\SensitiveParameter] array $input, string $mode, array $seats, string $key): Booking
    {
        $details = $this->details($input);
        $this->selection($details, $mode, $seats);
        Validator::make(['key' => $key], ['key' => ['required', 'uuid']])->validate();
        $seats = array_values(array_map('intval', $seats));
        $hash = hash('sha256', json_encode([$trip->id, $this->fingerprintDetails($details), $mode, $seats], JSON_THROW_ON_ERROR));

        try {
            return $this->transaction(function () use ($user, $trip, $details, $mode, $seats, $key, $hash) {
                $trip = $this->lockedTrip($trip);
                $existing = Booking::where('user_id', $user->id)->where('submission_key', $key)->lockForUpdate()->first();
                if ($existing) {
                    return $this->sameSubmission($existing, $hash);
                }

                $this->itinerary($trip, $details['boarding_order'], $details['alighting_order']);
                $this->assertClass($trip, $details['travel_class'], count($details['passengers']));
                $this->assertAccessibilityCapacity($trip, $details);
                $this->promoteClass($trip, $details['travel_class']);
                $waiting = $trip->waitlistEntries()->where('travel_class', $details['travel_class'])->where('status', 'waiting')->exists();
                $free = $this->available($trip, $details['travel_class'])->with('seat.coach')->orderBy('id')->lockForUpdate()->limit(6000)->get();
                $suggestions = $mode === 'waitlist' ? app(SeatRecommendations::class)->suggest($details['passengers'], $free, $details['keep_group_together']) : [];

                if ($mode === 'reserve' && ($waiting || count(array_intersect($free->modelKeys(), $seats)) !== count($seats))) {
                    $this->reject('seats', 'Your seats are no longer available, or an earlier waitlist group has priority. Choose other seats or join the waitlist.');
                }
                if ($mode === 'reserve') {
                    $this->assertPreferences($details['passengers'], $seats, $free);
                }
                if ($mode === 'waitlist' && ! $waiting && $suggestions !== []) {
                    $this->reject('waitlist', 'Enough seats are available. Select seats instead of joining the waitlist.');
                }

                $booking = Booking::create([
                    'reference' => (string) Str::uuid(), 'user_id' => $user->id,
                    'trip_id' => $trip->id, 'train_id' => $trip->train_id,
                    'boarding_order' => $details['boarding_order'], 'alighting_order' => $details['alighting_order'],
                    'submission_key' => $key, 'request_hash' => $hash, 'travel_class' => $details['travel_class'],
                    'keep_group_together' => $details['keep_group_together'],
                    'status' => 'pending', 'total_paise' => 0,
                ]);
                $this->event($booking, 'created', null, 'pending', $user->id);
                $people = collect();
                foreach ($details['passengers'] as $person) {
                    $people->push($booking->passengers()->create($person + ['trip_id' => $trip->id, 'status' => 'pending']));
                }
                if ($mode === 'reserve') {
                    $this->assign($booking, $people, $seats);
                    $this->transition($booking, 'confirmed', 'confirmed', $user->id);
                } else {
                    $position = (int) $trip->waitlistEntries()->where('travel_class', $details['travel_class'])->max('position');
                    foreach ($people as $person) {
                        $person->update(['status' => 'waitlisted']);
                        WaitlistEntry::create([
                            'passenger_id' => $person->id, 'trip_id' => $trip->id, 'travel_class' => $details['travel_class'],
                            'position' => ++$position, 'status' => 'waiting',
                        ]);
                    }
                    $this->transition($booking, 'waitlisted', 'waitlisted', $user->id);
                    app(WaitlistFeatures::class)->capture($booking);
                }

                return $booking->refresh();
            });
        } catch (PDOException $exception) {
            if ($exception instanceof DeadlockException && DB::transactionLevel() > 0) {
                throw $exception; // The caller must retry the entire outer transaction.
            }
            if ($this->isConflict($exception)) {
                // Read from the writer after the failed transaction has rolled back.
                $existing = Booking::query()->useWritePdo()->where('user_id', $user->id)->where('submission_key', $key)->first();
                if ($existing) {
                    return $this->sameSubmission($existing, $hash);
                }
                $this->reject('booking', 'The journey changed while confirming. Please retry the same review; no partial reservation was saved.');
            }
            throw $exception;
        }
    }

    public function cancel(User $actor, Booking $booking): Booking
    {
        Gate::forUser($actor)->authorize('cancel', $booking);
        // Resolve metadata outside the transaction, before any consistent-read snapshot.
        $trip = $booking->trip;

        return $this->conflictSafe(function () use ($actor, $booking, $trip) {
            $trip = $this->lockedTrip($trip);
            $booking = Booking::whereKey($booking->id)->lockForUpdate()->firstOrFail();
            Gate::forUser($actor)->authorize('cancel', $booking);
            if ($booking->status === 'cancelled') {
                return $booking;
            }
            $classes = $booking->passengers()->with('assignments.inventory.seat.coach')->orderBy('id')->get()
                ->flatMap(fn ($person) => $person->assignments->where('status', 'active')->map(fn ($assignment) => $assignment->inventory->seat->coach->travel_class))
                ->push($booking->travel_class)->filter()->unique()->sort();
            $this->cancelGroup($booking, 'cancelled', $actor->id);
            if ($trip->status === 'scheduled' && $trip->train->is_active) {
                foreach ($classes as $class) {
                    $this->promoteClass($trip, $class);
                }
            }

            return $booking->refresh();
        });
    }

    /** Used after inventory initialization; never a startup job or external command. */
    public function promoteTrip(TrainTrip $trip): int
    {
        return $this->conflictSafe(function () use ($trip) {
            $trip = $this->lockedTrip($trip);
            if ($trip->status !== 'scheduled' || ! $trip->train->is_active) {
                return 0;
            }
            $promoted = 0;
            foreach (BookingRules::CLASSES as $class) {
                $promoted += $this->promoteClass($trip, $class);
            }

            return $promoted;
        });
    }

    private function promoteClass(TrainTrip $trip, string $class): int
    {
        $promoted = 0;
        while ($head = $trip->waitlistEntries()->where('status', 'waiting')->where('travel_class', $class)->orderBy('created_at')->orderBy('id')->first()) {
            $booking = Booking::whereKey($head->passenger->booking_id)->lockForUpdate()->firstOrFail();
            if ($booking->status !== 'waitlisted') {
                $this->reject('waitlist', 'Waitlist state requires administrator review.');
            }
            try {
                $this->itinerary($trip, $booking->boarding_order, $booking->alighting_order);
            } catch (ValidationException) {
                $this->cancelGroup($booking, 'expired', null);

                continue;
            }
            $people = $booking->passengers()->orderBy('id')->lockForUpdate()->get();
            $free = $this->available($trip, $class)->with('seat.coach')->orderBy('id')->lockForUpdate()->limit(6000)->get();
            $suggestions = app(SeatRecommendations::class)->suggest($people->toArray(), $free, (bool) $booking->keep_group_together);
            if ($suggestions === []) {
                break; // Strict group FIFO: do not skip a larger head group.
            }
            $entries = WaitlistEntry::whereIn('passenger_id', $people->modelKeys())->where('status', 'waiting')->orderBy('id')->lockForUpdate()->get();
            if ($entries->count() !== $people->count() || $people->contains(fn ($person) => $person->status !== 'waitlisted')) {
                $this->reject('waitlist', 'Waitlist group integrity requires administrator review.');
            }
            $this->assign($booking, $people, array_column($suggestions, 'id'));
            foreach ($entries as $entry) {
                $entry->update(['status' => 'promoted', 'promoted_at' => now()]);
            }
            $this->transition($booking, 'confirmed', 'promoted', null);
            $promoted++;
        }

        return $promoted;
    }

    private function assign(Booking $booking, Collection $people, array $inventoryIds): void
    {
        // Passenger-seat pairing follows input order; writes use inventory ID order.
        $pairs = $people->values()->map(fn ($person, $index) => ['person' => $person, 'inventory_id' => $inventoryIds[$index]])->sortBy('inventory_id');
        foreach ($pairs as $pair) {
            SeatAssignment::create([
                'passenger_id' => $pair['person']->id, 'inventory_id' => $pair['inventory_id'],
                'trip_id' => $booking->trip_id, 'status' => 'active',
            ]);
            $pair['person']->update(['status' => 'confirmed']);
        }
    }

    private function assertPreferences(array $people, array $ids, Collection $inventory): void
    {
        $byId = $inventory->keyBy('id');
        foreach (array_values($people) as $index => $person) {
            if (($person['accessible_seat_required'] ?? false)
                && $byId->get($ids[$index])?->seat->accessibility_status !== 'accessible') {
                $this->reject('seats', 'A selected seat is not verified accessible. Choose a verified seat or explicitly update that passenger requirement.');
            }
        }
    }

    private function assertAccessibilityCapacity(TrainTrip $trip, array $details): void
    {
        $needed = count(array_filter($details['passengers'], fn ($person) => $person['accessible_seat_required']));
        if ($needed && $trip->train->seats()->where('accessibility_status', 'accessible')
            ->whereHas('coach', fn ($query) => $query->where('travel_class', $details['travel_class']))->count() < $needed) {
            $this->reject('passengers', 'This class has too few administrator-verified accessible seats for your group. Choose another class or journey.');
        }
    }

    /** Preserve hashes of existing default-preference submissions across this upgrade. */
    private function fingerprintDetails(array $details): array
    {
        if (! $details['keep_group_together']) {
            unset($details['keep_group_together']);
        }
        foreach ($details['passengers'] as &$person) {
            if ($person['preferred_berth'] === 'any') {
                unset($person['preferred_berth']);
            }
            if (! $person['accessible_seat_required']) {
                unset($person['accessible_seat_required']);
            }
        }

        return $details;
    }

    private function cancelGroup(Booking $booking, string $action, ?int $actor): void
    {
        $people = $booking->passengers()->orderBy('id')->lockForUpdate()->get();
        $assignments = SeatAssignment::whereIn('passenger_id', $people->modelKeys())->where('status', 'active')->orderBy('inventory_id')->lockForUpdate()->get();
        TripSeatInventory::whereIn('id', $assignments->pluck('inventory_id'))->orderBy('id')->lockForUpdate()->get();
        foreach ($assignments as $assignment) {
            $assignment->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        }
        $waiting = WaitlistEntry::whereIn('passenger_id', $people->modelKeys())->where('status', 'waiting')->orderBy('id')->lockForUpdate()->get();
        foreach ($waiting as $entry) {
            $entry->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        }
        foreach ($people as $person) {
            $person->update(['status' => 'cancelled', 'cancelled_at' => now()]);
        }
        $this->transition($booking, 'cancelled', $action, $actor);
    }

    private function lockedTrip(TrainTrip $trip): TrainTrip
    {
        // Matches catalogue/init lock order and avoids train FK / trip lock inversion.
        $train = Train::whereKey($trip->train_id)->lockForUpdate()->firstOrFail();
        $locked = TrainTrip::whereKey($trip->id)->lockForUpdate()->firstOrFail();
        if ($locked->train_id !== $train->id) {
            $this->reject('journey', 'The train configuration changed. Start a new booking.');
        }

        return $locked->setRelation('train', $train);
    }

    private function selection(array $details, string $mode, array $seats): void
    {
        Validator::make(['mode' => $mode, 'seats' => $seats], [
            'mode' => ['required', Rule::in(['reserve', 'waitlist'])],
            'seats' => ['array', 'list', $mode === 'reserve' ? 'size:'.count($details['passengers']) : 'max:0'],
            'seats.*' => ['required', 'integer', 'min:1', 'distinct'],
        ])->validate();
    }

    private function assertClass(TrainTrip $trip, string $class, int $groupSize): void
    {
        $capacity = $trip->train->seats()->whereHas('coach', fn ($query) => $query->where('travel_class', $class))->count();
        if ($capacity === 0) {
            $this->reject('travel_class', 'This train has no configured seats in that travel class.');
        }
        if ($groupSize > $capacity) {
            $this->reject('passengers', 'Your group exceeds the entire configured capacity of this class. Choose a smaller group or a different class.');
        }
    }

    private function sameSubmission(Booking $booking, string $hash): Booking
    {
        if (! hash_equals((string) $booking->request_hash, $hash)) {
            $this->reject('booking', 'This submission key was already used for different booking details.');
        }

        return $booking;
    }

    private function transition(Booking $booking, string $status, string $action, ?int $actor): void
    {
        $from = $booking->status;
        $booking->update(['status' => $status, 'cancelled_at' => $status === 'cancelled' ? now() : null]);
        $this->event($booking, $action, $from, $status, $actor);
    }

    private function event(Booking $booking, string $action, ?string $from, string $to, ?int $actor): void
    {
        $booking->events()->create(['action' => $action, 'from_status' => $from, 'to_status' => $to, 'actor_id' => $actor]);
    }

    private function transaction(Closure $action): mixed
    {
        return DB::transaction($action, (int) Config::get('smartseat.booking.transaction_attempts'));
    }

    private function conflictSafe(Closure $action): mixed
    {
        try {
            return $this->transaction($action);
        } catch (PDOException $exception) {
            if ($exception instanceof DeadlockException && DB::transactionLevel() > 0) {
                throw $exception;
            }
            if ($this->isConflict($exception)) {
                $this->reject('booking', 'The journey is busy. Please retry; this operation was rolled back.');
            }
            throw $exception;
        }
    }

    private function isConflict(PDOException $exception): bool
    {
        return $exception instanceof DeadlockException
            || in_array((string) $exception->getCode(), ['40001', '40P01'], true)
            || in_array($exception->errorInfo[0] ?? '', ['23000', '23505', '40001', '40P01'], true)
            || in_array((int) ($exception->errorInfo[1] ?? 0), [5, 6, 1205, 1213], true);
    }

    private function reject(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
