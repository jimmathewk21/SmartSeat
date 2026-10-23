<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuditLogsController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('audit_log_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = AuditLog::query()->select(sprintf('%s.*', (new AuditLog)->table));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('subject_id', 'like', "%{$search}%")
                    ->orWhere('subject_type', 'like', "%{$search}%")
                    ->orWhere('user_id', 'like', "%{$search}%")
                    ->orWhere('host', 'like', "%{$search}%");
            });
        }

        if ($request->filled('subject_type')) {
            $query->where('subject_type', $request->input('subject_type'));
        }

        $allowedSorts = ['id', 'created_at'];
        $sortBy = $request->input('sort_by', 'id');
        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'id';
        }
        $sortDir = $request->input('sort_dir', 'desc') === 'asc' ? 'asc' : 'desc';

        $auditLogs = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate(25)
            ->appends($request->query());

        $subjectTypes = AuditLog::query()
            ->select('subject_type')
            ->whereNotNull('subject_type')
            ->distinct()
            ->orderBy('subject_type')
            ->pluck('subject_type');

        return view('admin.auditLogs.index', compact('auditLogs', 'subjectTypes'));
    }

    public function show(AuditLog $auditLog)
    {
        abort_if(Gate::denies('audit_log_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.auditLogs.show', compact('auditLog'));
    }
}
