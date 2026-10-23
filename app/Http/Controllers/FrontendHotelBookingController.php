<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHotelBookingRequest;
use App\Mail\PlainTextMail;
use App\Services\AdminNotificationMailer;
use App\Models\Country;
use App\Models\HotelBooking;
use App\Models\VenueHotel;
use App\Support\VenuePageSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FrontendHotelBookingController extends Controller
{
    public function index()
    {
        $settings = VenuePageSettings::get();

        $hotels = VenueHotel::query()
            ->select([
                'id',
                'name',
                'city',
                'country',
                'hotel_image',
                'hotel_address',
                'hotel_description',
                'stars',
                'booking_available_from',
                'booking_available_to',
            ])
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(12);

        return view('frontend.venue.index', compact('hotels', 'settings'));
    }

    public function show(VenueHotel $venueHotel)
    {
        $rooms = $this->normalizedRooms($venueHotel);
        $gallery = $this->normalizedGallery($venueHotel);
        $settings = VenuePageSettings::get();
        $directionUrl = $this->buildDirectionUrl(
            (string) ($venueHotel->direction_from ?? ''),
            (string) ($venueHotel->direction_to ?? '')
        );
        $taxRate = (float) ($settings['tax_rate'] ?? 18);
        $taxRateText = rtrim(rtrim(number_format($taxRate, 2, '.', ''), '0'), '.');
        $taxNoteText = str_replace(':tax_rate', $taxRateText, (string) ($settings['tax_note_text'] ?? ':tax_rate% GST will be applicable extra on the above rates.'));

        return view('frontend.venue.show', [
            'hotel' => $venueHotel,
            'rooms' => $rooms,
            'gallery' => $gallery,
            'directionUrl' => $directionUrl,
            'roomSectionHeading' => trim((string) ($venueHotel->room_section_heading ?: 'Choose Your Room')),
            'roomSectionText' => trim((string) ($venueHotel->room_section_text ?: 'Select Your Room Type and review category-wise rates before reservation. Please note that these special rates are available for a limited number of rooms only and will be offered on a first-come, first-served basis.')),
            'gstRate' => $taxRate,
            'taxNoteText' => $taxNoteText,
        ]);
    }

    public function reserve(VenueHotel $venueHotel)
    {
        $settings = VenuePageSettings::get();
        $rooms = $this->normalizedRooms($venueHotel);
        $selectedRoomIndex = (int) request()->integer('room', 0);
        if (! isset($rooms[$selectedRoomIndex])) {
            $selectedRoomIndex = 0;
        }
        $selectedRoom = $rooms[$selectedRoomIndex] ?? null;

        if (! $selectedRoom) {
            return redirect()
                ->route('frontend.venue.show', $venueHotel)
                ->with('message', 'No room is configured for this hotel yet.');
        }

        $taxRateText = rtrim(rtrim(number_format((float) ($settings['tax_rate'] ?? 18), 2, '.', ''), '0'), '.');

        return view('frontend.venue.reserve', [
            'hotel' => $venueHotel,
            'selectedRoom' => $selectedRoom,
            'selectedRoomIndex' => $selectedRoomIndex,
            'bookingFrom' => optional($venueHotel->booking_available_from)->format('Y-m-d'),
            'bookingTo' => optional($venueHotel->booking_available_to)->format('Y-m-d'),
            'settings' => $settings,
            'gstRate' => (float) ($settings['tax_rate'] ?? 18),
            'taxBadgeText' => str_replace(':tax_rate', $taxRateText, (string) ($settings['tax_badge_text'] ?? ':tax_rate% GST extra')),
            'taxSummaryNoteText' => str_replace(':tax_rate', $taxRateText, (string) ($settings['tax_summary_note_text'] ?? '* Taxes will be additionally charged as applied.')),
            'countries' => Country::query()
                ->select(['name'])
                ->whereNotNull('name')
                ->where('name', '!=', '')
                ->orderBy('name')
                ->pluck('name'),
        ]);
    }

    public function store(VenueHotel $venueHotel, StoreHotelBookingRequest $request)
    {
        $payload = $request->validated();
        $bookingCode = 'HB-' . strtoupper(Str::random(8));
        $rooms = $this->normalizedRooms($venueHotel);
        $roomIndex = (int) ($payload['selected_room_index'] ?? 0);
        $selectedRoom = $rooms[$roomIndex] ?? null;

        if (! $selectedRoom) {
            throw ValidationException::withMessages([
                'selected_room_index' => 'Selected room is invalid.',
            ]);
        }

        $checkInDate = Carbon::parse($payload['check_in_date']);
        $checkOutDate = Carbon::parse($payload['check_out_date']);
        if ($venueHotel->booking_available_from && $checkInDate->lt(Carbon::parse($venueHotel->booking_available_from))) {
            throw ValidationException::withMessages([
                'check_in_date' => 'Check-in date must be within hotel booking date range.',
            ]);
        }
        if ($venueHotel->booking_available_to && $checkOutDate->gt(Carbon::parse($venueHotel->booking_available_to))) {
            throw ValidationException::withMessages([
                'check_out_date' => 'Check-out date must be within hotel booking date range.',
            ]);
        }

        $isDoubleOccupancy = (($payload['occupancy'] ?? 'single') === 'double');
        $numberOfGuests = $isDoubleOccupancy ? 2 : 1;
        $selectedRateInr = (float) ($isDoubleOccupancy ? ($selectedRoom['double_rate_inr'] ?? 0) : ($selectedRoom['single_rate_inr'] ?? 0));
        $selectedRateUsd = (float) ($isDoubleOccupancy ? ($selectedRoom['double_rate_usd'] ?? 0) : ($selectedRoom['single_rate_usd'] ?? 0));
        $roomsCount = max(1, (int) ($payload['number_of_rooms'] ?? 1));
        $nights = max(1, $checkInDate->diffInDays($checkOutDate));
        $settings = VenuePageSettings::get();
        $gstRate = (float) ($settings['tax_rate'] ?? 18);

        $subtotalInr = $selectedRateInr * $nights * $roomsCount;
        $gstAmountInr = 0;
        $totalInr = $subtotalInr;

        $subtotalUsd = $selectedRateUsd * $nights * $roomsCount;
        $gstAmountUsd = 0;
        $totalUsd = $subtotalUsd;

        $booking = HotelBooking::query()->create([
            'venue_hotel_id' => $venueHotel->id,
            'booking_code' => $bookingCode,
            'hotel_name' => (string) $venueHotel->name,
            'room_name' => (string) $selectedRoom['name'],
            'rate_inr' => $selectedRateInr,
            'rate_usd' => $selectedRateUsd,
            'full_name' => $payload['full_name'],
            'email' => $payload['email'],
            'phone' => $payload['phone'] ?? null,
            'country' => $payload['country'],
            'occupancy' => $payload['occupancy'],
            'bed_size' => $payload['bed_size'] ?? null,
            'preference' => $payload['preference'] ?? null,
            'check_in_date' => $payload['check_in_date'],
            'check_out_date' => $payload['check_out_date'],
            'number_of_guests' => $numberOfGuests,
            'number_of_rooms' => $roomsCount,
            'number_of_nights' => $nights,
            'subtotal_inr' => $subtotalInr,
            'subtotal_usd' => $subtotalUsd,
            'gst_rate' => $gstRate,
            'gst_amount_inr' => $gstAmountInr,
            'gst_amount_usd' => $gstAmountUsd,
            'total_inr' => $totalInr,
            'total_usd' => $totalUsd,
            'special_requests' => $payload['special_requests'] ?? null,
            'status' => 'pending',
        ]);

        $this->sendBookingEmails($booking);

        return redirect()
            ->route('frontend.venue.reserve', ['venueHotel' => $venueHotel->id, 'room' => $roomIndex])
            ->with('message', 'Thank you for submitting your details. Our team will share your details with the hotel reservation team, and you will receive a confirmation directly from the hotel soon.');
    }

    private function sendBookingEmails(HotelBooking $booking): void
    {
        $checkIn = Carbon::parse($booking->check_in_date)->format('d-M-Y');
        $checkOut = Carbon::parse($booking->check_out_date)->format('d-M-Y');

        $message = implode("\n", [
            'Hotel Booking Request',
            'Booking Code: ' . $booking->booking_code,
            'Hotel: ' . ($booking->hotel_name ?: '-'),
            'Room: ' . ($booking->room_name ?: '-'),
            'Rate INR: ' . number_format((float) $booking->rate_inr, 2),
            'Rate USD: ' . number_format((float) $booking->rate_usd, 2),
            'Name: ' . $booking->full_name,
            'Email: ' . $booking->email,
            'Phone: ' . ($booking->phone ?: '-'),
            'Country: ' . ($booking->country ?: '-'),
            'Occupancy: ' . ($booking->occupancy ?: '-'),
            'Bed Size: ' . ($booking->bed_size ?: '-'),
            'Preference: ' . ($booking->preference ?: '-'),
            'Check-in: ' . $checkIn,
            'Check-out: ' . $checkOut,
            'Guests: ' . $booking->number_of_guests,
            'Rooms: ' . $booking->number_of_rooms,
            'Nights: ' . ($booking->number_of_nights ?: '-'),
            'Subtotal INR: ' . number_format((float) ($booking->subtotal_inr ?? 0), 2),
            'GST Rate: ' . number_format((float) ($booking->gst_rate ?? 0), 2) . '%',
            'GST INR: ' . number_format((float) ($booking->gst_amount_inr ?? 0), 2),
            'Total INR: ' . number_format((float) ($booking->total_inr ?? 0), 2),
            'Special Requests: ' . ($booking->special_requests ?: '-'),
        ]);

        try {
            Mail::to($booking->email)->queue(
                new PlainTextMail(
                    'Hotel Booking Confirmation: ' . $booking->booking_code,
                    $message
                )
            );
        } catch (\Throwable $e) {
            // Keep booking flow successful even if SMTP fails.
        }

        app(AdminNotificationMailer::class)->send('hotel_booking', [
            'booking_code' => $booking->booking_code,
            'hotel_name' => $booking->hotel_name ?: '-',
            'room_name' => $booking->room_name ?: '-',
            'full_name' => $booking->full_name,
            'email' => $booking->email,
            'phone' => $booking->phone ?: '-',
            'country' => $booking->country ?: '-',
            'occupancy' => $booking->occupancy ?: '-',
            'bed_size' => $booking->bed_size ?: '-',
            'preference' => $booking->preference ?: '-',
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
            'number_of_guests' => $booking->number_of_guests,
            'number_of_rooms' => $booking->number_of_rooms,
            'number_of_nights' => $booking->number_of_nights,
            'subtotal_inr' => number_format((float) ($booking->subtotal_inr ?? 0), 2),
            'gst_rate' => number_format((float) ($booking->gst_rate ?? 0), 2),
            'gst_amount_inr' => number_format((float) ($booking->gst_amount_inr ?? 0), 2),
            'total_inr' => number_format((float) ($booking->total_inr ?? 0), 2),
            'special_requests' => $booking->special_requests ?: '-',
        ]);
    }

    private function buildDirectionUrl(string $from, string $to): string
    {
        $from = trim($from);
        $to = trim($to);
        if ($from === '' || $to === '') {
            return '';
        }

        return 'https://www.google.com/maps/dir/?api=1&origin=' . rawurlencode($from) . '&destination=' . rawurlencode($to);
    }

    private function normalizedRooms(VenueHotel $venueHotel): array
    {
        $rooms = is_array($venueHotel->hotel_rooms) ? $venueHotel->hotel_rooms : [];
        $result = [];

        foreach ($rooms as $index => $room) {
            if (! is_array($room)) {
                continue;
            }

            $name = trim((string) ($room['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $image = trim((string) ($room['image'] ?? ''));
            $benefits = $room['benefits'] ?? [];
            if (is_string($benefits)) {
                $benefits = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $benefits))));
            }
            if (! is_array($benefits)) {
                $benefits = [];
            }

            $result[] = [
                'index' => (int) $index,
                'name' => $name,
                'caption' => trim((string) ($room['caption'] ?? '')),
                'size' => trim((string) ($room['size'] ?? '')),
                'image' => $this->assetPath($image),
                'benefits' => $benefits,
                'rate_inr' => (float) ($room['rate_inr'] ?? 0),
                'single_rate_inr' => (float) ($room['single_rate_inr'] ?? $room['rate_inr'] ?? 0),
                'double_rate_inr' => (float) ($room['double_rate_inr'] ?? $room['rate_inr'] ?? 0),
                'single_rate_usd' => (float) ($room['single_rate_usd'] ?? $room['rate_usd'] ?? 0),
                'double_rate_usd' => (float) ($room['double_rate_usd'] ?? $room['rate_usd'] ?? 0),
                'badge' => trim((string) ($room['badge'] ?? '')),
            ];
        }

        return $result;
    }

    private function normalizedGallery(VenueHotel $venueHotel): array
    {
        $gallery = is_array($venueHotel->hotel_gallery) ? $venueHotel->hotel_gallery : [];
        $result = [];
        foreach ($gallery as $item) {
            $value = trim((string) $item);
            if ($value !== '') {
                $result[] = $this->assetPath($value);
            }
        }

        return $result;
    }

    private function assetPath(string $value): string
    {
        if ($value === '') {
            return asset('images/misc/1.webp');
        }

        return (str_starts_with($value, 'http://')
            || str_starts_with($value, 'https://')
            || str_starts_with($value, '//')
            || str_starts_with($value, '/'))
            ? $value
            : asset($value);
    }
}
