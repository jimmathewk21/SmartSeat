<?php

namespace App\Services;

use App\Models\TrainBooking;
use App\Models\TrainBookingPassenger;
use App\Models\TrainBookingSeat;
use App\Models\TrainRouteStop;
use App\Models\TrainTrip;
use App\Models\TrainWaitlistEntry;
use App\Models\TrainWaitlistPassenger;
use App\Models\TripSeatInventory;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TrainBookingService
{
    public function book(User $user, int $tripId, array $inventoryIds, array $passengers, int $boardingStationId, int $destinationStationId): TrainBooking
    {
        $this->validatePassengers($passengers);

        return DB::transaction(function () use ($user, $tripId, $inventoryIds, $passengers, $boardingStationId, $destinationStationId) {
            $trip = TrainTrip::query()->lockForUpdate()->findOrFail($tripId);
            $journey = $this->resolveJourney($trip, $boardingStationId, $destinationStationId);
            $this->ensureTripBookable($trip, $journey['boarding_at']);
            $inventoryIds = array_values(array_unique(array_map('intval', $inventoryIds)));

            if (count($inventoryIds) !== count($passengers) || count($inventoryIds) === 0) {
                throw ValidationException::withMessages(['seats' => 'Choose one available seat for each passenger.']);
            }

            $inventories = $this->lockInventories($tripId, $inventoryIds);
            if ($inventories->count() !== count($inventoryIds) || $inventories->contains(fn ($inventory) => $inventory->status !== TripSeatInventory::STATUS_AVAILABLE)) {
                throw ValidationException::withMessages(['seats' => 'One or more selected seats are no longer available. Refresh the search and try again.']);
            }

            return $this->createConfirmedBooking($user, $trip, $inventories, $passengers, $journey);
        }, 3);
    }

    public function joinWaitlist(User $user, int $tripId, array $passengers, int $boardingStationId, int $destinationStationId): TrainWaitlistEntry
    {
        $this->validatePassengers($passengers);

        return DB::transaction(function () use ($user, $tripId, $passengers, $boardingStationId, $destinationStationId) {
            $trip = TrainTrip::query()->lockForUpdate()->findOrFail($tripId);
            $journey = $this->resolveJourney($trip, $boardingStationId, $destinationStationId);
            $this->ensureTripBookable($trip, $journey['boarding_at']);

            $availableInventories = TripSeatInventory::query()
                ->where('train_trip_id', $tripId)
                ->where('status', TripSeatInventory::STATUS_AVAILABLE)
                ->with('seat')
                ->get();
            if ($this->canAssign($availableInventories, $passengers)) {
                throw ValidationException::withMessages(['waitlist' => 'Seats are available. Select seats from the search results.']);
            }

            $entry = TrainWaitlistEntry::create([
                'user_id' => $user->id,
                'train_trip_id' => $tripId,
                ...$journey,
                'seat_count' => count($passengers),
                'requires_accessible_seat' => collect($passengers)->contains(fn ($passenger) => (bool) ($passenger['requires_accessible_seat'] ?? false)),
                'status' => TrainWaitlistEntry::STATUS_WAITING,
            ]);

            foreach ($passengers as $passenger) {
                TrainWaitlistPassenger::create([
                    'train_waitlist_entry_id' => $entry->id,
                    'name' => $passenger['name'],
                    'age' => $passenger['age'] ?? null,
                    'gender' => $passenger['gender'] ?? null,
                    'requires_accessible_seat' => (bool) ($passenger['requires_accessible_seat'] ?? false),
                ]);
            }

            return $entry->load('passengers');
        }, 3);
    }

    public function cancel(User $user, int $bookingId): TrainBooking
    {
        return DB::transaction(function () use ($user, $bookingId) {
            $booking = TrainBooking::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->findOrFail($bookingId);

            if ($booking->status !== TrainBooking::STATUS_CONFIRMED) {
                throw ValidationException::withMessages(['booking' => 'Only confirmed bookings can be cancelled.']);
            }

            $trip = TrainTrip::query()->lockForUpdate()->findOrFail($booking->train_trip_id);
            if (! ($booking->boarding_at ?? $trip->starts_at)->isFuture()) {
                throw ValidationException::withMessages(['booking' => 'A train booking cannot be cancelled after departure.']);
            }

            $inventories = TripSeatInventory::query()
                ->where('current_booking_id', $booking->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $this->ensureBeforeCutoff($booking->boarding_at ?? $trip->starts_at, 'booking');
            foreach ($inventories as $inventory) {
                $inventory->forceFill([
                    'status' => TripSeatInventory::STATUS_AVAILABLE,
                    'current_booking_id' => null,
                ])->save();
            }

            TrainBookingSeat::query()
                ->where('train_booking_id', $booking->id)
                ->whereNull('released_at')
                ->update(['released_at' => now(), 'updated_at' => now()]);

            $booking->forceFill(['status' => TrainBooking::STATUS_CANCELLED, 'cancelled_at' => now()])->save();
            $promotedCutoff = null;
            if ($trip->status === TrainTrip::STATUS_SCHEDULED) {
                $promotedCutoff = $this->promoteWaitingEntry($booking->train_trip_id);
            }

            $result = $booking->fresh(['trip.train', 'seatAssignments.inventory.seat.coach', 'passengers']);
            $this->ensureBeforeCutoff($booking->boarding_at ?? $trip->starts_at, 'booking');
            if ($promotedCutoff !== null) {
                $this->ensureBeforeCutoff($promotedCutoff, 'waitlist');
            }

            return $result;
        }, 3);
    }

    public function cancelWaitlistEntry(User $user, int $entryId): TrainWaitlistEntry
    {
        return DB::transaction(function () use ($user, $entryId) {
            $entry = TrainWaitlistEntry::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->findOrFail($entryId);

            if ($entry->status !== TrainWaitlistEntry::STATUS_WAITING) {
                throw ValidationException::withMessages(['waitlist' => 'Only waiting entries can be cancelled.']);
            }

            $entry->forceFill(['status' => TrainWaitlistEntry::STATUS_CANCELLED])->save();

            return $entry;
        }, 3);
    }

    private function promoteWaitingEntry(int $tripId): ?\Illuminate\Support\Carbon
    {
        $skippedEntryIds = [];
        while ($entry = TrainWaitlistEntry::query()
            ->with(['passengers', 'user', 'trip'])
            ->where('train_trip_id', $tripId)
            ->where('status', TrainWaitlistEntry::STATUS_WAITING)
            ->when($skippedEntryIds !== [], fn ($query) => $query->whereNotIn('id', $skippedEntryIds))
            ->orderBy('created_at')
            ->orderBy('id')
            ->lockForUpdate()
            ->first()) {
            $inventories = TripSeatInventory::query()
                ->where('train_trip_id', $tripId)
                ->where('status', TripSeatInventory::STATUS_AVAILABLE)
                ->with('seat')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if (! $this->canAssign($inventories, $entry->passengers->toArray())) {
                $skippedEntryIds[] = $entry->id;
                continue;
            }

            if (! ($entry->boarding_at ?? $entry->trip->starts_at)->isFuture()) {
                $skippedEntryIds[] = $entry->id;
                continue;
            }

            $booking = $this->createConfirmedBooking(
                $entry->user,
                $entry->trip,
                $this->selectAssignable($inventories, $entry->passengers->toArray()),
                $entry->passengers->map(fn ($passenger) => $passenger->only(['name', 'age', 'gender', 'requires_accessible_seat']))->all(),
                $entry->only(['boarding_station_id', 'destination_station_id', 'boarding_at', 'arrival_at'])
            );

            $entry->forceFill([
                'status' => TrainWaitlistEntry::STATUS_PROMOTED,
                'promoted_booking_id' => $booking->id,
                'promoted_at' => now(),
            ])->save();

            $cutoff = $entry->boarding_at ?? $entry->trip->starts_at;
            $this->ensureBeforeCutoff($cutoff, 'waitlist');

            return $cutoff;
        }

        return null;
    }

    private function createConfirmedBooking(User $user, TrainTrip $trip, Collection $inventories, array $passengers, array $journey): TrainBooking
    {
        $this->ensureBeforeCutoff($journey['boarding_at'] ?? $trip->starts_at, 'trip');
        if (! $this->canAssign($inventories, $passengers)) {
            throw ValidationException::withMessages(['seats' => 'The selected seats do not meet passenger accessibility requirements.']);
        }

        $booking = TrainBooking::create([
            'booking_code' => $this->bookingCode(),
            'user_id' => $user->id,
            'train_trip_id' => $trip->id,
            ...$journey,
            'status' => TrainBooking::STATUS_CONFIRMED,
            'seat_count' => count($passengers),
            'confirmed_at' => now(),
        ]);

        $passengerModels = collect($passengers)->map(fn ($passenger) => TrainBookingPassenger::create([
            'train_booking_id' => $booking->id,
            'name' => $passenger['name'],
            'age' => $passenger['age'] ?? null,
            'gender' => $passenger['gender'] ?? null,
            'requires_accessible_seat' => (bool) ($passenger['requires_accessible_seat'] ?? false),
        ]));

        $assignable = $this->selectAssignable($inventories, $passengers);
        $assignedSeats = $assignable->values();
        $passengerOrder = collect($passengers)->keys()->sortBy(fn ($index) => ! (bool) ($passengers[$index]['requires_accessible_seat'] ?? false))->values();

        foreach ($passengerOrder as $seatIndex => $passengerIndex) {
            $inventory = $assignedSeats[$seatIndex];
            $passenger = $passengerModels[$passengerIndex];
            $inventory->forceFill(['status' => TripSeatInventory::STATUS_BOOKED, 'current_booking_id' => $booking->id])->save();
            TrainBookingSeat::create([
                'train_booking_id' => $booking->id,
                'train_trip_id' => $trip->id,
                'trip_seat_inventory_id' => $inventory->id,
                'train_booking_passenger_id' => $passenger->id,
            ]);
        }

        $result = $booking->load(['trip.train', 'passengers', 'seatAssignments.inventory.seat.coach']);
        $this->ensureBeforeCutoff($booking->boarding_at ?? $trip->starts_at, 'trip');

        return $result;
    }

    private function ensureBeforeCutoff(\Illuminate\Support\Carbon $cutoff, string $key): void
    {
        // Throw inside the transaction so all writes roll back after lock or query delays.
        // The database commit itself can still finish after this application-side check.
        if (! $cutoff->isFuture()) {
            throw ValidationException::withMessages([$key => 'The boarding departure cutoff has passed.']);
        }
    }

    private function canAssign(Collection $inventories, array $passengers): bool
    {
        if ($inventories->count() < count($passengers)) {
            return false;
        }

        $requiredAccessible = collect($passengers)->filter(fn ($passenger) => (bool) ($passenger['requires_accessible_seat'] ?? false))->count();

        return $inventories->filter(fn ($inventory) => (bool) $inventory->seat?->is_accessible)->count() >= $requiredAccessible;
    }

    private function selectAssignable(Collection $inventories, array $passengers): Collection
    {
        $requiredCount = collect($passengers)->filter(fn ($passenger) => (bool) ($passenger['requires_accessible_seat'] ?? false))->count();
        $accessible = $inventories->filter(fn ($inventory) => (bool) $inventory->seat?->is_accessible)->take($requiredCount);
        $other = $inventories->reject(fn ($inventory) => $accessible->contains('id', $inventory->id));

        return $accessible->concat($other)->take(count($passengers))->values();
    }

    private function lockInventories(int $tripId, array $inventoryIds): Collection
    {
        return TripSeatInventory::query()
            ->where('train_trip_id', $tripId)
            ->whereIn('id', $inventoryIds)
            ->with('seat')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();
    }

    private function ensureTripBookable(TrainTrip $trip, \Illuminate\Support\Carbon $boardingAt): void
    {
        if (
            $trip->status !== TrainTrip::STATUS_SCHEDULED
            || ! $boardingAt->isFuture()
            || ! $trip->train()->where('is_active', true)->exists()
        ) {
            throw ValidationException::withMessages(['trip' => 'This train trip is no longer available for booking.']);
        }
    }

    private function resolveJourney(TrainTrip $trip, int $boardingStationId, int $destinationStationId): array
    {
        if ($boardingStationId === $destinationStationId) {
            throw ValidationException::withMessages(['stations' => 'Choose different boarding and destination stations.']);
        }

        $stops = TrainRouteStop::query()
            ->where('train_id', $trip->train_id)
            ->whereIn('station_id', [$boardingStationId, $destinationStationId])
            ->whereHas('station', fn ($query) => $query->where('is_active', true))
            ->orderBy('stop_order')
            ->lockForUpdate()
            ->get();

        $boarding = $stops->firstWhere('station_id', $boardingStationId);
        $destination = $stops->firstWhere('station_id', $destinationStationId);
        if (! $boarding || ! $destination || $boarding->stop_order >= $destination->stop_order
            || $boarding->departure_offset_minutes === null || $destination->arrival_offset_minutes === null
            || $boarding->departure_offset_minutes >= $destination->arrival_offset_minutes) {
            throw ValidationException::withMessages(['stations' => 'Choose active stations in the order of this train route.']);
        }

        return [
            'boarding_station_id' => $boardingStationId,
            'destination_station_id' => $destinationStationId,
            'boarding_at' => $trip->starts_at->copy()->addMinutes($boarding->departure_offset_minutes),
            'arrival_at' => $trip->starts_at->copy()->addMinutes($destination->arrival_offset_minutes),
        ];
    }

    private function bookingCode(): string
    {
        do {
            $code = 'TR-' . now()->format('Ymd') . '-' . Str::upper(Str::random(8));
        } while (TrainBooking::query()->where('booking_code', $code)->exists());

        return $code;
    }

    private function validatePassengers(array $passengers): void
    {
        Validator::make(['passengers' => $passengers], [
            'passengers' => ['required', 'array', 'min:1', 'max:6'],
            'passengers.*' => ['required', 'array'],
            'passengers.*.name' => ['required', 'string', 'max:255'],
            'passengers.*.age' => ['nullable', 'integer', 'between:0,120'],
            'passengers.*.gender' => ['nullable', 'string', 'max:20'],
            'passengers.*.requires_accessible_seat' => ['sometimes', 'boolean'],
        ])->validate();
    }
}
