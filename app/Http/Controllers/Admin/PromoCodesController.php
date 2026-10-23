<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class PromoCodesController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = PromoCode::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search): void {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $promoCodes = $query->orderByDesc('id')->paginate(25)->appends($request->query());

        return view('admin.promoCodes.index', compact('promoCodes'));
    }

    public function create()
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return view('admin.promoCodes.create');
    }

    public function store(Request $request)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $data = $this->validated($request);
        PromoCode::create($data);
        return redirect()->route('admin.promo-codes.index')->with('message', 'Promo code created.');
    }

    public function edit(PromoCode $promoCode)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        return view('admin.promoCodes.edit', compact('promoCode'));
    }

    public function update(Request $request, PromoCode $promoCode)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $data = $this->validated($request, $promoCode->id);
        $promoCode->update($data);
        return redirect()->route('admin.promo-codes.index')->with('message', 'Promo code updated.');
    }

    public function destroy(PromoCode $promoCode)
    {
        abort_if(Gate::denies('event_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        $promoCode->delete();
        return back()->with('message', 'Promo code deleted.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'size:6', Rule::unique('promo_codes', 'code')->ignore($ignoreId)],
            'name' => ['nullable', 'string', 'max:255'],
            'discount_type' => ['required', 'in:percentage,flat,quantity'],
            'discount_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'flat_inr' => ['nullable', 'numeric', 'min:0'],
            'flat_usd' => ['nullable', 'numeric', 'min:0'],
            'quantity_buy' => ['nullable', 'integer', 'min:0', 'max:100'],
            'quantity_pay' => ['nullable', 'integer', 'min:0', 'max:100'],
            'max_uses' => ['nullable', 'integer', 'min:1'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        return [
            'code' => strtoupper((string) $validated['code']),
            'name' => $validated['name'] ?? null,
            'discount_type' => $validated['discount_type'],
            'discount_percent' => (int) ($validated['discount_percent'] ?? 0),
            'flat_inr_paise' => (int) round(((float) ($validated['flat_inr'] ?? 0)) * 100),
            'flat_usd_cents' => (int) round(((float) ($validated['flat_usd'] ?? 0)) * 100),
            'quantity_buy' => (int) ($validated['quantity_buy'] ?? 0),
            'quantity_pay' => (int) ($validated['quantity_pay'] ?? 0),
            'max_uses' => $validated['max_uses'] ?? null,
            'starts_at' => $validated['starts_at'] ?? null,
            'ends_at' => $validated['ends_at'] ?? null,
            'is_active' => (bool) $request->boolean('is_active', true),
        ];
    }
}

