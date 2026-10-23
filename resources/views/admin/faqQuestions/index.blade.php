@extends('layouts.admin')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;">
            <span>Asked Questions</span>
            <a class="btn btn-outline-primary" href="{{ route('admin.faqs.index') }}">Back to FAQs</a>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('admin.faq-questions.index') }}" class="mb-3">
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <input class="form-control" type="text" name="search" value="{{ request('search') }}" placeholder="Search name, email, or question">
                    </div>
                    <div class="col-md-3 mb-2">
                        <select class="form-control" name="status">
                            <option value="pending" @selected(request('status', 'pending') === 'pending')>Pending</option>
                            <option value="answered" @selected(request('status') === 'answered')>Answered</option>
                        </select>
                    </div>
                    <div class="col-md-3 mb-2 d-flex" style="gap:8px;">
                        <button class="btn btn-primary" type="submit">Search</button>
                        <a class="btn btn-outline-secondary" href="{{ route('admin.faq-questions.index') }}">Clear</a>
                    </div>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Question</th>
                            <th>Status</th>
                            <th>Asked On</th>
                            <th>Answered On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($questions as $question)
                            <tr>
                                <td>{{ $question->id }}</td>
                                <td>{{ $question->name }}</td>
                                <td>{{ $question->email }}</td>
                                <td style="max-width:360px; white-space:normal;">{{ $question->question }}</td>
                                <td>{{ $question->status === 'answered' ? 'Answered' : 'Pending' }}</td>
                                <td>{{ optional($question->created_at)->format('d-M-Y h:i A') ?: '-' }}</td>
                                <td>{{ optional($question->answered_at)->format('d-M-Y h:i A') ?: '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.faq-questions.edit', $question) }}" class="btn btn-sm btn-primary">Respond</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8">No asked questions found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $questions->links() }}
        </div>
    </div>
@endsection
