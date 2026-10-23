<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEventOrderRequest;
use App\Http\Requests\VerifyRazorpayPaymentRequest;
use App\Models\Country;
use App\Models\Event;
use App\Services\RazorpayTicketService;
use App\Support\TicketCheckoutSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;
use Symfony\Component\HttpFoundation\Response;

class FrontendTicketController extends Controller
{
    public function show(string $event, Request $request)
    {
        $event = $this->resolveEventBySlug($event);

        abort_if(! $event->isBookableUpcoming(), Response::HTTP_NOT_FOUND);

        $event->load(['ticketPlans' => function ($query) {
            $query->select([
                'id',
                'event_id',
                'plan_index',
                'sort_order',
                'name',
                'caption',
                'mrp_inr_paise',
                'sale_inr_paise',
                'mrp_usd_cents',
                'sale_usd_cents',
                'benefits',
                'is_active',
                'valid_till_text',
            ])
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->orderBy('id');
        }]);

        $selectedPlanId = (int) $request->integer('plan', 0);
        if ($selectedPlanId <= 0 || ! $event->ticketPlans->contains('id', $selectedPlanId)) {
            $selectedPlanId = (int) optional($event->ticketPlans->first())->id;
        }

        $countries = Country::query()
            ->select(['id', 'name', 'short_code'])
            ->whereNotNull('short_code')
            ->orderBy('name')
            ->get()
            ->filter(fn ($country) => strlen((string) $country->short_code) === 2)
            ->values();

        $ticketCheckoutSettings = TicketCheckoutSettings::get();
        $minTicketQuantity = max(1, (int) ($ticketCheckoutSettings['min_ticket_quantity'] ?? 1));
        $maxTicketQuantity = max($minTicketQuantity, (int) ($ticketCheckoutSettings['max_ticket_quantity'] ?? 10));

        return view('frontend.events.tickets', compact(
            'event',
            'selectedPlanId',
            'countries',
            'ticketCheckoutSettings',
            'minTicketQuantity',
            'maxTicketQuantity'
        ));
    }

    public function createOrder(string $event, StoreEventOrderRequest $request, RazorpayTicketService $service)
    {
        $event = $this->resolveEventBySlug($event);

        abort_if(! $event->isBookableUpcoming(), 422, 'Tickets are available only for the upcoming event.');

        $event->load('ticketPlans');
        $planId = (int) $request->input('event_ticket_plan_id');
        $plan = $event->ticketPlans->firstWhere('id', $planId);

        if (! $plan) {
            return response()->json(['message' => 'Selected plan is not available for this event.'], 422);
        }

        try {
            $result = $service->createOrderWithDetails(
                $request->user(),
                $event,
                $plan,
                (int) $request->input('quantity'),
                (string) $request->input('billing_country_code'),
                [
                    'billing_company_name' => (string) $request->input('billing_company_name'),
                    'billing_address_line_1' => (string) $request->input('billing_address_line_1'),
                    'billing_address_line_2' => (string) $request->input('billing_address_line_2', ''),
                    'gstin' => (string) $request->input('gstin', ''),
                ],
                (array) $request->input('delegates', []),
                $request->input('promo_code'),
                (string) $request->input('preferred_currency', '')
            );
        } catch (Throwable $exception) {
            $message = 'Unable to initialize payment order right now.';
            if (
                $exception instanceof HttpExceptionInterface
                && $exception->getStatusCode() >= 400
                && $exception->getStatusCode() < 500
                && trim((string) $exception->getMessage()) !== ''
            ) {
                $message = (string) $exception->getMessage();
            }

            return response()->json([
                'message' => $message,
            ], 422);
        }

        return response()->json([
            'booking_id' => $result['booking']->id,
            'booking_code' => $result['booking']->booking_code,
            'razorpay_order_id' => $result['order_id'],
            'amount' => $result['amount'],
            'currency' => $result['currency'],
            'display_currency' => $result['display_currency'],
            'display_unit_amount' => $result['display_unit_amount_minor'],
            'display_total_amount' => $result['display_total_amount_minor'],
            'subtotal_amount' => $result['subtotal_minor'],
            'promo_discount_amount' => $result['promo_discount_minor'],
            'gst_charge_amount' => $result['gst_charge_minor'],
            'gateway_charge_amount' => $result['gateway_charge_minor'],
            'grand_total_amount' => $result['grand_total_minor'],
            'promo_code' => $result['promo_code'],
            'billing_country_code' => $result['billing_country_code'],
            'fx_rate_usd_to_inr' => $result['fx_rate_usd_to_inr'],
            'key' => $result['key'],
            'name' => $result['name'],
            'description' => $result['description'],
            'redirect_url' => route('my-tickets.index'),
            'prefill' => [
                'name' => $request->user()->name,
                'email' => $request->user()->email,
            ],
            'theme' => [
                'color' => '#6f42f5',
            ],
        ]);
    }

    public function verifyPayment(VerifyRazorpayPaymentRequest $request, RazorpayTicketService $service)
    {
        try {
            $booking = $service->verifyAndCapturePayment($request->user(), $request->validated());
        } catch (Throwable $exception) {
            return response()->json([
                'message' => 'Payment verification failed.',
            ], 422);
        }

        return response()->json([
            'message' => 'Payment verified successfully.',
            'booking_code' => $booking->booking_code,
            'status' => $booking->status,
            'redirect_url' => route('my-tickets.index'),
        ]);
    }

    public function paymentFailed(Request $request, RazorpayTicketService $service)
    {
        $validated = $request->validate([
            'razorpay_order_id' => ['required', 'string', 'max:255'],
            'razorpay_payment_id' => ['nullable', 'string', 'max:255'],
            'reason' => ['nullable', 'string', 'max:500'],
            'payload' => ['nullable', 'array'],
        ]);

        $booking = $service->markBookingFailedByOrderId(
            $request->user(),
            (string) $validated['razorpay_order_id'],
            (string) ($validated['reason'] ?? 'Payment failed'),
            (string) ($validated['razorpay_payment_id'] ?? ''),
            (array) ($validated['payload'] ?? [])
        );

        return response()->json([
            'message' => 'Payment failure recorded.',
            'recorded' => (bool) $booking,
        ]);
    }

    public function webhook(Request $request, RazorpayTicketService $service)
    {
        $signature = (string) $request->header('X-Razorpay-Signature', '');
        $processed = $service->processWebhook($request->all(), $signature);

        return response()->json(['processed' => $processed], $processed ? 200 : 400);
    }

    public function summary(string $event, StoreEventOrderRequest $request, RazorpayTicketService $service)
    {
        $event = $this->resolveEventBySlug($event);

        abort_if(! $event->isBookableUpcoming(), 422, 'Tickets are available only for the upcoming event.');

        $event->load('ticketPlans');
        $planId = (int) $request->input('event_ticket_plan_id');
        $plan = $event->ticketPlans->firstWhere('id', $planId);

        if (! $plan) {
            return response()->json(['message' => 'Selected plan is not available for this event.'], 422);
        }

        try {
            $summary = $service->previewCheckout(
                $event,
                $plan,
                (int) $request->input('quantity'),
                (string) $request->input('billing_country_code'),
                $request->input('promo_code'),
                (string) $request->input('preferred_currency', ''),
                (string) $request->input('gstin', '')
            );
        } catch (Throwable $exception) {
            return response()->json([
                'message' => $exception->getMessage() ?: 'Unable to build summary.',
            ], 422);
        }

        return response()->json($summary);
    }

    private function resolveEventBySlug(string $value): Event
    {
        $rawSlug = trim($value);
        $normalizedSlug = Str::slug($rawSlug);
        $canonicalRawSlug = Str::lower(str_replace(['-', '_', ' '], '', $rawSlug));

        return Event::query()
            ->where(function ($query) use ($rawSlug, $normalizedSlug, $canonicalRawSlug) {
                $query->whereRaw('LOWER(slug) = ?', [mb_strtolower($rawSlug)]);

                if ($normalizedSlug !== '' && mb_strtolower($normalizedSlug) !== mb_strtolower($rawSlug)) {
                    $query->orWhereRaw('LOWER(slug) = ?', [mb_strtolower($normalizedSlug)]);
                }

                if ($canonicalRawSlug !== '') {
                    $query->orWhereRaw(
                        "REPLACE(REPLACE(REPLACE(LOWER(slug), '-', ''), '_', ''), ' ', '') = ?",
                        [$canonicalRawSlug]
                    );
                }
            })
            ->firstOrFail();
    }
}
