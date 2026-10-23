<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\MassDestroyUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Role;
use App\Models\Train;
use App\Models\User;
use App\Services\AdminNotificationMailer;
use App\Services\LoginActivityGeoService;
use Gate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpFoundation\Response;

class UsersController extends Controller
{
    public function index(Request $request)
    {
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $geoService = app(LoginActivityGeoService::class);
        $query = User::with(['roles', 'participant', 'latestLoginActivity'])
            ->select('users.*');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhereHas('latestLoginActivity', function ($loginActivityQuery) use ($search) {
                        $loginActivityQuery->where('ip_address', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->input('user_type'));
        }

        if ($request->filled('approval_status')) {
            if ($request->input('approval_status') === 'approved') {
                $query->whereHas('participant', function ($participantQuery) {
                    $participantQuery->where('is_approved', true);
                });
            } elseif ($request->input('approval_status') === 'blocked') {
                $query->whereHas('participant', function ($participantQuery) {
                    $participantQuery->where('is_approved', false);
                });
            }
        }

        $allowedSorts = ['id', 'name', 'email', 'mobile', 'company_name', 'created_at', 'ip_address'];
        $sortBy  = $request->input('sort_by', 'id');
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'id';
        }
        $sortDir = $request->input('sort_dir', 'desc') === 'desc' ? 'desc' : 'asc';

        if ($sortBy === 'ip_address') {
            // Join with the latest login activity per user to sort by IP address
            $latestActivity = \DB::table('login_activities')
                ->select('user_id', \DB::raw('MAX(attempted_at) as latest_at'))
                ->whereNotNull('user_id')
                ->groupBy('user_id');

            $query->leftJoinSub($latestActivity, 'latest_la', function ($join) {
                    $join->on('users.id', '=', 'latest_la.user_id');
                })
                ->leftJoin('login_activities as la_sort', function ($join) {
                    $join->on('users.id', '=', 'la_sort.user_id')
                         ->on('la_sort.attempted_at', '=', 'latest_la.latest_at');
                })
                ->orderBy('la_sort.ip_address', $sortDir);
        } else {
            $query->orderBy($sortBy, $sortDir);
        }

        $users = $query
            ->paginate(25)
            ->appends($request->query());

        $users->getCollection()->transform(function (User $user) use ($geoService) {
            $user->setAttribute('geo_location', $geoService->lookup(optional($user->latestLoginActivity)->ip_address));

            return $user;
        });

        $userTypes = User::query()
            ->select('user_type')
            ->whereNotNull('user_type')
            ->distinct()
            ->orderBy('user_type')
            ->pluck('user_type');

        return view('admin.users.index', compact('users', 'userTypes'));
    }

    public function create()
    {
        abort_if(Gate::denies('user_create'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $roles = Role::pluck('title', 'id');
        $defaultRoleId = Role::query()
            ->where('title', 'User')
            ->value('id');

        return view('admin.users.create', compact('roles', 'defaultRoleId'));
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create($request->validated());
        $roles = array_values(array_unique((array) $request->input('roles', [])));

        if ($roles === []) {
            $defaultRoleId = Role::query()
                ->where('title', 'User')
                ->value('id');

            if ($defaultRoleId) {
                $roles = [$defaultRoleId];
            }
        }

        $user->roles()->sync($roles);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        abort_if(Gate::denies('user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $roles = Role::pluck('title', 'id');
        $trains = Train::orderBy('name')->get(['id', 'code', 'name', 'owner_user_id']);

        $user->load('roles');

        return view('admin.users.edit', compact('roles', 'user', 'trains'));
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();
        $roleIds = array_values(array_unique(array_map('intval', $data['roles'] ?? [])));
        $isTrainOwner = Role::query()->where('title', 'Train Owner')->whereIn('id', $roleIds)->exists();
        $approveTrainOwner = (bool) ($data['approve_train_owner'] ?? false);

        if ($approveTrainOwner && ! $isTrainOwner) {
            return back()->withErrors(['approve_train_owner' => 'Assign the Train Owner role before approving train owner access.'])->withInput();
        }

        if ($approveTrainOwner && ! $user->participant()->exists()) {
            return back()->withErrors(['approve_train_owner' => 'Train owner accounts must retain a participant record so event approval remains separate.'])->withInput();
        }

        if (! $isTrainOwner && ($data['train_ids'] ?? []) !== []) {
            return back()->withErrors(['train_ids' => 'Assign the Train Owner role before assigning trains.'])->withInput();
        }

        $attributes = collect($data)->only(['name', 'mobile', 'company_name', 'email', 'user_type'])->all();
        if (! empty($data['password'])) {
            $attributes['password'] = $data['password'];
        }

        $trainIds = $isTrainOwner ? array_map('intval', $data['train_ids'] ?? []) : [];
        $ownerApprovedAt = $approveTrainOwner ? ($user->train_owner_approved_at ?? now()) : null;
        DB::transaction(function () use ($user, $attributes, $roleIds, $ownerApprovedAt, $trainIds): void {
            $user->update($attributes);
            $user->roles()->sync($roleIds);
            $user->forceFill(['train_owner_approved_at' => $ownerApprovedAt])->save();

            $user->ownedTrains()->whereNotIn('id', $trainIds ?: [0])->update(['owner_user_id' => null]);
            if ($trainIds !== []) {
                Train::query()->whereIn('id', $trainIds)->update(['owner_user_id' => $user->id]);
            }
        });

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function show(User $user)
    {
        abort_if(Gate::denies('user_show'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user->load('roles');

        return view('admin.users.show', compact('user'));
    }

    public function destroy(User $user)
    {
        abort_if(Gate::denies('user_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }

    public function massDestroy(MassDestroyUserRequest $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('message', 'Select at least one user first.');
        }

        User::whereIn('id', $ids)->delete();

        return back()->with('success', 'Selected users deleted successfully.');
    }

    public function bulkApproval(Request $request)
    {
        $data = $request->validate([
            'ids' => ['nullable', 'array'],
            'ids.*' => ['integer', 'exists:users,id'],
            'status' => ['required', 'in:approved,blocked,delete'],
        ]);

        if ($data['status'] === 'delete') {
            abort_if(Gate::denies('user_delete'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        } else {
            abort_if(Gate::denies('user_edit'), Response::HTTP_FORBIDDEN, '403 Forbidden');
        }

        $ids = $data['ids'] ?? [];
        if ($ids === []) {
            return back()->with('message', 'Select at least one user first.');
        }

        if ($data['status'] === 'delete') {
            User::whereIn('id', $ids)->delete();

            return back()->with('success', 'Selected users deleted successfully.');
        }

        $shouldApprove = $data['status'] === 'approved';
        $users = User::with('participant')->whereIn('id', $ids)->get();

        foreach ($users as $user) {
            if ($user->is_admin || ! $user->participant) {
                continue;
            }

            $wasApproved = (bool) $user->participant->is_approved;

            $user->participant->forceFill([
                'is_approved' => $shouldApprove,
                'approved_at' => $shouldApprove ? now() : null,
            ])->save();

            $user->forceFill([
                'verification_status' => $shouldApprove,
                'remember_token' => $shouldApprove ? $user->remember_token : Str::random(60),
            ])->save();

            if ($shouldApprove && ! $wasApproved) {
                $this->sendApprovalEmail($user);
            }
        }

        return back()->with('success', $shouldApprove ? 'Selected users approved successfully.' : 'Selected users blocked successfully.');
    }

    public function export(Request $request): StreamedResponse
    {
        abort_if(Gate::denies('user_access'), Response::HTTP_FORBIDDEN, '403 Forbidden');

        $query = User::with(['roles', 'participant', 'latestLoginActivity'])->select('users.*');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhereHas('latestLoginActivity', function ($loginActivityQuery) use ($search) {
                        $loginActivityQuery->where('ip_address', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('user_type')) {
            $query->where('user_type', $request->input('user_type'));
        }

        $filename = 'users-export-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['ID', 'Name', 'Email', 'Mobile', 'IP Address', 'Company', 'Roles', 'Approval Status']);

            $query->orderBy('id')->chunk(200, function ($users) use ($handle) {
                foreach ($users as $user) {
                    fputcsv($handle, [
                        $user->id,
                        $user->name,
                        $user->email,
                        $user->mobile,
                        optional($user->latestLoginActivity)->ip_address,
                        $user->company_name,
                        $user->roles->pluck('title')->implode(' | '),
                        $user->participant ? ($user->participant->is_approved ? 'Approved' : 'Blocked') : 'N/A',
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    private function sendApprovalEmail(User $user): void
    {
        if (! $user->email) {
            return;
        }

        app(AdminNotificationMailer::class)->send('user_approval', [
            'name' => $user->name,
            'email' => $user->email,
            'mobile' => $user->mobile ?: '-',
            'phone' => $user->mobile ?: '-',
            'company_name' => $user->company_name ?: '-',
            'login_url' => route('login'),
            'support_email' => (string) config('mail.from.address', ''),
            'approved_at' => now()->format('d-M-Y h:i A'),
        ], [], [$user->email]);
    }
}
