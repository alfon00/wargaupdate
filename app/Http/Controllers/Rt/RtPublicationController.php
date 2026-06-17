<?php

namespace App\Http\Controllers\Rt;

use App\Enums\RtPublicationType;
use App\Http\Controllers\Controller;
use App\Jobs\SendPublicationWhatsApp;
use App\Models\NotificationLog;
use App\Services\WahaNotificationService;
use App\Models\RtProfile;
use App\Models\RtPublication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RtPublicationController extends Controller
{
    public function indexKegiatan(): View|RedirectResponse
    {
        $rt = $this->requireRtProfile();

        if ($rt instanceof RedirectResponse) {
            return $rt;
        }

        $publications = $rt->publications()
            ->kegiatan()
            ->latest('published_at')
            ->latest('id')
            ->paginate(15);

        return view('rt.publications.index', [
            'type' => RtPublicationType::Kegiatan,
            'publications' => $publications,
            'rt' => $rt,
        ]);
    }

    public function createKegiatan(): View|RedirectResponse
    {
        $rt = $this->requireRtProfile();

        if ($rt instanceof RedirectResponse) {
            return $rt;
        }

        return view('rt.publications.form', [
            'type' => RtPublicationType::Kegiatan,
            'publication' => new RtPublication(['type' => RtPublicationType::Kegiatan, 'is_published' => true]),
            'rt' => $rt,
        ]);
    }

    public function storeKegiatan(Request $request): RedirectResponse
    {
        return $this->store($request, RtPublicationType::Kegiatan);
    }

    public function editKegiatan(RtPublication $publication): View|RedirectResponse
    {
        return $this->edit($publication, RtPublicationType::Kegiatan);
    }

    public function updateKegiatan(Request $request, RtPublication $publication): RedirectResponse
    {
        return $this->update($request, $publication, RtPublicationType::Kegiatan);
    }

    public function destroyKegiatan(RtPublication $publication): RedirectResponse
    {
        return $this->destroy($publication, RtPublicationType::Kegiatan);
    }

    public function indexPengumuman(): View|RedirectResponse
    {
        $rt = $this->requireRtProfile();

        if ($rt instanceof RedirectResponse) {
            return $rt;
        }

        $publications = $rt->publications()
            ->pengumuman()
            ->latest('published_at')
            ->latest('id')
            ->paginate(15);

        return view('rt.publications.index', [
            'type' => RtPublicationType::Pengumuman,
            'publications' => $publications,
            'rt' => $rt,
        ]);
    }

    public function createPengumuman(): View|RedirectResponse
    {
        $rt = $this->requireRtProfile();

        if ($rt instanceof RedirectResponse) {
            return $rt;
        }

        return view('rt.publications.form', [
            'type' => RtPublicationType::Pengumuman,
            'publication' => new RtPublication(['type' => RtPublicationType::Pengumuman, 'is_published' => true]),
            'rt' => $rt,
        ]);
    }

    public function storePengumuman(Request $request): RedirectResponse
    {
        return $this->store($request, RtPublicationType::Pengumuman);
    }

    public function editPengumuman(RtPublication $publication): View|RedirectResponse
    {
        return $this->edit($publication, RtPublicationType::Pengumuman);
    }

    public function updatePengumuman(Request $request, RtPublication $publication): RedirectResponse
    {
        return $this->update($request, $publication, RtPublicationType::Pengumuman);
    }

    public function destroyPengumuman(RtPublication $publication): RedirectResponse
    {
        return $this->destroy($publication, RtPublicationType::Pengumuman);
    }

    public function sendWhatsAppKegiatan(RtPublication $publication): RedirectResponse
    {
        return $this->sendWhatsApp($publication, RtPublicationType::Kegiatan);
    }

    public function sendWhatsAppPengumuman(RtPublication $publication): RedirectResponse
    {
        return $this->sendWhatsApp($publication, RtPublicationType::Pengumuman);
    }

    private function sendWhatsApp(RtPublication $publication, RtPublicationType $type): RedirectResponse
    {
        $rt = $this->requireRtProfile();

        if ($rt instanceof RedirectResponse) {
            return $rt;
        }

        if ($redirect = $this->authorizePublication($publication, $rt, $type)) {
            return $redirect;
        }

        $summary = (new SendPublicationWhatsApp($publication->id))
            ->handle(app(WahaNotificationService::class));

        $message = sprintf(
            'Broadcast WhatsApp selesai: %d terkirim, %d dilewati, %d gagal.',
            $summary['sent'],
            $summary['skipped'],
            $summary['failed'],
        );

        return redirect()
            ->to($type === RtPublicationType::Kegiatan
                ? route('rt.kegiatan.edit', $publication)
                : route('rt.pengumuman.edit', $publication))
            ->with('success', $message);
    }

    private function store(Request $request, RtPublicationType $type): RedirectResponse
    {
        $rt = $this->requireRtProfile();

        if ($rt instanceof RedirectResponse) {
            return $rt;
        }

        $validated = $this->validatePublication($request, $type);
        $publication = new RtPublication($validated);
        $publication->rt_profile_id = $rt->id;
        $publication->type = $type;
        $publication->published_at = now();
        $publication->is_published = true;

        if ($request->hasFile('foto')) {
            $publication->foto_path = $this->storeFoto($request, $rt);
        }

        $publication->save();

        return redirect()
            ->to($this->indexRoute($type))
            ->with('success', $type->label().' berhasil ditambahkan dan tampil di halaman Kegiatan publik.');
    }

    private function edit(RtPublication $publication, RtPublicationType $type): View|RedirectResponse
    {
        $rt = $this->requireRtProfile();

        if ($rt instanceof RedirectResponse) {
            return $rt;
        }

        if ($redirect = $this->authorizePublication($publication, $rt, $type)) {
            return $redirect;
        }

        return view('rt.publications.form', [
            'type' => $type,
            'publication' => $publication,
            'rt' => $rt,
            'publicationBroadcastLogs' => $this->publicationBroadcastLogs($publication),
        ]);
    }

    private function update(Request $request, RtPublication $publication, RtPublicationType $type): RedirectResponse
    {
        $rt = $this->requireRtProfile();

        if ($rt instanceof RedirectResponse) {
            return $rt;
        }

        if ($redirect = $this->authorizePublication($publication, $rt, $type)) {
            return $redirect;
        }

        $validated = $this->validatePublication($request, $type, $publication);
        $publication->fill($validated);

        if ($request->hasFile('foto')) {
            $publication->deleteFoto();
            $publication->foto_path = $this->storeFoto($request, $rt);
        }

        if ($publication->is_published && ! $publication->published_at) {
            $publication->published_at = now();
        }

        $publication->save();

        return redirect()
            ->to($this->indexRoute($type))
            ->with('success', $type->label().' berhasil diperbarui.');
    }

    private function destroy(RtPublication $publication, RtPublicationType $type): RedirectResponse
    {
        $rt = $this->requireRtProfile();

        if ($rt instanceof RedirectResponse) {
            return $rt;
        }

        if ($redirect = $this->authorizePublication($publication, $rt, $type)) {
            return $redirect;
        }

        $publication->deleteFoto();
        $publication->delete();

        return redirect()
            ->to($this->indexRoute($type))
            ->with('success', $type->label().' berhasil dihapus.');
    }

    private function requireRtProfile(): RtProfile|RedirectResponse
    {
        $rt = RtProfile::forRtStaffUser(auth()->user());

        if (! $rt) {
            return redirect()
                ->route('rt.dashboard')
                ->withErrors(['rt' => 'Akun Anda belum terhubung ke profil RT. Hubungi admin untuk menetapkan RT.']);
        }

        return $rt;
    }

    private function authorizePublication(
        RtPublication $publication,
        RtProfile $rt,
        RtPublicationType $type
    ): ?RedirectResponse {
        if ((int) $publication->rt_profile_id !== (int) $rt->id || $publication->type !== $type) {
            abort(404);
        }

        return null;
    }

    /** @return array<string, mixed> */
    private function validatePublication(
        Request $request,
        RtPublicationType $type,
        ?RtPublication $existing = null
    ): array {
        $rules = [
            'judul' => ['required', 'string', 'max:255'],
            'ringkasan' => ['nullable', 'string', 'max:5000'],
            'lokasi' => ['nullable', 'string', 'max:255'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];

        if ($type === RtPublicationType::Kegiatan) {
            $rules['tanggal'] = ['required', 'date'];
        } else {
            $rules['tanggal'] = ['nullable', 'date'];
            $rules['expires_at'] = ['nullable', 'date'];

            if (! $existing) {
                $rules['expires_at'][] = 'after_or_equal:today';
            }
        }

        return $request->validate($rules);
    }

    private function storeFoto(Request $request, RtProfile $rt): string
    {
        $ext = $request->file('foto')->getClientOriginalExtension();

        return $request->file('foto')->storeAs(
            'kegiatan/'.$rt->id,
            uniqid('foto_', true).'.'.$ext,
            'public'
        );
    }

    private function indexRoute(RtPublicationType $type): string
    {
        return $type === RtPublicationType::Kegiatan
            ? route('rt.kegiatan.index')
            : route('rt.pengumuman.index');
    }

    /** @return \Illuminate\Support\Collection<int, NotificationLog> */
    private function publicationBroadcastLogs(RtPublication $publication): \Illuminate\Support\Collection
    {
        if (! $publication->exists) {
            return collect();
        }

        return NotificationLog::query()
            ->forPublication($publication->id)
            ->latest()
            ->limit(10)
            ->get();
    }
}
