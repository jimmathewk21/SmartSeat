@extends('layouts.railway-admin')
@section('title', 'Prediction models')
@section('management')
<div class="page-heading"><div><span class="eyebrow">Evidence before estimates</span><h1>Waitlist intelligence.</h1><p class="muted">Explicit training only. No sample history or automatic startup training.</p></div></div>
<section class="panel railway-form"><h2>Model status</h2>
@if($model)
<p><strong>{{ $model['version'] }}</strong></p><p class="muted">Trained {{ \Carbon\CarbonImmutable::parse($model['trained_at'])->timezone(config('smartseat.railway_timezone'))->format('d-M-Y H:i A') }} · {{ $model['algorithm'] }}</p>
<p>Training rows: {{ $model['training_rows'] }} · Validation rows: {{ $model['validation_rows'] }}</p>
<p>Brier score: {{ number_format($model['metrics']['brier'], 4) }} · Log loss: {{ number_format($model['metrics']['log_loss'], 4) }} · Calibration ECE: {{ number_format($model['metrics']['calibration']['ece'], 4) }}</p>
<div class="table-scroll"><table><thead><tr><th>Probability bin</th><th>Count</th><th>Mean estimate</th><th>Observed rate</th></tr></thead><tbody>
@foreach($model['metrics']['calibration']['bins'] as $bin)<tr><td>{{ $bin['lower'] }}–{{ $bin['upper'] }}</td><td>{{ $bin['count'] }}</td><td>{{ number_format($bin['mean_probability'], 3) }}</td><td>{{ number_format($bin['observed_rate'], 3) }}</td></tr>@endforeach
</tbody></table></div>
<p class="muted">Small bins are uncertain. These held-out metrics do not guarantee future accuracy.</p>
<p class="muted">Passenger estimates are withheld for models older than {{ config('smartseat.intelligence.max_model_days') }} days or queue states outside historical feature coverage.</p>
@else<p>{{ $unavailable }}</p>@endif
</section>
<section class="panel railway-form railway-list"><h2>Genuine historical data</h2><p>{{ $report['eligible'] }} mature compatible snapshots · {{ count($report['train']) }} training · {{ count($report['validation']) }} validation · {{ $report['purged'] }} purged for timing.</p>
<p class="muted">Requires at least {{ config('smartseat.intelligence.min_training_rows') }} training and {{ config('smartseat.intelligence.min_validation_rows') }} validation groups, both outcomes, and passing held-out calibration/performance. No old records are backfilled with invented features.</p>
<form method="POST" action="{{ route('administrator.intelligence.train') }}">@csrf
<label class="checkbox"><input type="checkbox" name="confirm_training" value="1" required> Train explicitly from genuine mature historical snapshots.</label>
<button class="button" type="submit">Train and evaluate model</button></form>
<p class="muted">Recent password confirmation is required. An unsuccessful run preserves the accepted model; training never changes booking records.</p></section>
@endsection
