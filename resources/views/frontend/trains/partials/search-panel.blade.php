<section class="train-search-card" id="train-search" aria-labelledby="train-search-title">
    <div class="train-section-heading">
        <h2 id="train-search-title"><span aria-hidden="true">↗</span> Find your next train</h2>
        <span class="train-search-step">A window seat to somewhere new.</span>
    </div>
    <form class="train-search-form" method="GET" action="{{ route('frontend.trains.search') }}" data-train-search-form>
        <div class="train-search-fields">
            <div class="train-route-fields">
                <div class="train-field train-station-field">
                    <label for="from_station_id">From station</label>
                    <div class="train-control-wrap">
                        <select id="from_station_id" name="from_station_id" required data-station-select aria-describedby="from-station-hint">
                            <option value="">Choose origin station</option>
                            @foreach($stations as $station)
                                <option value="{{ $station->id }}" @selected((string) request('from_station_id') === (string) $station->id)>{{ $station->name }}{{ $station->city ? ' · ' . $station->city : '' }} ({{ $station->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <span class="train-sr-only" id="from-station-hint">Type a station name, city or code. Use arrow keys to browse and Enter to select.</span>
                </div>
                <button class="train-swap-button" type="button" data-swap-stations aria-label="Swap origin and destination" title="Swap stations">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 7h12m0 0-3-3m3 3-3 3M17 17H5m0 0 3 3m-3-3 3-3"/></svg>
                </button>
                <div class="train-field train-station-field">
                    <label for="to_station_id">To station</label>
                    <div class="train-control-wrap">
                        <select id="to_station_id" name="to_station_id" required data-station-select aria-describedby="to-station-hint">
                            <option value="">Choose destination station</option>
                            @foreach($stations as $station)
                                <option value="{{ $station->id }}" @selected((string) request('to_station_id') === (string) $station->id)>{{ $station->name }}{{ $station->city ? ' · ' . $station->city : '' }} ({{ $station->code }})</option>
                            @endforeach
                        </select>
                    </div>
                    <span class="train-sr-only" id="to-station-hint">Type a station name, city or code. Use arrow keys to browse and Enter to select.</span>
                </div>
            </div>
            <div class="train-field train-date-field">
                <label for="date">Departure date</label>
                <div class="train-control-wrap">
                    <input id="date" name="date" type="date" min="{{ now()->toDateString() }}" value="{{ request('date', now()->toDateString()) }}" required>
                </div>
            </div>
            <button class="train-submit-button" type="submit" data-search-submit>
                <span class="train-submit-label">Search trains</span>
                <span class="train-submit-loading" aria-hidden="true">Searching…</span>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13m-5-5 5 5-5 5"/></svg>
            </button>
        </div>
        <p class="train-search-hint" data-station-status role="status">@if($stations->isEmpty())No stations are available to search yet.@elseSearch by station name, city or code. Add passengers when you choose your seats.@endif</p>
    </form>
</section>
