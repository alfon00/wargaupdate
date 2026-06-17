<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\KelurahanOfficial;
use App\Services\SyncUserPublicProfile;
use App\Support\PhoneNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View|RedirectResponse
    {
        $user = auth()->user();

        if ($user->isSuperAdmin()) {
            return redirect()->route('admin.profile');
        }

        return view('panel.profile', [
            'user' => $user,
        ]);
    }

    public function indexAdmin(): View
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        $user = auth()->user();
        $lurah = KelurahanOfficial::publicLurahArray();

        return view('panel.profile.admin-index', [
            'user' => $user,
            'lurah' => $lurah,
        ]);
    }

    public function showAccount(): View
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        return view('panel.profile.admin-account-show', [
            'user' => auth()->user(),
        ]);
    }

    public function editAccount(): View
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        return view('panel.profile.admin-account-edit', [
            'user' => auth()->user(),
        ]);
    }

    public function showKelurahan(): View
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        return view('panel.profile.admin-kelurahan-show', [
            'lurah' => KelurahanOfficial::publicLurahArray(),
        ]);
    }

    public function editKelurahan(): View
    {
        abort_unless(auth()->user()?->isSuperAdmin(), 403);

        return view('panel.profile.admin-kelurahan-edit', [
            'lurahOfficial' => KelurahanOfficial::lurah(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'phone' => PhoneNormalizer::validationRules(unchangedFrom: $user->phone),
            'avatar' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];

        if ($user->isRtStaff()) {
            $rules['public_bio'] = ['nullable', 'string', 'max:1000'];
            if ($user->rt_profile_id) {
                $rules['contact_email'] = [
                    'nullable',
                    'email',
                    'max:255',
                    function (string $attribute, mixed $value, \Closure $fail) use ($request): void {
                        if (! filled($value)) {
                            return;
                        }

                        $loginEmail = Str::lower(trim((string) $request->input('email')));
                        if (Str::lower(trim((string) $value)) === $loginEmail) {
                            $fail('Email kontak RT tidak boleh sama dengan email untuk masuk.');
                        }
                    },
                ];
            }
        }

        if ($request->filled('password')) {
            $rules['password'] = ['required', 'confirmed', Password::min(8)];
            $rules['current_password'] = ['required', 'current_password'];
        }

        $validated = $request->validate($rules);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->phone = $validated['phone'] ?? null;
        if (array_key_exists('public_bio', $validated)) {
            $user->public_bio = $validated['public_bio'] ?? null;
        }

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }

            $ext = $request->file('avatar')->getClientOriginalExtension();
            $path = $request->file('avatar')->storeAs(
                'avatars',
                $user->id.'.'.$ext,
                'public'
            );
            $user->avatar_path = $path;
        }

        $user->save();

        if ($user->isRtStaff() && $user->rt_profile_id && array_key_exists('contact_email', $validated)) {
            $rtProfile = $user->rtProfile;
            if ($rtProfile) {
                $rtProfile->email = $validated['contact_email'] ?? null;
                $rtProfile->save();
            }
        }

        app(SyncUserPublicProfile::class)->sync($user->fresh());

        $redirectRoute = $user->isSuperAdmin()
            ? route('admin.profile.account.show')
            : $user->profileRoute();

        $message = $user->isSuperAdmin()
            ? 'Profil akun berhasil diperbarui.'
            : 'Profil berhasil diperbarui dan disinkronkan ke halaman Profil publik.';

        return redirect()->to($redirectRoute)->with('success', $message);
    }

    public function updateKelurahanPublicProfile(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $validated = $request->validate([
            'jabatan' => ['required', 'string', 'max:255'],
            'nama' => ['nullable', 'string', 'max:255'],
            'telepon' => PhoneNormalizer::validationRules(),
            'whatsapp' => PhoneNormalizer::validationRules(),
            'email' => ['nullable', 'email', 'max:255'],
            'alamat_kantor' => ['nullable', 'string', 'max:500'],
            'jam_layanan' => ['nullable', 'string', 'max:255'],
            'visi' => ['nullable', 'string'],
            'misi' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ]);

        $official = KelurahanOfficial::lurah();
        $official->fill([
            'jabatan' => $validated['jabatan'],
            'nama' => $validated['nama'] ?? null,
            'telepon' => $validated['telepon'] ?? null,
            'whatsapp' => $validated['whatsapp'] ?? null,
            'email' => $validated['email'] ?? null,
            'alamat_kantor' => $validated['alamat_kantor'] ?? null,
            'jam_layanan' => $validated['jam_layanan'] ?? null,
            'visi' => $validated['visi'] ?? null,
            'misi' => $validated['misi'] ?? null,
        ]);

        if ($request->hasFile('photo')) {
            if ($official->photo_path) {
                Storage::disk('public')->delete($official->photo_path);
            }

            $ext = $request->file('photo')->getClientOriginalExtension();
            $official->photo_path = $request->file('photo')->storeAs(
                'kelurahan',
                'lurah.'.$ext,
                'public'
            );
        }

        $official->save();

        return redirect()->route('admin.profile.kelurahan.show')->with('success', 'Profil Kelurahan publik berhasil diperbarui.');
    }

    public function destroyKelurahanPublicPhoto(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isSuperAdmin(), 403);

        $official = KelurahanOfficial::lurah();

        if ($official->photo_path) {
            Storage::disk('public')->delete($official->photo_path);
            $official->photo_path = null;
            $official->save();
        }

        return redirect()->route('admin.profile.kelurahan.edit')->with('success', 'Foto lurah dihapus dari halaman publik.');
    }

    public function destroyAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
            $user->avatar_path = null;
            $user->save();

            app(SyncUserPublicProfile::class)->sync($user->fresh());
        }

        $redirectRoute = $user->isSuperAdmin()
            ? route('admin.profile.account.edit')
            : $user->profileRoute();

        return redirect()->to($redirectRoute)->with('success', 'Foto profil berhasil dihapus.');
    }
}
