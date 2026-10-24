<?php

// Render real Blade templates with in-memory UI fixtures. Never connect to a database.
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
if (! $app->environment('testing') || config('database.default') !== 'mysql' || config('database.connections.mysql.database') !== 'smartseat_train_test') {
    throw new RuntimeException('UI fixtures require the isolated testing configuration.');
}
config(['session.driver' => 'array']);
// Eloquent uses the connection grammar to cast dates, but no SQL is allowed here.
Illuminate\Support\Facades\DB::connection()->beforeExecuting(static function (): void {
    throw new RuntimeException('UI fixture rendering must not execute SQL.');
});
$app['events']->forget('composing: frontend.*');
$app['events']->forget('composing: auth.*');
Illuminate\Support\Facades\URL::forceRootUrl('http://smartseat.test');
$session = $app['session']->driver();
$session->start();
$session->put('_token', 'ui-fixture-csrf-token');
$app['view']->share('errors', new Illuminate\Support\ViewErrorBag());
$directory = __DIR__.'/../../test-results/train-ui-fixtures';
if (! is_dir($directory)) mkdir($directory, 0777, true);

$from = (new App\Models\Station())->forceFill(['id' => 103, 'name' => 'Mumbai CSMT', 'city' => 'Mumbai', 'code' => 'CSMT']);
$to = (new App\Models\Station())->forceFill(['id' => 204, 'name' => 'Pune Junction', 'city' => 'Pune', 'code' => 'PUNE']);
$stations = collect([$from, $to]);
$departure = now()->addDays(3)->setTime(9, 15);
$arrival = $departure->copy()->addHours(3);
$train = (new App\Models\Train())->forceFill(['id' => 701, 'name' => 'Browser fixture — fictional service', 'code' => 'UI-FIXTURE']);
$train->setRelation('routeStops', collect([
    (new App\Models\TrainRouteStop())->setRelation('station', $from),
    (new App\Models\TrainRouteStop())->setRelation('station', $to),
]));
$trip = (new App\Models\TrainTrip())->forceFill(['id' => 702, 'starts_at' => $departure, 'ends_at' => $arrival, 'search_departure_at' => $departure, 'search_arrival_at' => $arrival]);
$trip->setRelation('train', $train)->setRelation('origin_station', $from)->setRelation('destination_station', $to);
$coach = (new App\Models\TrainCoach())->forceFill(['code' => 'D1', 'class_code' => 'CC']);
$inventory = collect(range(1, 6))->map(function ($number) use ($coach) {
    $seat = (new App\Models\TrainSeat())->forceFill(['number' => $number, 'berth_type' => 'window', 'is_accessible' => $number === 1]);
    $seat->setRelation('coach', $coach);
    return (new App\Models\TripSeatInventory())->forceFill(['id' => 800 + $number])->setRelation('seat', $seat);
});
$trip->setRelation('inventories', $inventory);
$passenger = (new App\Models\TrainBookingPassenger())->forceFill(['name' => 'Riya Fixture', 'requires_accessible_seat' => false]);
$assignment = (new App\Models\TrainBookingSeat())->setRelation('inventory', $inventory->first())->setRelation('passenger', $passenger);
$booking = (new App\Models\TrainBooking())->forceFill([
    'id' => 901, 'booking_code' => 'SS-UI-FIXTURE', 'status' => 'confirmed',
    'boarding_station_id' => 103, 'destination_station_id' => 204, 'boarding_at' => $departure, 'arrival_at' => $arrival,
]);
$booking->setRelation('trip', $trip)->setRelation('boardingStation', $from)->setRelation('destinationStation', $to)
    ->setRelation('passengers', collect([$passenger]))->setRelation('seatAssignments', collect([$assignment]));
$waitlist = (new App\Models\TrainWaitlistEntry())->forceFill([
    'id' => 902, 'status' => 'waiting', 'boarding_station_id' => 103, 'destination_station_id' => 204,
    'boarding_at' => $departure, 'arrival_at' => $arrival,
]);
$waitlist->setRelation('trip', $trip)->setRelation('boardingStation', $from)->setRelation('destinationStation', $to)->setRelation('passengers', collect([$passenger]));
$user = (new App\Models\User())->forceFill(['id' => 903, 'name' => 'Riya Fixture']);
$user->setRelation('participant', (new App\Models\Participant())->forceFill(['train_access_enabled' => true]));

$render = function (string $name, string $view, array $data, array $query = []) use ($app, $session, $directory): void {
    $request = Illuminate\Http\Request::create('http://smartseat.test/', 'GET', $query);
    $request->setLaravelSession($session);
    $app->instance('request', $request);
    $app['url']->setRequest($request);
    file_put_contents($directory.'/'.$name.'.html', view($view, $data)->render());
};
$render('home', 'frontend.trains.search', ['stations' => $stations, 'trips' => collect(), 'searched' => false]);
$render('no-stations', 'frontend.trains.search', ['stations' => collect(), 'trips' => collect(), 'searched' => false]);
$app['auth']->guard()->setUser($user);
$query = ['from_station_id' => 103, 'to_station_id' => 204, 'date' => $departure->toDateString()];
$render('results', 'frontend.trains.search', ['stations' => $stations, 'trips' => collect([$trip]), 'searched' => true], $query);
$render('empty-results', 'frontend.trains.search', ['stations' => $stations, 'trips' => collect(), 'searched' => true], $query);
$paginator = fn (array $items) => new Illuminate\Pagination\LengthAwarePaginator($items, count($items), 15);
$render('bookings', 'frontend.trains.bookings', [
    'confirmedBooking' => $booking, 'bookings' => $paginator([$booking]), 'waitlistEntries' => $paginator([$waitlist]),
]);
echo 'Rendered five UI fixtures without a database connection.'.PHP_EOL;
