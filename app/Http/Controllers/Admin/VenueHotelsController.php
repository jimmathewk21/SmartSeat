<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VenueHotel;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class VenueHotelsController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = VenueHotel::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search): void {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $venueHotels = $query
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->appends($request->query());

        return view('admin.venueHotels.index', compact('venueHotels'));
    }

    public function create()
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.venueHotels.create');
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        VenueHotel::query()->create($this->validateRequest($request));

        return redirect()->route('admin.venue-hotels.index')->with('message', 'Hotel created successfully.');
    }

    public function show(VenueHotel $venueHotel)
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.venueHotels.show', compact('venueHotel'));
    }

    public function edit(VenueHotel $venueHotel)
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.venueHotels.edit', compact('venueHotel'));
    }

    public function update(Request $request, VenueHotel $venueHotel)
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $venueHotel->update($this->validateRequest($request));

        return redirect()->route('admin.venue-hotels.index')->with('message', 'Hotel updated successfully.');
    }

    public function destroy(VenueHotel $venueHotel)
    {
        abort_if(Gate::denies('venue_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $venueHotel->delete();

        return back()->with('message', 'Hotel deleted successfully.');
    }

    private function validateRequest(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:120'],
            'country' => ['nullable', 'string', 'max:120'],
            'stars' => ['nullable', 'integer', 'min:0', 'max:5'],
            'hotel_image' => ['nullable', 'string', 'max:1000'],
            'hotel_image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'hotel_gallery_text' => ['nullable', 'string'],
            'hotel_gallery_files' => ['nullable', 'array'],
            'hotel_gallery_files.*' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'hotel_address' => ['nullable', 'string'],
            'hotel_description' => ['nullable', 'string'],
            'room_section_heading' => ['nullable', 'string', 'max:255'],
            'room_section_text' => ['nullable', 'string'],
            'rooms' => ['nullable', 'array'],
            'rooms.*.name' => ['nullable', 'string', 'max:255'],
            'rooms.*.caption' => ['nullable', 'string', 'max:255'],
            'rooms.*.size' => ['nullable', 'string', 'max:120'],
            'rooms.*.existing_image' => ['nullable', 'string', 'max:1000'],
            'rooms.*.image_file' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp,svg', 'max:4096'],
            'rooms.*.benefits_text' => ['nullable', 'string'],
            'rooms.*.single_rate_inr' => ['nullable', 'numeric', 'min:0'],
            'rooms.*.double_rate_inr' => ['nullable', 'numeric', 'min:0'],
            'rooms.*.single_rate_usd' => ['nullable', 'numeric', 'min:0'],
            'rooms.*.double_rate_usd' => ['nullable', 'numeric', 'min:0'],
            'rooms.*.badge' => ['nullable', 'string', 'max:120'],
            'booking_available_from' => ['nullable', 'date'],
            'booking_available_to' => ['nullable', 'date', 'after_or_equal:booking_available_from'],
            'direction_from' => ['nullable', 'string', 'max:255'],
            'direction_to' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $gallery = $this->resolveGalleryPaths($request, $validated);
        $hotelImage = trim((string) ($validated['hotel_image'] ?? ''));
        if ($request->hasFile('hotel_image_file')) {
            $hotelImage = $this->storeHotelCoverImage($request->file('hotel_image_file'));
        }
        $uploadedRoomRows = (array) $request->file('rooms', []);

        $rooms = [];
        foreach ((array) ($validated['rooms'] ?? []) as $index => $room) {
            if (! is_array($room)) {
                continue;
            }

            $name = trim((string) ($room['name'] ?? ''));
            if ($name === '') {
                continue;
            }

            $benefits = array_values(array_filter(array_map(
                'trim',
                preg_split('/\r\n|\r|\n/', (string) ($room['benefits_text'] ?? ''))
            )));

            $imagePath = trim((string) ($room['existing_image'] ?? ''));
            $uploadedImage = $uploadedRoomRows[$index]['image_file'] ?? null;
            if ($uploadedImage) {
                $imagePath = $this->storeRoomImage($uploadedImage);
            }

            $rooms[] = [
                'name' => $name,
                'caption' => trim((string) ($room['caption'] ?? '')),
                'size' => trim((string) ($room['size'] ?? '')),
                'image' => $imagePath,
                'benefits' => $benefits,
                'single_rate_inr' => (float) ($room['single_rate_inr'] ?? $room['rate_inr'] ?? 0),
                'double_rate_inr' => (float) ($room['double_rate_inr'] ?? $room['rate_inr'] ?? 0),
                'single_rate_usd' => (float) ($room['single_rate_usd'] ?? $room['rate_usd'] ?? 0),
                'double_rate_usd' => (float) ($room['double_rate_usd'] ?? $room['rate_usd'] ?? 0),
                'badge' => trim((string) ($room['badge'] ?? '')),
            ];
        }

        return [
            'name' => $validated['name'],
            'city' => $validated['city'] ?? null,
            'country' => $validated['country'] ?? null,
            'stars' => (int) ($validated['stars'] ?? 0),
            'hotel_image' => $hotelImage,
            'hotel_gallery' => $gallery,
            'hotel_address' => $validated['hotel_address'] ?? null,
            'hotel_description' => $validated['hotel_description'] ?? null,
            'room_section_heading' => $validated['room_section_heading'] ?? null,
            'room_section_text' => $validated['room_section_text'] ?? null,
            'hotel_rooms' => $rooms,
            'booking_available_from' => $validated['booking_available_from'] ?? null,
            'booking_available_to' => $validated['booking_available_to'] ?? null,
            'direction_from' => $validated['direction_from'] ?? null,
            'direction_to' => $validated['direction_to'] ?? null,
            'sort_order' => (int) ($validated['sort_order'] ?? 0),
            'is_active' => (bool) ($validated['is_active'] ?? false),
        ];
    }

    private function resolveGalleryPaths(Request $request, array $validated): array
    {
        $gallery = [];

        foreach (preg_split('/\r\n|\r|\n/', (string) ($validated['hotel_gallery_text'] ?? '')) as $line) {
            $line = trim((string) $line);
            if ($line !== '') {
                $gallery[] = $line;
            }
        }

        if ($request->hasFile('hotel_gallery_files')) {
            $directory = public_path('uploads/venue-hotels/gallery');
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
            }

            foreach ((array) $request->file('hotel_gallery_files') as $image) {
                if (! $image) {
                    continue;
                }

                $extension = Str::lower($image->getClientOriginalExtension());
                $fileName = 'venue-gallery-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
                $image->move($directory, $fileName);
                $gallery[] = 'uploads/venue-hotels/gallery/' . $fileName;
            }
        }

        return array_values(array_unique($gallery));
    }

    private function storeRoomImage($image): string
    {
        $directory = public_path('uploads/venue-hotels/rooms');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = Str::lower($image->getClientOriginalExtension());
        $fileName = 'venue-room-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
        $image->move($directory, $fileName);

        return 'uploads/venue-hotels/rooms/' . $fileName;
    }

    private function storeHotelCoverImage($image): string
    {
        $directory = public_path('uploads/venue-hotels/covers');
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = Str::lower($image->getClientOriginalExtension());
        $fileName = 'venue-cover-' . now()->format('YmdHis') . '-' . Str::lower(Str::random(8)) . '.' . $extension;
        $image->move($directory, $fileName);

        return 'uploads/venue-hotels/covers/' . $fileName;
    }
}
