<?php

namespace App\Http\Controllers\Admin;

use App\Models\LoginActivity;
use App\Models\User;
use App\Support\PaymentGatewaySettings;
use App\Support\TicketFxSettings;
use Gate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class HomeController
{
    public function index()
    {
        abort_if(! auth()->user()?->is_admin, Response::HTTP_FORBIDDEN, '403 Forbidden');

        $paymentGatewayEnabled = PaymentGatewaySettings::enabled();
        $paymentGatewayConfigured = PaymentGatewaySettings::hasCredentials();
        $usdToInrRate = TicketFxSettings::usdToInr();
        $inrToUsdRate = TicketFxSettings::inrToUsd();
        $fxLastUpdatedAt = TicketFxSettings::lastUpdatedAt();

        $newUserLogins = Gate::allows('user_access')
            ? User::query()
                ->with([
                    'participant' => function ($query) {
                        $query->select([
                            'participants.id',
                            'participants.user_id',
                            'participants.is_approved',
                        ]);
                    },
                    'latestLoginActivity' => function ($query) {
                        $query->select([
                            'login_activities.id',
                            'login_activities.user_id',
                            'login_activities.ip_address',
                            'login_activities.attempted_at',
                        ]);
                    },
                ])
                ->select(['id', 'name', 'email', 'company_name', 'created_at'])
                ->orderByDesc('id')
                ->limit(5)
                ->get()
            : collect();

        $userActivities = Gate::allows('login_activity_access')
            ? LoginActivity::query()
                ->with(['user:id,name,email,company_name'])
                ->select(['id', 'user_id', 'email', 'status', 'ip_address', 'attempted_at', 'created_at'])
                ->orderByDesc('attempted_at')
                ->limit(5)
                ->get()
            : collect();

        return view('home', compact(
            'paymentGatewayEnabled',
            'paymentGatewayConfigured',
            'usdToInrRate',
            'inrToUsdRate',
            'fxLastUpdatedAt',
            'newUserLogins',
            'userActivities',
        ));
    }

    public function togglePaymentGateway(Request $request): RedirectResponse
    {
        $this->ensureDashboardSettingsPermission();

        $validated = $request->validate([
            'enabled' => ['required', 'boolean'],
        ]);

        PaymentGatewaySettings::set([
            'enabled' => (bool) $validated['enabled'],
        ]);

        $message = (bool) $validated['enabled']
            ? 'Payment gateway turned on.'
            : 'Payment gateway turned off.';

        return redirect()->route('admin.home')->with('message', $message);
    }

    public function updateFxRate(Request $request): RedirectResponse
    {
        $this->ensureDashboardSettingsPermission();

        $validated = $request->validate([
            'usd_amount' => ['required', 'numeric', 'gt:0'],
            'inr_amount' => ['required', 'numeric', 'gt:0'],
        ]);

        $usd = (float) $validated['usd_amount'];
        $inr = (float) $validated['inr_amount'];
        $usdToInrRate = $inr / $usd;

        TicketFxSettings::setUsdToInr($usdToInrRate);

        return redirect()
            ->route('admin.home')
            ->with('message', 'USD/INR rate updated successfully.');
    }

    public function liveFxRate(): JsonResponse
    {
        abort_if(! auth()->user()?->is_admin, Response::HTTP_FORBIDDEN);

        try {
            $response = Http::timeout(8)->get('https://open.er-api.com/v6/latest/USD');

            if ($response->successful()) {
                $data = $response->json();
                $rate = $data['rates']['INR'] ?? null;

                if ($rate && is_numeric($rate) && (float) $rate > 0) {
                    return response()->json([
                        'success' => true,
                        'rate'    => round((float) $rate, 6),
                    ]);
                }
            }
        } catch (\Throwable) {}

        return response()->json(['success' => false, 'rate' => null]);
    }

    private function ensureDashboardSettingsPermission(): void
    {
        abort_if(! auth()->user()?->is_admin, Response::HTTP_FORBIDDEN, '403 Forbidden');

        $hasPermission = Gate::allows('setting_edit') || Gate::allows('developer_options');
        abort_if(! $hasPermission, Response::HTTP_FORBIDDEN, '403 Forbidden');
    }
}
