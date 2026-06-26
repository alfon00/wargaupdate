<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\RtProfile;
use App\Models\User;
use App\Services\SyncUserPublicProfile;
use App\Support\PhoneNormalizer;
use App\Support\StaffEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $query = User::query()
            ->whereIn('role', array_map(fn (UserRole $role) => $role->value, UserRole::pengurusCases()))
            ->with('rtProfile')
            ->latest();

        if ($role = request('role')) {
            $query->where('role', $role);
        }

        if ($q = trim((string) request('q', ''))) {
            $term = '%'.$q.'%';
            $query->where(function ($sub) use ($term) {
                $sub->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term);
            });
        }

        $users = $query->paginate(20)->withQueryString();

        $roles = collect(UserRole::pengurusCases())
            ->mapWithKeys(fn (UserRole $r) => [$r->value => $r->label()]);

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'user' => new User,
            'roles' => $this->assignableRoles(),
            'roleGroups' => $this->assignableRoleGroups(),
            'roleDescriptions' => collect(UserRole::pengurusCases())
                ->mapWithKeys(fn (UserRole $role) => [$role->value => $role->description()])
                ->all(),
            'rtProfiles' => RtProfile::forStaffAssignment(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->normalizeRtProfileAssignment($this->validateUser($request));
        $validated['email'] = StaffEmail::compose($validated['email_local']);
        unset($validated['email_local']);
        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);
        app(SyncUserPublicProfile::class)->sync($user);

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'user' => $user,
            'roles' => $this->assignableRoles(),
            'roleGroups' => $this->assignableRoleGroups(),
            'roleDescriptions' => collect(UserRole::pengurusCases())
                ->mapWithKeys(fn (UserRole $role) => [$role->value => $role->description()])
                ->all(),
            'rtProfiles' => RtProfile::forStaffAssignment(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $this->normalizeRtProfileAssignment($this->validateUser($request, $user));
        $validated['email'] = StaffEmail::compose($validated['email_local']);
        unset($validated['email_local']);
        if (! empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        $user->update($validated);
        app(SyncUserPublicProfile::class)->sync($user->fresh());

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->withErrors(['delete' => 'Anda tidak dapat menghapus akun yang sedang digunakan.']);
        }

        if ($user->role === UserRole::Kelurahan && User::where('role', UserRole::Kelurahan)->count() <= 1) {
            return redirect()->route('admin.users.index')->withErrors(['delete' => 'Tidak dapat menghapus satu-satunya akun kelurahan.']);
        }

        if ($user->avatar_path && Storage::disk('public')->exists($user->avatar_path)) {
            Storage::disk('public')->delete($user->avatar_path);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Pengguna berhasil dihapus.');
    }

    /** @return array<string, string> */
    private function assignableRoles(): array
    {
        return collect(UserRole::pengurusCases())
            ->sortBy(fn (UserRole $role) => match ($role) {
                UserRole::KetuaRt => 1,
                UserRole::Kelurahan => 2,
            })
            ->mapWithKeys(fn (UserRole $role) => [$role->value => $role->label()])
            ->all();
    }

    /** @return array<string, array<string, string>> */
    private function assignableRoleGroups(): array
    {
        return collect(UserRole::pengurusCases())
            ->groupBy(fn (UserRole $role) => $role->accountGroup())
            ->map(fn ($roles) => $roles
                ->sortBy(fn (UserRole $role) => match ($role) {
                    UserRole::KetuaRt => 1,
                    UserRole::Kelurahan => 2,
                })
                ->mapWithKeys(fn (UserRole $role) => [$role->value => $role->label()])
                ->all())
            ->all();
    }

    /** @return array<string, mixed> */
    private function validateUser(Request $request, ?User $user = null): array
    {
        $rtRoles = [UserRole::KetuaRt->value];

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email_local' => [
                ...StaffEmail::validationRules(),
                function (string $attribute, mixed $value, \Closure $fail) use ($user): void {
                    if (! is_string($value)) {
                        return;
                    }

                    $email = StaffEmail::compose($value);
                    $query = User::where('email', $email);
                    if ($user) {
                        $query->where('id', '!=', $user->id);
                    }

                    if ($query->exists()) {
                        $fail('Email sudah digunakan.');
                    }
                },
            ],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8'],
            'role' => ['required', Rule::in(array_keys($this->assignableRoles()))],
            'phone' => PhoneNormalizer::validationRules(),
            'rt_profile_id' => [
                Rule::requiredIf(in_array($request->input('role'), $rtRoles, true)),
                'nullable',
                'exists:rt_profiles,id',
            ],
        ]);
    }

    /** @param  array<string, mixed>  $validated */
    private function normalizeRtProfileAssignment(array $validated): array
    {
        $rtRoles = [UserRole::KetuaRt->value];

        if (! in_array($validated['role'] ?? '', $rtRoles, true)) {
            $validated['rt_profile_id'] = null;

            return $validated;
        }

        if (! empty($validated['rt_profile_id'])) {
            $profile = RtProfile::find($validated['rt_profile_id']);
            if ($profile) {
                $canonicalId = RtProfile::canonicalProfileIdForRtNumber($profile->rt_number);
                if ($canonicalId) {
                    $validated['rt_profile_id'] = $canonicalId;
                }
            }
        }

        return $validated;
    }
}
