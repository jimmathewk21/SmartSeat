<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SponsorInquiry;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SponsorInquiriesController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('sponsor_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = SponsorInquiry::query();

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('job_title', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('country', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        }

        $inquiries = $query
            ->orderByDesc('id')
            ->paginate(25)
            ->appends($request->query());

        return view('admin.sponsorInquiries.index', compact('inquiries'));
    }

    public function show(SponsorInquiry $sponsor_inquiry)
    {
        abort_if(Gate::denies('sponsor_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.sponsorInquiries.show', compact('sponsor_inquiry'));
    }

    public function destroy(SponsorInquiry $sponsor_inquiry)
    {
        abort_if(Gate::denies('sponsor_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $sponsor_inquiry->delete();

        return back()->with('message', 'Sponsor inquiry deleted successfully.');
    }
}
