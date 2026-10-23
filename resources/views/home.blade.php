@extends('layouts.admin')

@section('styles')
    @parent
    <style>
        .dash-shell{background:#f3f4f8;border:1px dashed #c6c9d6;border-radius:16px;padding:14px}
        .dash-top{display:grid;grid-template-columns:1.2fr .9fr;gap:12px;margin-bottom:14px}
        .dash-box{background:#fff;border:1px solid #dfe3ed;border-radius:12px;padding:14px}
        .dash-title{margin:0 0 12px;font-size:21px;font-weight:700;color:#1e2330}
        .dash-actions{display:flex;flex-wrap:wrap;gap:8px}
        .dash-rate{display:grid;grid-template-columns:1fr 1fr;gap:8px}
        .dash-rate-box{background:#fff;border:1px solid #aebbd4;border-radius:10px;padding:10px 12px}
        .dash-rate-row{display:grid;grid-template-columns:56px 1fr;gap:8px;align-items:center;margin-bottom:8px}
        .dash-rate-row:last-child{margin-bottom:0}
        .dash-rate-label{margin:0;font-size:31px;font-weight:700;line-height:1}
        .dash-rate-input{width:100%;border:1px solid #d9dfed;border-radius:6px;padding:6px 8px;min-height:34px;background:#f8fafc}
        .dash-rate-val{margin:0;font-size:44px;font-weight:800;line-height:1}
        .dash-rate-sub{margin-top:6px;font-size:12px;color:#59617b;font-weight:600}
        .live-badge{display:inline-block;background:#16a34a;color:#fff;font-size:9px;font-weight:700;padding:2px 6px;border-radius:999px;letter-spacing:.5px;vertical-align:middle;margin-left:4px}
        .live-badge.fetching{background:#f59e0b}
        .live-badge.error{background:#dc2626}
        .clock-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}
        .clock-card{border:1px solid #dfe3ed;border-radius:10px;padding:10px;text-align:center;background:#fff}
        .clock-title{margin:0 0 8px;font-size:14px;font-weight:800;color:#1e2330;text-transform:uppercase}
        .clock-face{position:relative;width:96px;aspect-ratio:1;margin:0 auto;border:4px solid #3f63e0;border-radius:50%;background:#f8faff;box-shadow:inset 0 0 0 3px #fff}
        .clock-face:before{content:"";position:absolute;inset:8px;border:1px solid #d7def1;border-radius:50%}
        .clock-center{position:absolute;left:50%;top:50%;width:9px;height:9px;background:#1e2330;border-radius:50%;transform:translate(-50%,-50%);z-index:4}
        .clock-hand{position:absolute;left:50%;bottom:50%;transform-origin:50% 100%;border-radius:999px;background:#1e2330;z-index:3}
        .clock-hour{width:5px;height:28px;margin-left:-2.5px}
        .clock-minute{width:3px;height:36px;margin-left:-1.5px;background:#253b88}
        .clock-second{width:2px;height:40px;margin-left:-1px;background:#dc2626}
        .clock-digital{margin:8px 0 0;font-size:12px;font-weight:700;color:#59617b}
        .dash-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
        .dash-card{border:1px solid #dbe1ef;border-radius:8px;overflow:hidden;background:#fff}
        .dash-card-head{background:#3f63e0;color:#fff;display:flex;justify-content:space-between;align-items:center;padding:8px 10px}
        .dash-card-head h3{margin:0;font-size:24px;line-height:1.1;text-transform:uppercase;font-weight:700}
        .dash-enter{width:24px;height:24px;border-radius:6px;background:#fff;color:#3759cd;display:inline-flex;align-items:center;justify-content:center;text-decoration:none!important}
        .dash-card-body{padding:8px}
        .dash-empty{margin:0;min-height:116px;display:flex;align-items:center;justify-content:center;text-align:center;color:#677188;font-size:13px;font-weight:600;background:#f7f9fe;border:1px dashed #d9e0ef;border-radius:6px}
        .dash-table{width:100%;margin:0;border-collapse:separate;border-spacing:0}
        .dash-table th,.dash-table td{border-top:1px solid #ecf0f8;padding:5px 6px;font-size:10px;line-height:1.35;white-space:nowrap;color:#24314f}
        .dash-table th{border-top:none;font-size:9px;color:#59617b;text-transform:uppercase;font-weight:700;background:#f8faff}
        .pill{display:inline-block;padding:2px 7px;border-radius:999px;font-size:9px;font-weight:700;text-transform:uppercase}
        .pill-success{background:#e3f8eb;color:#18753b}.pill-warn{background:#fff1dd;color:#a85c06}.pill-danger{background:#ffe7e7;color:#a80d17}.pill-muted{background:#edf0f5;color:#5f6678}
        .dash-note{margin:8px 0 0;font-size:11px;color:#6c7588}
        .dash-bottom{margin-top:12px}
        @media (max-width:1199px){.dash-top{grid-template-columns:1fr}.dash-card-head h3{font-size:20px}}
        @media (max-width:991px){.dash-rate{grid-template-columns:1fr}.dash-grid{grid-template-columns:1fr}}
        @media (max-width:575px){.clock-grid{grid-template-columns:1fr}.clock-face{width:112px}}
    </style>
@endsection

@section('content')
    @php
        $frontendMaintenanceEnabled = \App\Support\FrontendMaintenanceSettings::enabled();
        $canManageDashboardSettings = Gate::allows('setting_edit') || Gate::allows('developer_options');
    @endphp

    @if(session('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <div class="dash-shell">
        <div class="dash-top">
            <div class="dash-box">
                <div class="clock-grid" aria-label="World clocks">
                    <div class="clock-card" data-clock-timezone="America/New_York">
                        <h2 class="clock-title">US</h2>
                        <div class="clock-face">
                            <span class="clock-hand clock-hour"></span>
                            <span class="clock-hand clock-minute"></span>
                            <span class="clock-hand clock-second"></span>
                            <span class="clock-center"></span>
                        </div>
                        <p class="clock-digital">--:--:--</p>
                    </div>
                    <div class="clock-card" data-clock-timezone="Europe/London">
                        <h2 class="clock-title">UK</h2>
                        <div class="clock-face">
                            <span class="clock-hand clock-hour"></span>
                            <span class="clock-hand clock-minute"></span>
                            <span class="clock-hand clock-second"></span>
                            <span class="clock-center"></span>
                        </div>
                        <p class="clock-digital">--:--:--</p>
                    </div>
                    <div class="clock-card" data-clock-timezone="Asia/Tokyo">
                        <h2 class="clock-title">Japan</h2>
                        <div class="clock-face">
                            <span class="clock-hand clock-hour"></span>
                            <span class="clock-hand clock-minute"></span>
                            <span class="clock-hand clock-second"></span>
                            <span class="clock-center"></span>
                        </div>
                        <p class="clock-digital">--:--:--</p>
                    </div>
                </div>
            </div>

            <div class="dash-rate">
                <div class="dash-rate-box">
                    <div class="dash-rate-row"><p class="dash-rate-label">USD</p><input type="number" min="0" step="0.0001" class="dash-rate-input" id="fx-usd-amount" value="1"></div>
                    <div class="dash-rate-row"><p class="dash-rate-label">INR</p><input type="number" min="0" step="0.0001" class="dash-rate-input" id="fx-inr-amount" value="{{ number_format($usdToInrRate, 4, '.', '') }}"></div>
                </div>
                <div class="dash-rate-box text-center d-flex flex-column justify-content-center">
                    <div class="dash-rate-label" style="font-size:34px;">USD/INR <span class="live-badge fetching" id="live-badge">...</span></div>
                    <p class="dash-rate-val" id="usd-inr-rate" data-saved-rate="{{ number_format($usdToInrRate, 6, '.', '') }}">{{ number_format($usdToInrRate, 2) }}</p>
                    <div class="dash-rate-sub" id="inr-usd-rate">1 INR = {{ number_format($inrToUsdRate, 6) }} USD</div>
                    <div class="dash-rate-sub" id="live-rate-row" style="display:none;color:#16a34a;">Live: <span id="live-rate-val"></span> INR</div>
                </div>
            </div>
        </div>

        <div class="dash-grid">
            <section class="dash-card">
                <header class="dash-card-head">
                    <h3>New User Login</h3>
                    @if(Gate::allows('user_access'))<a href="{{ route('admin.users.index') }}" class="dash-enter" title="Go to New User Login"><i class="fas fa-sign-in-alt"></i></a>@endif
                </header>
                <div class="dash-card-body">
                    @if(! Gate::allows('user_access'))
                        <p class="dash-empty">No access for this module.</p>
                    @elseif($newUserLogins->isEmpty())
                        <p class="dash-empty">No recent user records found.</p>
                    @else
                        <div class="table-responsive">
                            <table class="dash-table">
                                <thead><tr><th>Name</th><th>Email</th><th>Company</th><th>Date</th><th>Approval</th></tr></thead>
                                <tbody>
                                @foreach($newUserLogins as $row)
                                    @php
                                        $dateTime = optional($row->latestLoginActivity)->attempted_at ?? $row->created_at;
                                        $approval = $row->participant ? ($row->participant->is_approved ? 'Approved' : 'Blocked') : 'N/A';
                                        $approvalClass = $approval === 'Approved' ? 'pill-success' : ($approval === 'Blocked' ? 'pill-danger' : 'pill-muted');
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $row->name ?: '-' }}</strong></td>
                                        <td>{{ $row->email ?: '-' }}</td>
                                        <td>{{ $row->company_name ?: '-' }}</td>
                                        <td>{{ $dateTime ? \Carbon\Carbon::parse($dateTime)->format('d-M-Y h:i A') : '-' }}</td>
                                        <td><span class="pill {{ $approvalClass }}">{{ $approval }}</span></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </section>

            <section class="dash-card">
                <header class="dash-card-head">
                    <h3>User Activity</h3>
                    @if(Gate::allows('login_activity_access'))<a href="{{ route('admin.login-activities.index') }}" class="dash-enter" title="Go to User Activity"><i class="fas fa-sign-in-alt"></i></a>@endif
                </header>
                <div class="dash-card-body">
                    @if(! Gate::allows('login_activity_access'))
                        <p class="dash-empty">No access for this module.</p>
                    @elseif($userActivities->isEmpty())
                        <p class="dash-empty">No recent activity records found.</p>
                    @else
                        <div class="table-responsive">
                            <table class="dash-table">
                                <thead><tr><th>User</th><th>Email</th><th>IP</th><th>Status</th><th>Date</th></tr></thead>
                                <tbody>
                                @foreach($userActivities as $row)
                                    @php
                                        $status = ucfirst((string) $row->status);
                                        $statusClass = match (strtolower((string) $row->status)) {
                                            'success' => 'pill-success', 'failed' => 'pill-danger', 'pending' => 'pill-warn', default => 'pill-muted',
                                        };
                                        $activityAt = $row->attempted_at ?? $row->created_at;
                                    @endphp
                                    <tr>
                                        <td><strong>{{ $row->user?->name ?: '-' }}</strong></td>
                                        <td>{{ $row->email ?: ($row->user?->email ?: '-') }}</td>
                                        <td>{{ $row->ip_address ?: '-' }}</td>
                                        <td><span class="pill {{ $statusClass }}">{{ $status }}</span></td>
                                        <td>{{ $activityAt ? \Carbon\Carbon::parse($activityAt)->format('d-M-Y h:i A') : '-' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </section>

        </div>

        <div class="dash-box dash-bottom" id="developer-options-panel">
            <h2 class="dash-title">Developer Options</h2>
            <div class="dash-actions">
                @can('developer_options')
                    <form method="POST" action="{{ route('admin.maintenance.migrate') }}">@csrf <button class="btn btn-primary" type="submit">Run Migrate</button></form>
                    <form method="POST" action="{{ route('admin.maintenance.cacheRefresh') }}">@csrf <button class="btn btn-outline-secondary" type="submit">Cache Refresh</button></form>
                    <form method="POST" action="{{ route('admin.maintenance.optimize') }}">@csrf <button class="btn btn-outline-primary" type="submit">Optimize + Optimize:Clear</button></form>
                    <form method="POST" action="{{ route('admin.maintenance.frontendMode') }}">@csrf
                        <input type="hidden" name="enabled" value="{{ $frontendMaintenanceEnabled ? '0' : '1' }}">
                        <button class="btn {{ $frontendMaintenanceEnabled ? 'btn-success' : 'btn-danger' }}" type="submit">{{ $frontendMaintenanceEnabled ? 'Turn Off Maintenance Mode' : 'Turn On Maintenance Mode' }}</button>
                    </form>
                @endcan
                @if($canManageDashboardSettings)
                    <form method="POST" action="{{ route('admin.dashboard.payment-gateway.toggle') }}">@csrf
                        <input type="hidden" name="enabled" value="{{ $paymentGatewayEnabled ? '0' : '1' }}">
                        <button class="btn {{ $paymentGatewayEnabled ? 'btn-success' : 'btn-danger' }}" type="submit">Payment Gateway {{ $paymentGatewayEnabled ? 'On' : 'Off' }}</button>
                    </form>
                @endif
            </div>
            @if(! $paymentGatewayConfigured)
                <p class="dash-note">Razorpay credentials are missing in environment configuration.</p>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script>
    (function () {
        var clocks = Array.prototype.slice.call(document.querySelectorAll('[data-clock-timezone]'));

        function partsForTimezone(timeZone) {
            return new Intl.DateTimeFormat('en-US', {
                timeZone: timeZone,
                hour: 'numeric',
                minute: 'numeric',
                second: 'numeric',
                hour12: false
            }).formatToParts(new Date()).reduce(function (carry, part) {
                carry[part.type] = part.value;
                return carry;
            }, {});
        }

        function updateClock(card) {
            var parts = partsForTimezone(card.dataset.clockTimezone);
            var hours = parseInt(parts.hour, 10) % 12;
            var minutes = parseInt(parts.minute, 10);
            var seconds = parseInt(parts.second, 10);
            var hourDeg = (hours * 30) + (minutes * 0.5);
            var minuteDeg = (minutes * 6) + (seconds * 0.1);
            var secondDeg = seconds * 6;
            var hourHand = card.querySelector('.clock-hour');
            var minuteHand = card.querySelector('.clock-minute');
            var secondHand = card.querySelector('.clock-second');
            var digital = card.querySelector('.clock-digital');

            if (hourHand) hourHand.style.transform = 'rotate(' + hourDeg + 'deg)';
            if (minuteHand) minuteHand.style.transform = 'rotate(' + minuteDeg + 'deg)';
            if (secondHand) secondHand.style.transform = 'rotate(' + secondDeg + 'deg)';
            if (digital) digital.textContent = [parts.hour, parts.minute, parts.second].join(':');
        }

        function updateClocks() {
            clocks.forEach(updateClock);
        }

        updateClocks();
        setInterval(updateClocks, 1000);
    })();

    (function () {
        var usdInput    = document.getElementById('fx-usd-amount');
        var inrInput    = document.getElementById('fx-inr-amount');
        var rateDisplay = document.getElementById('usd-inr-rate');
        var inverseDisp = document.getElementById('inr-usd-rate');
        var liveBadge   = document.getElementById('live-badge');
        var liveRateRow = document.getElementById('live-rate-row');
        var liveRateVal = document.getElementById('live-rate-val');
        var currentRate = parseFloat(rateDisplay ? rateDisplay.dataset.savedRate : '0') || 0;
        var lastEdited = 'usd';

        function valid(n) { return isFinite(n) && n > 0; }
        function validAmount(n) { return isFinite(n) && n >= 0; }

        function setRateDisplay(rate) {
            if (!valid(rate)) return;
            currentRate = rate;
            if (rateDisplay)  rateDisplay.textContent = rate.toFixed(2);
            if (inverseDisp)  inverseDisp.textContent = '1 INR = ' + (1 / rate).toFixed(6) + ' USD';
            if (usdInput && inrInput) {
                if (document.activeElement === inrInput || lastEdited === 'inr') {
                    updateFromInr();
                } else {
                    updateFromUsd();
                }
            }
        }

        function inputNumber(input) {
            return parseFloat(String(input && input.value ? input.value : '').replace(/,/g, ''));
        }

        function updateFromUsd() {
            lastEdited = 'usd';
            if (!valid(currentRate) || !usdInput || !inrInput) return;
            var usd = inputNumber(usdInput);
            if (!validAmount(usd)) {
                inrInput.value = '';
                return;
            }
            inrInput.value = (usd * currentRate).toFixed(4);
        }

        function updateFromInr() {
            lastEdited = 'inr';
            if (!valid(currentRate) || !usdInput || !inrInput) return;
            var inr = inputNumber(inrInput);
            if (!validAmount(inr)) {
                usdInput.value = '';
                return;
            }
            usdInput.value = (inr / currentRate).toFixed(4);
        }

        function fetchLiveRate() {
            if (liveBadge) { liveBadge.textContent = '...'; liveBadge.className = 'live-badge fetching'; }

            fetch('{{ route('admin.dashboard.live-fx-rate') }}', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success && valid(data.rate)) {
                    if (liveBadge) { liveBadge.textContent = 'LIVE'; liveBadge.className = 'live-badge'; }
                    if (liveRateRow && liveRateVal) {
                        liveRateVal.textContent = data.rate.toFixed(4);
                        liveRateRow.style.display = '';
                    }
                    setRateDisplay(data.rate);
                } else {
                    if (liveBadge) { liveBadge.textContent = 'OFFLINE'; liveBadge.className = 'live-badge error'; }
                }
            })
            .catch(function () {
                if (liveBadge) { liveBadge.textContent = 'OFFLINE'; liveBadge.className = 'live-badge error'; }
            });
        }

        fetchLiveRate();
        setInterval(fetchLiveRate, 5 * 60 * 1000);
        if (usdInput) usdInput.addEventListener('input', updateFromUsd);
        if (inrInput) inrInput.addEventListener('input', updateFromInr);
    })();
    </script>
@endsection
