<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TrainAuthController extends Controller
{
    public function showLogin(Request $request)
    {
        $request->session()->forget('train_registration_token');
        [$searchParameters, $routeParameters, $returnTo] = $this->rememberReturnTarget($request);

        if ($request->user()) {
            return redirect($returnTo);
        }

        return view('auth.train-auth', [
            'mode' => 'login',
            'searchParameters' => $searchParameters,
            'authRouteParameters' => $routeParameters,
        ]);
    }

    public function showRegister(Request $request)
    {
        [$searchParameters, $routeParameters, $returnTo] = $this->rememberReturnTarget($request);

        if ($request->user()) {
            $request->session()->forget('train_registration_token');
            return redirect($returnTo);
        }

        $registrationToken = Str::random(64);
        $request->session()->put('train_registration_token', $registrationToken);

        $left = random_int(1, 9);
        $right = random_int(1, 9);
        $question = "{$left} + {$right} = ?";
        Session::put('register_captcha_answer', (string) ($left + $right));
        Session::put('register_captcha_question', $question);

        return view('auth.train-auth', [
            'mode' => 'register',
            'captchaQuestion' => $question,
            'registrationToken' => $registrationToken,
            'searchParameters' => $searchParameters,
            'authRouteParameters' => $routeParameters,
        ]);
    }

    /** Store only valid search criteria and construct a local, allowlisted return URL. */
    private function rememberReturnTarget(Request $request): array
    {
        $continue = $request->query('continue') === 'bookings' ? 'bookings' : 'search';
        $searchParameters = [];
        $candidates = $request->query();

        if (
            $continue === 'search'
            && collect(['from_station_id', 'to_station_id', 'date'])->every(
                fn (string $key) => isset($candidates[$key]) && ! is_array($candidates[$key]) && $candidates[$key] !== ''
            )
        ) {
            $searchParameters = Validator::make(
                array_intersect_key($candidates, array_flip(['from_station_id', 'to_station_id', 'date'])),
                [
                    'from_station_id' => ['required', 'integer', 'different:to_station_id', 'exists:stations,id,is_active,1'],
                    'to_station_id' => ['required', 'integer', 'different:from_station_id', 'exists:stations,id,is_active,1'],
                    'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
                ]
            )->validate();
        }

        $routeParameters = $continue === 'bookings'
            ? ['continue' => 'bookings']
            : $searchParameters;
        $returnTo = $continue === 'bookings'
            ? route('frontend.trains.bookings', [], false)
            : route('frontend.trains.search', $searchParameters, false);

        $request->session()->put([
            'train_auth_flow' => true,
            'train_auth_continue' => $continue,
            'train_auth_search_parameters' => $searchParameters,
            'train_auth_return_to' => $returnTo,
            'url.intended' => $returnTo,
        ]);

        return [$searchParameters, $routeParameters, $returnTo];
    }
}
