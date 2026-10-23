<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyMemberRequest;
use App\Http\Requests\StoreMemberRequest;
use App\Http\Requests\UpdateMemberRequest;
use App\Models\Member;
use Gate;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MembersController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('member_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = Member::query()->select(sprintf('%s.*', (new Member)->table));

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('designation', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $allowedSorts = ['id', 'name', 'designation', 'company', 'sort_order', 'is_active'];
        $sortBy = $request->input('sort_by', 'sort_order');

        if (!in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'sort_order';
        }

        $sortDir = $request->input('sort_dir', 'asc') === 'desc' ? 'desc' : 'asc';

        $members = $query
            ->orderBy($sortBy, $sortDir)
            ->orderBy('id')
            ->paginate(25)
            ->appends($request->query());

        return view('admin.members.index', compact('members'));
    }

    public function create()
    {
        abort_if(Gate::denies('member_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.members.create');
    }

    public function store(StoreMemberRequest $request)
    {
        Member::create($request->validated());

        return redirect()->route('admin.members.index');
    }

    public function edit(Member $member)
    {
        abort_if(Gate::denies('member_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.members.edit', compact('member'));
    }

    public function update(UpdateMemberRequest $request, Member $member)
    {
        $member->update($request->validated());

        return redirect()->route('admin.members.index');
    }

    public function show(Member $member)
    {
        abort_if(Gate::denies('member_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        return view('admin.members.show', compact('member'));
    }

    public function destroy(Member $member)
    {
        abort_if(Gate::denies('member_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $member->delete();

        return back();
    }

    public function massDestroy(MassDestroyMemberRequest $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back();
        }

        Member::whereIn('id', $ids)->delete();

        return back();
    }
}
