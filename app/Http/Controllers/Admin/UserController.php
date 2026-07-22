<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('search')->toString();
        $role = $request->string('role')->toString();
        $status = $request->string('status')->toString();

        $users = User::query()
            ->when($search, function ($query, $searchValue) {
                $query->where(function ($subQuery) use ($searchValue): void {
                    $subQuery->where('name', 'like', "%{$searchValue}%")
                        ->orWhere('email', 'like', "%{$searchValue}%")
                        ->orWhere('phone', 'like', "%{$searchValue}%");
                });
            })
            ->when($role, fn ($query, $roleValue) => $query->where('role', $roleValue))
            ->when($status !== '', fn ($query) => $query->where('is_active', $status === 'active'))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $search,
            'role' => $role,
            'status' => $status,
            'roles' => User::MANAGEABLE_ROLES,
            'roleLabels' => User::ROLE_LABELS,
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => User::MANAGEABLE_ROLES,
            'roleLabels' => User::ROLE_LABELS,
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(User::MANAGEABLE_ROLES)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $createdUser = User::create($validated);

        $this->recordAudit('user.created', 'Super admin menambahkan pengguna baru.', $createdUser);

        return redirect()->route($this->routePrefix().'.index')->with('success', 'Data pengguna berhasil ditambahkan.');
    }

    public function show(User $user): View
    {
        return view('admin.users.show', [
            'user' => $user,
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
            'roles' => User::MANAGEABLE_ROLES,
            'roleLabels' => User::ROLE_LABELS,
            'routePrefix' => $this->routePrefix(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(User::MANAGEABLE_ROLES)],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        if ($this->isProtectedLastSuperAdmin($user, $validated['role'], $validated['is_active'])) {
            return back()
                ->withInput()
                ->with('error', 'Super admin terakhir tidak dapat diubah rolenya atau dinonaktifkan.');
        }

        $user->update($validated);

        $this->recordAudit('user.updated', 'Super admin memperbarui data pengguna.', $user);

        return redirect()->route($this->routePrefix().'.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        if ($this->isLastSuperAdmin($user)) {
            return back()->with('error', 'Super admin terakhir tidak dapat dihapus.');
        }

        $deletedUserId = $user->id;
        $deletedUserName = $user->name;
        $deletedUserRole = $user->role;
        $user->delete();

        if (auth()->user()?->hasRole(User::ROLE_SUPER_ADMIN)) {
            AuditLog::query()->create([
                'user_id' => auth()->id(),
                'action' => 'user.deleted',
                'subject_type' => User::class,
                'subject_id' => $deletedUserId,
                'description' => 'Super admin menghapus pengguna.',
                'metadata' => [
                    'name' => $deletedUserName,
                    'role' => $deletedUserRole,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        }

        return redirect()->route($this->routePrefix().'.index')->with('success', 'Data pengguna berhasil dihapus.');
    }

    private function routePrefix(): string
    {
        return auth()->user()?->hasRole(User::ROLE_SUPER_ADMIN)
            ? 'super-admin.users'
            : 'admin.users';
    }

    private function isProtectedLastSuperAdmin(User $user, string $targetRole, bool $targetActive): bool
    {
        if (! $this->isLastSuperAdmin($user)) {
            return false;
        }

        return $targetRole !== User::ROLE_SUPER_ADMIN || ! $targetActive;
    }

    private function isLastSuperAdmin(User $user): bool
    {
        if (! $user->hasRole(User::ROLE_SUPER_ADMIN)) {
            return false;
        }

        return User::query()
            ->where('role', User::ROLE_SUPER_ADMIN)
            ->where('is_active', true)
            ->count() <= 1;
    }

    private function recordAudit(string $action, string $description, User $subject): void
    {
        if (! auth()->user()?->hasRole(User::ROLE_SUPER_ADMIN)) {
            return;
        }

        AuditLog::query()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => User::class,
            'subject_id' => $subject->id,
            'description' => $description,
            'metadata' => [
                'name' => $subject->name,
                'email' => $subject->email,
                'role' => $subject->role,
                'is_active' => $subject->is_active,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
