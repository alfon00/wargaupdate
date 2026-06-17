@props([
    'descriptorName' => 'head_face_descriptor',
    'selfieName' => 'head_selfie_data',
    'statusMessage' => 'Ketuk Buka kamera, posisikan wajah, lalu ambil foto selfie.',
])

<div id="lw-face-capture" class="lw-face-capture lw-mt-3">
    <div class="lw-face-capture__frame">
        <button type="button" id="face-switch-button" class="lw-face-capture__switch-btn" hidden aria-label="Ganti kamera">⟲ Ganti kamera</button>
        <div id="face-capture-preview-wrap" class="lw-face-capture__preview-wrap is-placeholder">
            <video id="face-capture-video" class="lw-face-capture__video" playsinline webkit-playsinline muted hidden></video>
            <img id="face-capture-preview" class="lw-face-capture__preview" alt="Pratinjau selfie" hidden>
            <p class="lw-face-capture__placeholder-text">Kamera belum aktif</p>
        </div>
        <span class="lw-face-capture__ring" aria-hidden="true"></span>
    </div>
    <canvas id="face-capture-canvas" class="lw-face-capture__canvas" hidden></canvas>
    <ol class="lw-face-capture__steps" aria-label="Langkah verifikasi wajah">
        <li class="lw-face-capture__step" data-face-step="position">Posisi wajah</li>
        <li class="lw-face-capture__step" data-face-step="capture">Siap foto</li>
    </ol>
    <p id="face-capture-status" class="lw-form-hint lw-mt-2 lw-mb-0 lw-face-capture__status">{{ $statusMessage }}</p>
    @error($descriptorName)<p class="lw-form-error">{{ $message }}</p>@enderror
    @error($selfieName)<p class="lw-form-error">{{ $message }}</p>@enderror
    <div class="lw-form-actions lw-mt-3 lw-face-capture__actions">
        <button type="button" id="face-start-button" class="lw-btn-primary lw-face-capture__start-btn" disabled>Buka kamera</button>
        <button type="button" id="face-capture-button" class="lw-btn-secondary" disabled>Ambil foto selfie</button>
        <button type="button" id="face-retake-button" class="lw-btn-secondary" hidden>Ambil ulang foto</button>
    </div>
</div>
<input type="hidden" name="{{ $descriptorName }}" id="{{ $descriptorName }}" value="{{ is_array(old($descriptorName)) ? json_encode(old($descriptorName)) : old($descriptorName) }}">
<input type="hidden" name="{{ $selfieName }}" id="{{ $selfieName }}" value="{{ old($selfieName) }}">
