@php
    $eligibleResidents = $whatsappEligibleResidents ?? collect();
    $eligibleWithPhone = $eligibleResidents->filter(fn ($resident) => filled($resident->whatsappNotificationPhone()));
@endphp

<form method="POST" action="{{ $whatsappRoute }}" class="lw-panel-form lw-publication-wa-form" id="publication-wa-form">
    @csrf

    <fieldset class="lw-panel-form-fieldset">
        <legend class="lw-panel-form-legend">Penerima notifikasi</legend>
        <div class="lw-panel-field">
            <label class="lw-form-check">
                <input type="radio" name="recipient_mode" value="all" checked data-wa-recipient-mode>
                Semua warga RT dengan notifikasi WhatsApp aktif
                @if($eligibleWithPhone->isNotEmpty())
                    <span class="lw-panel-card-note">({{ $eligibleWithPhone->count() }} nomor siap kirim)</span>
                @endif
            </label>
            <label class="lw-form-check lw-mt-2">
                <input type="radio" name="recipient_mode" value="selected" data-wa-recipient-mode>
                Pilih warga tertentu
            </label>
        </div>

        <div class="lw-panel-field lw-mt-3" id="wa-recipient-picker" hidden>
            @if($eligibleResidents->isEmpty())
                <p class="lw-panel-card-note">Belum ada warga aktif dengan notifikasi WhatsApp.</p>
            @else
                <div class="lw-panel-wa-recipient-toolbar lw-mb-2">
                    <button type="button" class="lw-panel-link" data-wa-select-all>Pilih semua</button>
                    <span aria-hidden="true">·</span>
                    <button type="button" class="lw-panel-link" data-wa-clear-all>Kosongkan</button>
                </div>
                <div class="lw-panel-wa-recipient-list">
                    @foreach($eligibleResidents as $resident)
                        @php
                            $phone = $resident->whatsappNotificationPhone();
                            $canSend = filled($phone);
                        @endphp
                        <label class="lw-form-check lw-panel-wa-recipient-item @if(! $canSend) is-disabled @endif">
                            <input type="checkbox"
                                name="resident_ids[]"
                                value="{{ $resident->id }}"
                                data-wa-resident-checkbox
                                @disabled(! $canSend)>
                            <span>
                                <strong>{{ $resident->name }}</strong>
                                @if($canSend)
                                    <span class="lw-panel-card-note">· {{ $phone }}</span>
                                @else
                                    <span class="lw-panel-card-note">· nomor WhatsApp tidak tersedia</span>
                                @endif
                            </span>
                        </label>
                    @endforeach
                </div>
            @endif
        </div>
    </fieldset>

    <div class="lw-panel-form-actions">
        <button type="submit" class="lw-panel-btn lw-panel-btn--secondary">Kirim WhatsApp ke warga</button>
    </div>
</form>

<script>
(() => {
    const form = document.getElementById('publication-wa-form');
    if (!form) return;

    const picker = document.getElementById('wa-recipient-picker');
    const modeInputs = form.querySelectorAll('[data-wa-recipient-mode]');
    const checkboxes = form.querySelectorAll('[data-wa-resident-checkbox]:not(:disabled)');
    const selectAllBtn = form.querySelector('[data-wa-select-all]');
    const clearAllBtn = form.querySelector('[data-wa-clear-all]');

    const syncPicker = () => {
        const selectedMode = form.querySelector('[data-wa-recipient-mode]:checked')?.value ?? 'all';
        if (picker) picker.hidden = selectedMode !== 'selected';
    };

    modeInputs.forEach((input) => input.addEventListener('change', syncPicker));
    syncPicker();

    selectAllBtn?.addEventListener('click', () => {
        checkboxes.forEach((box) => { box.checked = true; });
    });

    clearAllBtn?.addEventListener('click', () => {
        checkboxes.forEach((box) => { box.checked = false; });
    });

    form.addEventListener('submit', (event) => {
        const mode = form.querySelector('[data-wa-recipient-mode]:checked')?.value ?? 'all';
        if (mode === 'selected') {
            const picked = form.querySelectorAll('[data-wa-resident-checkbox]:checked').length;
            if (picked < 1) {
                event.preventDefault();
                window.alert('Pilih minimal satu warga penerima.');
                return;
            }
            if (!window.confirm(`Kirim notifikasi WhatsApp ke ${picked} warga terpilih?`)) {
                event.preventDefault();
            }
            return;
        }
        if (!window.confirm('Kirim notifikasi WhatsApp ke semua warga RT yang mengaktifkan notifikasi?')) {
            event.preventDefault();
        }
    });
})();
</script>
