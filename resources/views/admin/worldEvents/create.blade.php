@extends('layouts.admin')
@section('content')
<div class="card">
    <div class="card-header">Create World Event</div>
    <div class="card-body">
        <form method="POST" action="{{ route('admin.world-events.store') }}" enctype="multipart/form-data">
            @include('admin.worldEvents._form')
        </form>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script>
    (function () {
        var startDateInput = document.getElementById('world-event-start-date');
        var endDateInput = document.getElementById('world-event-end-date');

        if (!startDateInput || !endDateInput) {
            return;
        }

        function syncEndDateConstraint() {
            var startDate = startDateInput.value;

            if (startDate) {
                endDateInput.min = startDate;

                if (endDateInput.value && endDateInput.value < startDate) {
                    endDateInput.value = '';
                }
            } else {
                endDateInput.removeAttribute('min');
            }
        }

        startDateInput.addEventListener('change', syncEndDateConstraint);
        startDateInput.addEventListener('input', syncEndDateConstraint);

        syncEndDateConstraint();
    })();
</script>
@endsection
