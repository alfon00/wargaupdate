@php
    use App\Models\PendataanDocument;
    use App\Models\Resident;
    use App\Services\GuestResidentService;

    /** @var Resident $resident */
    $guestResidents = app(GuestResidentService::class);
    $identityTypes = $guestResidents->identityDocumentTypesForResident($resident);
    $documents = $household->pendataanDocuments
        ->filter(fn (PendataanDocument $doc) => in_array($doc->document_type, $identityTypes, true))
        ->sortBy(fn (PendataanDocument $doc) => array_search($doc->document_type, $identityTypes, true))
        ->values();
    $streamHead = $head ?? $household->headResident;
    $identityLabel = $guestResidents->memberUsesKiaDocument($resident) ? 'KIA' : 'KTP';
@endphp

<fieldset class="lw-panel-form-fieldset lw-rt-edit-documents">
    <legend class="lw-panel-form-legend">Lampiran berkas</legend>

    @if($documents->isNotEmpty())
        <div class="lw-rt-edit-doc-existing">
            <p class="lw-panel-field-hint lw-mb-4">Berkas identitas anggota ini. Centang hapus jika perlu dihapus saat simpan.</p>
            <div class="lw-rt-doc-grid lw-rt-doc-grid--compact">
                @foreach($documents as $doc)
                    <div class="lw-rt-edit-doc-item">
                        @include('components.rt.partials.pendataan-document-card', [
                            'doc' => $doc,
                            'head' => $streamHead,
                            'variant' => 'compact',
                        ])
                        <label class="lw-rt-edit-doc-remove">
                            <input type="checkbox" name="remove_identity_document[]" value="{{ $doc->id }}">
                            Hapus berkas ini
                        </label>
                    </div>
                @endforeach
            </div>
            @error('remove_identity_document')<p class="lw-form-error">{{ $message }}</p>@enderror
        </div>
    @else
        <p class="lw-panel-field-hint lw-mb-4">
            @if($household->isRtDirectEntry())
                Belum ada berkas identitas anggota ini — Anda dapat mengunggah di bawah.
            @else
                Belum ada berkas identitas anggota ini — berkas belum diunggah warga atau pengajuan sebelum fitur unggah.
            @endif
        </p>
    @endif

    <div class="lw-rt-edit-doc-upload">
        <p class="lw-rt-edit-doc-upload-title">Unggah berkas baru</p>
        <p class="lw-panel-field-hint lw-mb-4">
            Format: PDF/JPG/JPEG/PNG maksimal 5 MB. Kosongkan field jika tidak ingin mengubah berkas identitas anggota ini.
        </p>
        <div class="lw-panel-form-grid lw-panel-form-grid--labeled">
            <div class="lw-panel-field lw-panel-field--span2">
                <label class="lw-panel-field-label">Ganti scan/foto {{ $identityLabel }} (opsional)</label>
                <input type="file" name="document_identity" class="lw-panel-field-input" accept=".pdf,.jpg,.jpeg,.png">
                @error('document_identity')<p class="lw-form-error">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
</fieldset>
