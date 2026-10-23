@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header">Respond to Asked Question</div>

        <div class="card-body">
            <div class="mb-3">
                <a class="btn btn-outline-secondary" href="{{ route('admin.faq-questions.index') }}">Back</a>
            </div>

            <form method="POST" action="{{ route('admin.faq-questions.update', $faqQuestion) }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Name</label>
                    <input type="text" class="form-control" value="{{ $faqQuestion->name }}" disabled>
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="text" class="form-control" value="{{ $faqQuestion->email }}" disabled>
                </div>

                <div class="form-group">
                    <label>Question</label>
                    <textarea class="form-control" rows="5" disabled>{{ $faqQuestion->question }}</textarea>
                </div>

                <div class="form-group">
                    <label for="answer">Answer</label>
                    <textarea name="answer" id="answer" rows="7" class="form-control {{ $errors->has('answer') ? 'is-invalid' : '' }}" placeholder="Type answer to send user...">{{ old('answer', $faqQuestion->answer) }}</textarea>
                    @if($errors->has('answer'))
                        <div class="invalid-feedback">{{ $errors->first('answer') }}</div>
                    @endif
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="pending" @selected(old('status', $faqQuestion->status) === 'pending')>Pending</option>
                        <option value="answered" @selected(old('status', $faqQuestion->status) === 'answered')>Answered</option>
                    </select>
                    <small class="form-text text-muted">When set to Answered, user will receive this answer via email.</small>
                </div>

                <div class="form-group">
                    <button class="btn btn-primary" type="submit">Save Response</button>
                </div>
            </form>
        </div>
    </div>
@endsection
