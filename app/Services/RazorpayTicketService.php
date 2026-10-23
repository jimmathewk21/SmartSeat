<?php

namespace App\Services;

use App\Models\BookingTicket;
use App\Models\Event;
use App\Models\EventBooking;
use App\Models\EventBookingDelegate;
use App\Models\EventTicketPlan;
use App\Models\Participant;
use App\Models\PaymentWebhook;
use App\Models\PromoCode;
use App\Models\User;
use App\Support\PaymentGatewaySettings;
use App\Support\TicketFxSettings;
use App\Support\TicketCheckoutSettings;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Razorpay\Api\Api;
use Throwable;

class RazorpayTicketService
{
    public function createOrder(User $user, Event $event, EventTicketPlan $plan, int $quantity, string $countryCode, ?string $preferredCurrency = null): array
    {
        return $this->createOrderWithDetails($user, $event, $plan, $quantity, $countryCode, [], [], null, $preferredCurrency);
    }

    public function createOrderWithDetails(
        User $user,
        Event $event,
        EventTicketPlan $plan,
        int $quantity,
        string $countryCode,
        array $billingDetails,
        array $delegates,
        ?string $promoCode = null,
        ?string $preferredCurrency = null
    ): array
    {
        if (! $event->isBookableUpcoming()) {
            abort(422, 'Tickets are available only for the upcoming event.');
        }

        if (! $plan->is_active || $plan->event_id !== $event->id) {
            abort(422, 'Invalid ticket plan selected.');
        }

        if (! PaymentGatewaySettings::enabled()) {
            abort(422, 'Online payment is currently disabled.');
        }

        $minQty = $this->getMinTicketQuantity();
        $maxQty = $this->getMaxTicketQuantity();
        if ($quantity < $minQty || $quantity > $maxQty) {
            abort(422, 'Quantity must be between ' . $minQty . ' and ' . $maxQty . '.');
        }

        if (count($delegates) !== $quantity) {
            abort(422, 'Delegate details count must match selected quantity.');
        }

        $currency = (string) Config::get('services.razorpay.currency', 'INR');
        if ($currency !== 'INR') {
            abort(422, 'Only INR currency is supported.');
        }

        $countryCode = strtoupper(trim($countryCode));
        $preferredCurrency = strtoupper(trim((string) $preferredCurrency));
        if (! in_array($preferredCurrency, ['INR', 'USD'], true)) {
            $preferredCurrency = '';
        }

        $displayCurrency = $preferredCurrency !== '' ? $preferredCurrency : ($countryCode === 'IN' ? 'INR' : 'USD');
        $fxRate = null;

        if ($displayCurrency === 'INR') {
            $displayUnitMinor = (int) $plan->sale_inr_paise;
        } else {
            $displayUnitMinor = (int) $plan->sale_usd_cents;
            $fxRate = $this->getUsdToInrRate();
        }

        if ($displayUnitMinor <= 0) {
            abort(422, 'Selected ticket plan amount is invalid.');
        }

        $pricing = $this->buildCheckoutPricing(
            $displayCurrency,
            $displayUnitMinor,
            $quantity,
            $countryCode,
            $promoCode
        );
        $displayGrandMinor = (int) $pricing['grand_total_minor'];
        $displaySubtotalMinor = (int) $pricing['subtotal_minor'];
        $promoDiscountMinor = (int) $pricing['promo_discount_minor'];
        $gstChargeMinor = (int) $pricing['gst_charge_minor'];
        $gatewayChargeMinor = (int) $pricing['gateway_charge_minor'];
        $appliedPromo = $pricing['promo'] ?? null;

        if ($displayGrandMinor <= 0) {
            abort(422, 'Calculated amount is invalid.');
        }

        $amountPaise = $displayCurrency === 'INR'
            ? $displayGrandMinor
            : (int) round((($displayGrandMinor / 100) * $fxRate) * 100);

        $booking = DB::transaction(function () use ($user, $event, $plan, $quantity, $amountPaise, $currency, $countryCode, $displayCurrency, $displayUnitMinor, $displayGrandMinor, $displaySubtotalMinor, $promoDiscountMinor, $gatewayChargeMinor, $appliedPromo, $fxRate, $billingDetails, $delegates) {
            $participantId = Participant::query()
                ->where('user_id', $user->id)
                ->value('id');

            $booking = EventBooking::create([
                'booking_code' => $this->generateBookingCode(),
                'user_id' => $user->id,
                'participant_id' => $participantId,
                'event_id' => $event->id,
                'event_ticket_plan_id' => $plan->id,
                'quantity' => $quantity,
                'unit_price_paise' => (int) round($amountPaise / max(1, $quantity)),
                'total_amount_paise' => $amountPaise,
                'currency' => $currency,
                'billing_country_code' => $countryCode,
                'display_currency' => $displayCurrency,
                'display_unit_amount_minor' => $displayUnitMinor,
                'display_total_amount_minor' => $displayGrandMinor,
                'subtotal_display_minor' => $displaySubtotalMinor,
                'grand_total_display_minor' => $displayGrandMinor,
                'promo_code' => $appliedPromo?->code,
                'promo_discount_minor' => $promoDiscountMinor,
                'gateway_charge_minor' => $gatewayChargeMinor,
                'billing_company_name' => (string) ($billingDetails['billing_company_name'] ?? ''),
                'billing_address_line_1' => (string) ($billingDetails['billing_address_line_1'] ?? ''),
                'billing_address_line_2' => (string) ($billingDetails['billing_address_line_2'] ?? ''),
                'gstin' => (string) ($billingDetails['gstin'] ?? ''),
                'fx_rate_usd_to_inr' => $fxRate,
                'status' => EventBooking::STATUS_PENDING,
            ]);

            foreach ($delegates as $index => $delegate) {
                EventBookingDelegate::create([
                    'event_booking_id' => $booking->id,
                    'delegate_index' => $index + 1,
                    'delegate_name' => (string) ($delegate['delegate_name'] ?? ''),
                    'designation' => (string) ($delegate['designation'] ?? ''),
                    'company_name' => (string) ($delegate['company_name'] ?? ''),
                    'country' => (string) ($delegate['country'] ?? ''),
                    'email' => (string) ($delegate['email'] ?? ''),
                    'mobile' => (string) ($delegate['mobile'] ?? ''),
                ]);
            }

            if ($appliedPromo) {
                $appliedPromo->increment('used_count');
            }

            return $booking;
        });

        try {
            $api = $this->apiClient();
            $order = $api->order->create([
                'receipt' => $booking->booking_code,
                'amount' => $amountPaise,
                'currency' => $currency,
                'notes' => [
                    'booking_id' => (string) $booking->id,
                    'event_id' => (string) $event->id,
                    'plan_id' => (string) $plan->id,
                ],
            ]);
        } catch (Throwable $exception) {
            $booking->forceFill([
                'status' => EventBooking::STATUS_FAILED,
                'failure_reason' => 'Unable to create payment order.',
            ])->save();
            $this->sendAdminFailedBookingNotification($booking->fresh(['event', 'plan', 'user', 'delegates']));
            throw $exception;
        }

        $booking->forceFill([
            'razorpay_order_id' => $order['id'],
            'payment_payload' => json_encode(['order' => $order->toArray()], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ])->save();

        return [
            'booking' => $booking->fresh(['event', 'plan']),
            'order_id' => $order['id'],
            'amount' => $amountPaise,
            'currency' => $currency,
            'display_currency' => $displayCurrency,
            'display_unit_amount_minor' => $displayUnitMinor,
            'display_total_amount_minor' => $displayGrandMinor,
            'subtotal_minor' => $displaySubtotalMinor,
            'promo_discount_minor' => $promoDiscountMinor,
            'gst_charge_minor' => $gstChargeMinor,
            'gateway_charge_minor' => $gatewayChargeMinor,
            'grand_total_minor' => $displayGrandMinor,
            'promo_code' => $appliedPromo?->code,
            'billing_country_code' => $countryCode,
            'fx_rate_usd_to_inr' => $fxRate,
            'key' => (string) Config::get('services.razorpay.key_id', ''),
            'name' => Config::get('app.name', 'Event Management'),
            'description' => $event->title . ' - ' . $plan->name,
        ];
    }

    public function previewCheckout(Event $event, EventTicketPlan $plan, int $quantity, string $countryCode, ?string $promoCode = null, ?string $preferredCurrency = null, ?string $gstin = null): array
    {
        if (! $event->isBookableUpcoming()) {
            abort(422, 'Tickets are available only for the upcoming event.');
        }

        if (! $plan->is_active || $plan->event_id !== $event->id) {
            abort(422, 'Invalid ticket plan selected.');
        }

        $minQty = $this->getMinTicketQuantity();
        $maxQty = $this->getMaxTicketQuantity();
        if ($quantity < $minQty || $quantity > $maxQty) {
            abort(422, 'Quantity must be between ' . $minQty . ' and ' . $maxQty . '.');
        }

        $countryCode = strtoupper(trim($countryCode));
        $preferredCurrency = strtoupper(trim((string) $preferredCurrency));
        if (! in_array($preferredCurrency, ['INR', 'USD'], true)) {
            $preferredCurrency = '';
        }

        $displayCurrency = $preferredCurrency !== '' ? $preferredCurrency : ($countryCode === 'IN' ? 'INR' : 'USD');
        $displayUnitMinor = $displayCurrency === 'INR' ? (int) $plan->sale_inr_paise : (int) $plan->sale_usd_cents;
        if ($displayUnitMinor <= 0) {
            abort(422, 'Selected ticket plan amount is invalid.');
        }

        $pricing = $this->buildCheckoutPricing(
            $displayCurrency,
            $displayUnitMinor,
            $quantity,
            $countryCode,
            $promoCode
        );

        return [
            'display_currency' => $displayCurrency,
            'display_unit_minor' => $displayUnitMinor,
            'subtotal_minor' => (int) $pricing['subtotal_minor'],
            'promo_discount_minor' => (int) $pricing['promo_discount_minor'],
            'gst_charge_minor' => (int) $pricing['gst_charge_minor'],
            'gateway_charge_minor' => (int) $pricing['gateway_charge_minor'],
            'grand_total_minor' => (int) $pricing['grand_total_minor'],
            'promo_code' => $pricing['promo']?->code,
        ];
    }

    public function verifyAndCapturePayment(User $user, array $payload): EventBooking
    {
        $orderId = (string) ($payload['razorpay_order_id'] ?? '');
        $paymentId = (string) ($payload['razorpay_payment_id'] ?? '');
        $signature = (string) ($payload['razorpay_signature'] ?? '');

        if ($orderId === '' || $paymentId === '' || $signature === '') {
            abort(422, 'Payment response is incomplete.');
        }

        $booking = EventBooking::query()
            ->where('razorpay_order_id', $orderId)
            ->where('user_id', $user->id)
            ->with(['event', 'plan', 'tickets'])
            ->firstOrFail();

        if ($booking->status === EventBooking::STATUS_PAID) {
            return $booking;
        }

        $api = $this->apiClient();
        $api->utility->verifyPaymentSignature([
            'razorpay_order_id' => $orderId,
            'razorpay_payment_id' => $paymentId,
            'razorpay_signature' => $signature,
        ]);

        DB::transaction(function () use ($booking, $paymentId, $signature, $payload) {
            $booking->forceFill([
                'status' => EventBooking::STATUS_PAID,
                'razorpay_payment_id' => $paymentId,
                'razorpay_signature' => $signature,
                'paid_at' => now(),
                'failure_reason' => null,
                'payment_payload' => json_encode(['verify' => $payload], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ])->save();

            $this->ensureTicketsGenerated($booking);
        });

        $this->sendAdminPaidBookingNotification($booking->fresh(['event', 'plan', 'user', 'delegates']));

        return $booking->fresh(['event', 'plan', 'tickets']);
    }

    public function processWebhook(array $payload, string $signature): bool
    {
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
        $webhookSecret = (string) Config::get('services.razorpay.webhook_secret', '');
        $signatureValid = $this->isWebhookSignatureValid($body, $signature, $webhookSecret);

        $eventName = (string) ($payload['event'] ?? '');
        $eventId = (string) ($payload['payload']['payment']['entity']['id'] ?? $payload['payload']['order']['entity']['id'] ?? '');
        $entityId = (string) ($payload['payload']['payment']['entity']['id'] ?? '');
        $payloadHash = hash('sha256', $body);

        if ($eventId !== '') {
            $webhookRow = PaymentWebhook::query()->firstOrNew([
                'provider' => 'razorpay',
                'event_id' => $eventId,
            ]);
        } else {
            $webhookRow = PaymentWebhook::query()->firstOrNew([
                'provider' => 'razorpay',
                'payload_hash' => $payloadHash,
            ]);
        }

        if ($webhookRow->exists && $webhookRow->processed) {
            return true;
        }

        $webhookRow->fill([
            'event_name' => $eventName,
            'entity_id' => $entityId !== '' ? $entityId : null,
            'payload_hash' => $payloadHash,
            'signature_valid' => $signatureValid,
            'processed' => false,
            'payload' => $body,
        ]);
        $webhookRow->save();

        if (! $signatureValid) {
            return false;
        }

        $orderId = (string) ($payload['payload']['payment']['entity']['order_id'] ?? $payload['payload']['order']['entity']['id'] ?? '');
        if ($orderId === '') {
            $webhookRow->forceFill(['processed' => true])->save();
            return true;
        }

        $booking = EventBooking::query()
            ->where('razorpay_order_id', $orderId)
            ->with('tickets')
            ->first();

        if (! $booking) {
            $webhookRow->forceFill(['processed' => true])->save();
            return true;
        }

        $sendPaidNotification = false;

        DB::transaction(function () use ($booking, $eventName, $payload, $webhookRow, &$sendPaidNotification) {
            if ($eventName === 'payment.captured' || $eventName === 'order.paid') {
                if ($booking->status !== EventBooking::STATUS_PAID) {
                    $booking->forceFill([
                        'status' => EventBooking::STATUS_PAID,
                        'razorpay_payment_id' => (string) ($payload['payload']['payment']['entity']['id'] ?? $booking->razorpay_payment_id),
                        'paid_at' => $booking->paid_at ?: now(),
                        'payment_payload' => json_encode(['webhook' => $payload], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'failure_reason' => null,
                    ])->save();

                    $sendPaidNotification = true;
                }

                $this->ensureTicketsGenerated($booking);
            }

            if ($eventName === 'payment.failed' && $booking->status !== EventBooking::STATUS_PAID) {
                $wasFailed = $booking->status === EventBooking::STATUS_FAILED;
                $booking->forceFill([
                    'status' => EventBooking::STATUS_FAILED,
                    'failure_reason' => (string) ($payload['payload']['payment']['entity']['error_description'] ?? 'Payment failed'),
                    'payment_payload' => json_encode(['webhook' => $payload], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ])->save();

                if (! $wasFailed) {
                    $this->sendAdminFailedBookingNotification($booking->fresh(['event', 'plan', 'user', 'delegates']));
                }
            }

            $webhookRow->forceFill(['processed' => true])->save();
        });

        if ($sendPaidNotification) {
            $this->sendAdminPaidBookingNotification($booking->fresh(['event', 'plan', 'user', 'delegates']));
        }

        return true;
    }

    public function markBookingFailedByOrderId(User $user, string $orderId, string $reason, string $paymentId = '', array $payload = []): ?EventBooking
    {
        if ($orderId === '') {
            return null;
        }

        $booking = EventBooking::query()
            ->where('razorpay_order_id', $orderId)
            ->where('user_id', $user->id)
            ->with(['event', 'plan', 'user', 'delegates'])
            ->first();

        if (! $booking || $booking->status === EventBooking::STATUS_PAID) {
            return null;
        }

        if ($booking->status === EventBooking::STATUS_FAILED) {
            return $booking;
        }

        $booking->forceFill([
            'status' => EventBooking::STATUS_FAILED,
            'failure_reason' => $reason,
            'razorpay_payment_id' => $paymentId !== '' ? $paymentId : $booking->razorpay_payment_id,
            'payment_payload' => ! empty($payload)
                ? json_encode(['failure_callback' => $payload], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : $booking->payment_payload,
        ])->save();

        $this->sendAdminFailedBookingNotification($booking->fresh(['event', 'plan', 'user', 'delegates']));

        return $booking;
    }

    private function apiClient(): Api
    {
        $keyId = (string) Config::get('services.razorpay.key_id', '');
        $keySecret = (string) Config::get('services.razorpay.key_secret', '');

        if ($keyId === '' || $keySecret === '') {
            abort(422, 'Payment gateway credentials are not configured.');
        }

        return new Api($keyId, $keySecret);
    }

    private function ensureTicketsGenerated(EventBooking $booking): void
    {
        $existingCount = $booking->tickets()->count();
        $remaining = max(0, (int) $booking->quantity - $existingCount);

        for ($i = 0; $i < $remaining; $i++) {
            BookingTicket::create([
                'event_booking_id' => $booking->id,
                'ticket_code' => $this->generateTicketCode(),
                'status' => 'issued',
                'issued_at' => now(),
            ]);
        }
    }

    private function generateBookingCode(): string
    {
        do {
            $code = 'BK-' . now()->format('Ymd') . '-' . strtoupper(Str::random(8));
        } while (EventBooking::query()->where('booking_code', $code)->exists());

        return $code;
    }

    private function generateTicketCode(): string
    {
        do {
            $code = 'TKT-' . strtoupper(Str::random(10));
        } while (BookingTicket::query()->where('ticket_code', $code)->exists());

        return $code;
    }

    private function isWebhookSignatureValid(string $body, string $signature, string $secret): bool
    {
        if ($signature === '' || $secret === '') {
            return false;
        }

        try {
            $computed = hash_hmac('sha256', $body, $secret);
            return hash_equals($computed, $signature);
        } catch (Throwable $exception) {
            return false;
        }
    }

    private function getUsdToInrRate(): float
    {
        return TicketFxSettings::usdToInr();
    }

    private function buildCheckoutPricing(
        string $displayCurrency,
        int $displayUnitMinor,
        int $quantity,
        string $countryCode,
        ?string $promoCode = null
    ): array
    {
        $subtotal = $displayUnitMinor * $quantity;
        $promoDiscount = 0;
        $promo = $this->resolvePromo($promoCode);

        if ($promo) {
            if ($promo->discount_type === 'percentage') {
                $promoDiscount = (int) round($subtotal * (((int) $promo->discount_percent) / 100));
            } elseif ($promo->discount_type === 'flat') {
                $promoDiscount = $displayCurrency === 'INR'
                    ? (int) $promo->flat_inr_paise
                    : (int) $promo->flat_usd_cents;
            } elseif ($promo->discount_type === 'quantity') {
                $buy = max(0, (int) $promo->quantity_buy);
                $pay = max(0, (int) $promo->quantity_pay);
                if ($buy > 0 && $pay >= 0 && $pay < $buy && $quantity >= $buy) {
                    $freeUnits = intdiv($quantity, $buy) * ($buy - $pay);
                    $promoDiscount = $freeUnits * $displayUnitMinor;
                }
            }
        }

        $promoDiscount = min($promoDiscount, $subtotal);
        $afterPromo = max(0, $subtotal - $promoDiscount);

        $gstCharge = 0;
        // GST is intentionally disabled for ticket checkout.

        $gatewaySettings = TicketCheckoutSettings::get();
        $gatewayCharge = 0;
        if (($gatewaySettings['gateway_charge_type'] ?? 'none') === 'percent') {
            $pct = (float) ($gatewaySettings['gateway_charge_percent'] ?? 0);
            $gatewayCharge = (int) round(($afterPromo + $gstCharge) * ($pct / 100));
        } elseif (($gatewaySettings['gateway_charge_type'] ?? 'none') === 'flat') {
            $gatewayCharge = $displayCurrency === 'INR'
                ? (int) round(((float) ($gatewaySettings['gateway_charge_inr'] ?? 0)) * 100)
                : (int) round(((float) ($gatewaySettings['gateway_charge_usd'] ?? 0)) * 100);
        }

        return [
            'subtotal_minor' => $subtotal,
            'promo_discount_minor' => max(0, $promoDiscount),
            'gst_charge_minor' => max(0, $gstCharge),
            'gateway_charge_minor' => max(0, $gatewayCharge),
            'grand_total_minor' => max(0, $afterPromo + $gstCharge + $gatewayCharge),
            'promo' => $promo,
        ];
    }

    private function resolvePromo(?string $promoCode): ?PromoCode
    {
        $code = strtoupper(trim((string) $promoCode));
        if ($code === '') {
            return null;
        }

        $promo = PromoCode::query()->where('code', $code)->where('is_active', true)->first();
        if (! $promo) {
            return null;
        }

        $now = now();
        if ($promo->starts_at && $now->lt($promo->starts_at)) {
            return null;
        }
        if ($promo->ends_at && $now->gt($promo->ends_at)) {
            return null;
        }
        if ($promo->max_uses !== null && (int) $promo->used_count >= (int) $promo->max_uses) {
            return null;
        }

        return $promo;
    }

    private function getMinTicketQuantity(): int
    {
        $settings = TicketCheckoutSettings::get();
        return max(1, (int) ($settings['min_ticket_quantity'] ?? 1));
    }

    private function getMaxTicketQuantity(): int
    {
        $settings = TicketCheckoutSettings::get();
        $min = max(1, (int) ($settings['min_ticket_quantity'] ?? 1));
        return max($min, (int) ($settings['max_ticket_quantity'] ?? 10));
    }

    private function sendAdminPaidBookingNotification(EventBooking $booking): void
    {
        app(AdminNotificationMailer::class)->send('ticket_booking_paid', $this->bookingNotificationPlaceholders($booking));
    }

    private function sendAdminFailedBookingNotification(EventBooking $booking): void
    {
        app(AdminNotificationMailer::class)->send('ticket_booking_failed', $this->bookingNotificationPlaceholders($booking) + [
            'failure_reason' => $booking->failure_reason ?: 'Payment failed',
            'razorpay_order_id' => $booking->razorpay_order_id ?: '-',
            'razorpay_payment_id' => $booking->razorpay_payment_id ?: '-',
        ]);
    }

    private function bookingNotificationPlaceholders(EventBooking $booking): array
    {
        $delegateSummary = $booking->delegates
            ->map(function (EventBookingDelegate $delegate): string {
                return collect([
                    $delegate->delegate_name,
                    $delegate->designation,
                    $delegate->company_name,
                    $delegate->country,
                    $delegate->email,
                    $delegate->mobile,
                ])->filter()->implode(' | ');
            })
            ->filter()
            ->implode(', ');

        return [
            'booking_code' => $booking->booking_code,
            'event_title' => $booking->event?->title ?: '-',
            'plan_name' => $booking->plan?->name ?: '-',
            'user_name' => $booking->user?->name ?: '-',
            'user_email' => $booking->user?->email ?: '-',
            'quantity' => $booking->quantity,
            'display_currency' => $booking->display_currency ?: $booking->currency,
            'grand_total' => number_format(((int) $booking->grand_total_display_minor) / 100, 2),
            'billing_country_code' => $booking->billing_country_code ?: '-',
            'billing_company_name' => $booking->billing_company_name ?: '-',
            'billing_address_line_1' => $booking->billing_address_line_1 ?: '-',
            'billing_address_line_2' => $booking->billing_address_line_2 ?: '-',
            'gstin' => $booking->gstin ?: '-',
            'promo_code' => $booking->promo_code ?: '-',
            'delegates' => $delegateSummary !== '' ? $delegateSummary : '-',
        ];
    }
}
