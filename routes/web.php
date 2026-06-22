<?php

use App\Http\Controllers\Admin\DashboardController as SystemDashboardController;
use App\Http\Controllers\Admin\PermanentDeletionRequestController;
use App\Http\Controllers\Admin\RtProfileController as AdminRtProfileController;
use App\Http\Controllers\Admin\ServiceTypeController as AdminServiceTypeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Kelurahan\ApplicationController as KelurahanApplicationController;
use App\Http\Controllers\Kelurahan\ContactReportController as KelurahanContactReportController;
use App\Http\Controllers\Kelurahan\DashboardController as KelurahanDashboardController;
use App\Http\Controllers\Panel\ProfileController as PanelProfileController;
use App\Http\Controllers\Public\GuestApplicationController;
use App\Http\Controllers\Public\LetterDownloadController;
use App\Http\Controllers\Public\LetterServiceController;
use App\Http\Controllers\Public\ActivityController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\ProfileController;
use App\Http\Controllers\Public\PendataanUlangController;
use App\Http\Controllers\Public\PendataanWargaController;
use App\Http\Controllers\Public\ContactReportController;
use App\Http\Controllers\Public\SecurityController;
use App\Http\Controllers\Public\ServiceCatalogController;
use App\Http\Controllers\Rt\ApplicationController as RtApplicationController;
use App\Http\Controllers\Rt\ContactReportController as RtContactReportController;
use App\Http\Controllers\Rt\DashboardController as RtDashboardController;
use App\Http\Controllers\Rt\HouseholdController;
use App\Http\Controllers\Rt\NotificationController;
use App\Http\Controllers\Rt\PendataanVerificationController;
use App\Http\Controllers\Rt\ResidentController;
use App\Http\Controllers\Rt\ResidentDataController;
use App\Http\Controllers\Rt\RtPublicationController;
use App\Http\Controllers\TrackController;
use App\Http\Middleware\RedirectPengurusToPanel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(RedirectPengurusToPanel::class)->group(function () {
    Route::get('/', HomeController::class)->name('home');
    Route::get('/kegiatan', [ActivityController::class, 'index'])->name('activities.index');
    Route::get('/profil', [ProfileController::class, 'index'])->name('profile.index');
    Route::get('/profil/{rtProfile:slug}/pengurus/{user}', [ProfileController::class, 'showStaff'])->name('profile.staff.show');
    Route::get('/profil/{rtProfile:slug}', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/layanan', [ServiceCatalogController::class, 'index'])->name('services.index');
    Route::redirect('/layanan/pendataan', '/layanan/pendataan-warga', 301)->name('services.pendataan');
    Route::redirect('/layanan/pendataan/berhasil', '/layanan/pendataan-warga/berhasil', 301);
    Route::get('/layanan/pendataan-warga', [PendataanWargaController::class, 'create'])->name('services.pendataan-warga');
    Route::post('/layanan/pendataan-warga', [PendataanWargaController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('services.pendataan-warga.store');
    Route::get('/layanan/pendataan-warga/berhasil', [PendataanWargaController::class, 'success'])
        ->name('services.pendataan-warga.success');
    Route::redirect('/layanan/pembaruan', '/layanan/pendataan-ulang', 301)->name('services.pembaruan');
    Route::redirect('/layanan/pembaruan/berhasil', '/layanan/pendataan-ulang', 301);
    Route::get('/layanan/pendataan-ulang', [PendataanUlangController::class, 'create'])->name('services.pendataan-ulang');
    Route::post('/layanan/pendataan-ulang/verifikasi', [PendataanUlangController::class, 'verify'])->name('services.pendataan-ulang.verify');
    Route::post('/layanan/pendataan-ulang', [PendataanUlangController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('services.pendataan-ulang.store');
    Route::get('/layanan/pendataan-ulang/berhasil', [PendataanUlangController::class, 'success'])
        ->name('services.pendataan-ulang.success');
    Route::redirect('/layanan/lapor-keluar', '/kontak', 301)->name('services.lapor-keluar');
    Route::redirect('/layanan/lapor-keluar/verifikasi', '/kontak', 301)->name('services.lapor-keluar.verify');
    Route::redirect('/layanan/lapor-keluar/berhasil', '/kontak', 301)->name('services.lapor-keluar.success');
    Route::redirect('/layanan/pengaduan', '/kontak?category=pengaduan_lingkungan', 301)->name('services.pengaduan');
    Route::redirect('/layanan/pengaduan/berhasil', '/kontak/terkirim', 301)->name('services.pengaduan.success');
    Route::get('/layanan/surat', [LetterServiceController::class, 'create'])->name('services.surat');
    Route::get('/layanan/surat/verifikasi', [LetterServiceController::class, 'verifyForm'])->name('services.surat.verify-form');
    Route::post('/layanan/surat/verifikasi', [LetterServiceController::class, 'verify'])->name('services.surat.verify');
    Route::redirect('/layanan/surat/katalog', '/layanan/surat')->name('services.surat.catalog');
    Route::post('/layanan/surat/keluar', [LetterServiceController::class, 'logout'])->name('services.surat.logout');
    Route::get('/layanan/permohonan-berhasil/{application:application_number}', [GuestApplicationController::class, 'success'])
        ->name('services.apply.success');
    Route::get('/layanan/{service:code}/ajukan', [GuestApplicationController::class, 'create'])->name('services.apply');
    Route::post('/layanan/{service:code}/ajukan', [GuestApplicationController::class, 'store'])->name('services.apply.store');
    Route::get('/layanan/{service:code}', [ServiceCatalogController::class, 'show'])->name('services.show');

    Route::get('/keamanan', SecurityController::class)->name('security');
    Route::get('/kontak', [ContactReportController::class, 'create'])->name('contact.create');
    Route::post('/kontak', [ContactReportController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('contact.store');
    Route::get('/kontak/terkirim', [ContactReportController::class, 'success'])->name('contact.success');
    Route::get('/lacak', [TrackController::class, 'form'])->name('track.form');
    Route::post('/lacak', [TrackController::class, 'show'])->name('track.show');
    Route::get('/surat/{application:id}/unduh', [LetterDownloadController::class, 'download'])
        ->middleware('signed')
        ->name('public.letter.download');
});

Route::redirect('/pengurus/masuk', '/akses-pengurus', 301);
Route::redirect('/masuk', '/akses-pengurus', 301);
Route::redirect('/masuk/{portal}', '/akses-pengurus', 301)
    ->whereIn('portal', ['rt', 'kelurahan', 'admin']);

Route::middleware('guest')->prefix('akses-pengurus')->group(function () {
    Route::get('/', [LoginController::class, 'create'])->name('login.hub');
    Route::post('/', [LoginController::class, 'store'])->name('login.store');
});

Route::redirect('/akses-pengurus/{portal}', '/akses-pengurus', 301)
    ->whereIn('portal', ['rt', 'kelurahan', 'admin']);

Route::post('/keluar', [LoginController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'role.rt'])->prefix('rt')->name('rt.')->group(function () {
    Route::get('/', RtDashboardController::class)->name('dashboard');
    Route::get('pendataan', [PendataanVerificationController::class, 'index'])->name('pendataan.index');
    Route::get('pendataan/{resident}', [PendataanVerificationController::class, 'show'])->name('pendataan.show');
    Route::get('pendataan/{resident}/dokumen/{document}', [PendataanVerificationController::class, 'viewDocument'])->name('pendataan.document.view');
    Route::get('pendataan/{resident}/dokumen/{document}/cetak', [\App\Http\Controllers\Panel\DocumentViewerController::class, 'rtPendataanDocument'])->name('pendataan.document.print');
    Route::get('pendataan/{resident}/dokumen/{document}/unduh', [PendataanVerificationController::class, 'downloadDocument'])->name('pendataan.document.download');
    Route::post('pendataan/{resident}/setujui', [PendataanVerificationController::class, 'approve'])->name('pendataan.approve');
    Route::post('pendataan/{resident}/tolak', [PendataanVerificationController::class, 'reject'])->name('pendataan.reject');
    Route::get('data-warga', [ResidentDataController::class, 'index'])->name('data-warga.index');
    Route::get('data-warga/report', [ResidentDataController::class, 'report'])->name('data-warga.report');
    Route::get('data-warga/create', [ResidentDataController::class, 'create'])->name('data-warga.create');
    Route::post('data-warga', [ResidentDataController::class, 'store'])->name('data-warga.store');
    Route::get('residents', fn (Request $request) => redirect()->route('rt.data-warga.index', $request->query()))->name('residents.index');
    Route::resource('residents', ResidentController::class)->except(['index']);
    Route::get('households', fn (Request $request) => redirect()->route('rt.data-warga.index', $request->query()))->name('households.index');
    Route::resource('households', HouseholdController::class)->except(['show', 'index']);
    Route::post('households/{household}/sinkron-wajah', [HouseholdController::class, 'syncFaceReferences'])
        ->name('households.sync-face-references');
    Route::get('laporan', [RtContactReportController::class, 'index'])->name('reports.index');
    Route::delete('laporan/{report:report_number}', [RtContactReportController::class, 'destroy'])->name('reports.destroy');
    Route::get('laporan/{report:report_number}', [RtContactReportController::class, 'show'])->name('reports.show');
    Route::post('laporan/{report:report_number}/status', [RtContactReportController::class, 'updateStatus'])->name('reports.status');
    Route::get('applications', [RtApplicationController::class, 'index'])->name('applications.index');
    Route::post('applications/cap', [RtApplicationController::class, 'updateStamp'])->name('applications.stamp.update');
    Route::delete('applications/cap', [RtApplicationController::class, 'destroyStamp'])->name('applications.stamp.destroy');
    Route::delete('applications/{application}', [RtApplicationController::class, 'destroy'])->name('applications.destroy');
    Route::get('applications/{application}', [RtApplicationController::class, 'show'])->name('applications.show');
    Route::post('applications/{application}/verifikasi', [RtApplicationController::class, 'verify'])->name('applications.verify');
    Route::post('applications/{application}/setujui', [RtApplicationController::class, 'approve'])->name('applications.approve');
    Route::post('applications/{application}/tolak', [RtApplicationController::class, 'reject'])->name('applications.reject');
    Route::post('applications/{application}/siap-diambil', [RtApplicationController::class, 'markReady'])->name('applications.mark-ready');
    Route::get('applications/{application}/dokumen/{document}/lihat', [RtApplicationController::class, 'viewDocument'])->name('applications.document.view');
    Route::get('applications/{application}/dokumen/{document}/cetak', [\App\Http\Controllers\Panel\DocumentViewerController::class, 'rtApplicationDocument'])->name('applications.document.print');
    Route::get('applications/{application}/dokumen/{document}', [RtApplicationController::class, 'downloadDocument'])->name('applications.document');
    Route::get('applications/{application}/verifikasi-wajah', [RtApplicationController::class, 'viewIdentitySelfie'])->name('applications.identity-selfie');
    Route::patch('applications/{application}/status', [RtApplicationController::class, 'updateStatus'])->name('applications.status');
    Route::get('applications/{application}/surat/buat', [RtApplicationController::class, 'composeLetter'])->name('applications.letter.compose');
    Route::get('applications/{application}/surat/cari-warga', [RtApplicationController::class, 'lookupLetterResident'])->name('applications.letter.resident-lookup');
    Route::post('applications/{application}/surat/draf', [RtApplicationController::class, 'saveLetterDraft'])->name('applications.letter.draft');
    Route::post('applications/{application}/surat/ttd', [RtApplicationController::class, 'saveLetterSignature'])->name('applications.letter.signature');
    Route::post('applications/{application}/surat/pratinjau', [RtApplicationController::class, 'previewLetter'])->name('applications.letter.preview');
    Route::post('applications/{application}/surat/terbitkan', [RtApplicationController::class, 'publishLetter'])->name('applications.letter.publish');
    Route::post('applications/{application}/surat/kirim-wa', [RtApplicationController::class, 'sendLetterWhatsApp'])->name('applications.letter.whatsapp');
    Route::post('applications/{application}/letter', [RtApplicationController::class, 'generateLetter'])->name('applications.letter');
    Route::get('applications/{application}/surat/lihat', [RtApplicationController::class, 'viewLetter'])->name('applications.letter.view');
    Route::get('applications/{application}/surat/cetak', [\App\Http\Controllers\Panel\DocumentViewerController::class, 'rtLetter'])->name('applications.letter.print');
    Route::get('applications/{application}/unduh', [RtApplicationController::class, 'download'])->name('applications.download');
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('kegiatan', [RtPublicationController::class, 'indexKegiatan'])->name('kegiatan.index');
    Route::get('kegiatan/tambah', [RtPublicationController::class, 'createKegiatan'])->name('kegiatan.create');
    Route::post('kegiatan', [RtPublicationController::class, 'storeKegiatan'])->name('kegiatan.store');
    Route::get('kegiatan/{publication}/edit', [RtPublicationController::class, 'editKegiatan'])->name('kegiatan.edit');
    Route::put('kegiatan/{publication}', [RtPublicationController::class, 'updateKegiatan'])->name('kegiatan.update');
    Route::delete('kegiatan/{publication}', [RtPublicationController::class, 'destroyKegiatan'])->name('kegiatan.destroy');
    Route::post('kegiatan/{publication}/kirim-wa', [RtPublicationController::class, 'sendWhatsAppKegiatan'])->name('kegiatan.whatsapp');
    Route::get('pengumuman', [RtPublicationController::class, 'indexPengumuman'])->name('pengumuman.index');
    Route::get('pengumuman/tambah', [RtPublicationController::class, 'createPengumuman'])->name('pengumuman.create');
    Route::post('pengumuman', [RtPublicationController::class, 'storePengumuman'])->name('pengumuman.store');
    Route::get('pengumuman/{publication}/edit', [RtPublicationController::class, 'editPengumuman'])->name('pengumuman.edit');
    Route::put('pengumuman/{publication}', [RtPublicationController::class, 'updatePengumuman'])->name('pengumuman.update');
    Route::delete('pengumuman/{publication}', [RtPublicationController::class, 'destroyPengumuman'])->name('pengumuman.destroy');
    Route::post('pengumuman/{publication}/kirim-wa', [RtPublicationController::class, 'sendWhatsAppPengumuman'])->name('pengumuman.whatsapp');
    Route::get('profil', [PanelProfileController::class, 'edit'])->name('profile');
    Route::put('profil', [PanelProfileController::class, 'update'])->name('profile.update');
    Route::delete('profil/foto', [PanelProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
});

Route::middleware(['auth', 'role.kelurahan'])->prefix('kelurahan')->name('kelurahan.')->group(function () {
    Route::get('/', KelurahanDashboardController::class)->name('dashboard');
    Route::get('applications', [KelurahanApplicationController::class, 'index'])->name('applications.index');
    Route::get('applications/{application}', [KelurahanApplicationController::class, 'show'])->name('applications.show');
    Route::get('applications/{application}/dokumen/{document}/lihat', [KelurahanApplicationController::class, 'viewDocument'])->name('applications.document.view');
    Route::get('applications/{application}/dokumen/{document}/cetak', [\App\Http\Controllers\Panel\DocumentViewerController::class, 'kelurahanApplicationDocument'])->name('applications.document.print');
    Route::get('applications/{application}/dokumen/{document}', [KelurahanApplicationController::class, 'downloadDocument'])->name('applications.document');
    Route::get('applications/{application}/surat/lihat', [KelurahanApplicationController::class, 'viewLetter'])->name('applications.letter.view');
    Route::get('applications/{application}/surat/cetak', [\App\Http\Controllers\Panel\DocumentViewerController::class, 'kelurahanLetter'])->name('applications.letter.print');
    Route::get('applications/{application}/surat/unduh', [KelurahanApplicationController::class, 'downloadLetter'])->name('applications.letter.download');
    Route::get('kegiatan', [\App\Http\Controllers\Kelurahan\PublicationController::class, 'indexKegiatan'])->name('kegiatan.index');
    Route::get('pengumuman', [\App\Http\Controllers\Kelurahan\PublicationController::class, 'indexPengumuman'])->name('pengumuman.index');
    Route::get('data-penduduk', [\App\Http\Controllers\Kelurahan\ResidentDataController::class, 'index'])->name('population.index');
    Route::get('data-penduduk/{resident}', [\App\Http\Controllers\Kelurahan\ResidentDataController::class, 'show'])->name('data-warga.show');
    Route::get('laporan', [KelurahanContactReportController::class, 'index'])->name('reports.index');
    Route::get('laporan/{report:report_number}', [KelurahanContactReportController::class, 'show'])->name('reports.show');
    Route::post('laporan/{report:report_number}/status', [KelurahanContactReportController::class, 'updateStatus'])->name('reports.status');
});

Route::middleware(['auth', 'role.admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', SystemDashboardController::class)->name('dashboard');
    Route::resource('users', UserController::class)->except(['show']);
    Route::resource('rt-profiles', AdminRtProfileController::class)->except(['show']);
    Route::get('layanan', [AdminServiceTypeController::class, 'index'])->name('services.index');
    Route::get('layanan/{serviceType}/edit', [AdminServiceTypeController::class, 'edit'])->name('services.edit');
    Route::put('layanan/{serviceType}', [AdminServiceTypeController::class, 'update'])->name('services.update');
    Route::delete('layanan/{serviceType}', [AdminServiceTypeController::class, 'destroy'])->name('services.destroy');
    Route::get('hapus-permanen', [PermanentDeletionRequestController::class, 'index'])->name('deletion-requests.index');
    Route::get('hapus-permanen/{deletionRequest}', [PermanentDeletionRequestController::class, 'show'])->name('deletion-requests.show');
    Route::post('hapus-permanen/{deletionRequest}/setujui', [PermanentDeletionRequestController::class, 'approve'])->name('deletion-requests.approve');
    Route::post('hapus-permanen/{deletionRequest}/tolak', [PermanentDeletionRequestController::class, 'reject'])->name('deletion-requests.reject');
    Route::get('profil', [PanelProfileController::class, 'indexAdmin'])->name('profile');
    Route::get('profil/akun', [PanelProfileController::class, 'showAccount'])->name('profile.account.show');
    Route::get('profil/akun/edit', [PanelProfileController::class, 'editAccount'])->name('profile.account.edit');
    Route::put('profil', [PanelProfileController::class, 'update'])->name('profile.update');
    Route::delete('profil/foto', [PanelProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
    Route::get('profil/kelurahan', [PanelProfileController::class, 'showKelurahan'])->name('profile.kelurahan.show');
    Route::get('profil/kelurahan/edit', [PanelProfileController::class, 'editKelurahan'])->name('profile.kelurahan.edit');
    Route::put('profil/kelurahan', [PanelProfileController::class, 'updateKelurahanPublicProfile'])->name('profile.kelurahan.update');
    Route::delete('profil/kelurahan/foto', [PanelProfileController::class, 'destroyKelurahanPublicPhoto'])->name('profile.kelurahan.photo.destroy');
});
