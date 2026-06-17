@php
    /** @var \App\Models\Resident $resident */
    $household = $resident->household;
    $isEdit = ($mode ?? 'show') === 'edit';

    if ($isEdit) {
        $demo = config('kelurahan.resident_demographics', []);
        $relationshipOptions = ['Kepala Keluarga', 'Istri', 'Anak', 'Orang Tua', 'Anggota Keluarga', 'Lainnya'];
        $currentRelationship = old('relationship_to_head', $resident->relationship_to_head ?: ($resident->is_head_of_family ? 'Kepala Keluarga' : ''));
        $relationshipPreset = in_array($currentRelationship, $relationshipOptions, true) ? $currentRelationship : (filled($currentRelationship) ? 'Lainnya' : '');
        $relationshipCustom = $relationshipPreset === 'Lainnya' ? $currentRelationship : '';
        $occupationVal = old('occupation', $resident->occupation);
        $occupations = $demo['occupations'] ?? [];
        $occupationLegacy = filled($occupationVal) && ! in_array($occupationVal, $occupations, true);
    }
@endphp

<div class="lw-panel-table-wrap">
    <table class="lw-panel-table lw-rt-resident-detail-table">
        <tbody>
            @include('rt.residents._household-kk-table', [
                'mode' => $mode ?? 'show',
                'household' => $household,
                'resident' => $resident,
            ])

            <tr class="lw-rt-resident-detail-section">
                <th colspan="2">Identitas warga</th>
            </tr>
            <tr>
                <th scope="row">Nama lengkap</th>
                <td>
                    @if($isEdit)
                        <input name="name" value="{{ old('name', $resident->name) }}" required>
                    @else
                        {{ $resident->name }}
                    @endif
                </td>
            </tr>
            <tr>
                <th scope="row">NIK</th>
                <td>
                    @if($isEdit)
                        <input name="nik" value="{{ old('nik', $resident->nik) }}" maxlength="16" inputmode="numeric">
                    @else
                        {{ $resident->nik ?: '—' }}
                    @endif
                </td>
            </tr>
            <tr>
                <th scope="row">Tempat, tanggal lahir</th>
                <td>
                    @if($isEdit)
                        <div class="lw-rt-edit-inline-pair">
                            <input name="birth_place" value="{{ old('birth_place', $resident->birth_place) }}" placeholder="Tempat lahir">
                            <input type="date" name="birth_date" value="{{ old('birth_date', $resident->birth_date?->format('Y-m-d')) }}">
                        </div>
                    @else
                        {{ $resident->birthPlaceDate() }}
                    @endif
                </td>
            </tr>
            <tr>
                <th scope="row">Jenis kelamin</th>
                <td>
                    @if($isEdit)
                        <select name="gender">
                            <option value="">—</option>
                            @foreach(['Laki-laki', 'Perempuan'] as $g)
                                <option value="{{ $g }}" @selected(old('gender', $resident->gender) === $g)>{{ $g }}</option>
                            @endforeach
                        </select>
                    @else
                        {{ $resident->gender ?: '—' }}
                    @endif
                </td>
            </tr>
            <tr>
                <th scope="row">Agama</th>
                <td>
                    @if($isEdit)
                        <select name="religion" id="religion">
                            <option value="">— Pilih —</option>
                            @foreach($demo['religions'] ?? [] as $rel)
                                <option value="{{ $rel }}" @selected(old('religion', $resident->religion) === $rel)>{{ $rel }}</option>
                            @endforeach
                        </select>
                        @error('religion')<p class="lw-form-error">{{ $message }}</p>@enderror
                    @else
                        {{ $resident->religion ?: '—' }}
                    @endif
                </td>
            </tr>
            <tr>
                <th scope="row">Suku</th>
                <td>
                    @if($isEdit)
                        <input name="suku" value="{{ old('suku', $household?->suku) }}" placeholder="Contoh: Amungme / Kamoro" maxlength="100" required>
                        @error('suku')<p class="lw-form-error">{{ $message }}</p>@enderror
                    @else
                        {{ $household?->suku ?: '—' }}
                    @endif
                </td>
            </tr>
            <tr>
                <th scope="row">Status domisili</th>
                <td>
                    <span class="lw-badge {{ $resident->domicile_status?->badgeClass() }}">
                        {{ $resident->domicile_status?->label() ?? '—' }}
                    </span>
                </td>
            </tr>
            <tr>
                <th scope="row">Kewarganegaraan</th>
                <td>
                    @if($isEdit)
                        <select name="citizenship">
                            @foreach($demo['citizenships'] ?? ['WNI'] as $cit)
                                <option value="{{ $cit }}" @selected(old('citizenship', $resident->citizenship ?: 'WNI') === $cit)>{{ $cit }}</option>
                            @endforeach
                        </select>
                    @else
                        {{ $resident->citizenship ?: '—' }}
                    @endif
                </td>
            </tr>

            <tr class="lw-rt-resident-detail-section">
                <th colspan="2">Sosial & pendidikan</th>
            </tr>
            <tr>
                <th scope="row">Pendidikan</th>
                <td>
                    @if($isEdit)
                        <select name="education">
                            <option value="">— Pilih —</option>
                            @foreach($demo['education_levels'] ?? [] as $level)
                                <option value="{{ $level }}" @selected(old('education', $resident->education) === $level)>{{ $level }}</option>
                            @endforeach
                        </select>
                    @else
                        {{ $resident->education ?: '—' }}
                    @endif
                </td>
            </tr>
            <tr>
                <th scope="row">Pekerjaan</th>
                <td>
                    @if($isEdit)
                        <select name="occupation" id="occupation">
                            <option value="">— Pilih —</option>
                            @if($occupationLegacy)
                                <option value="{{ $occupationVal }}" selected>{{ $occupationVal }}</option>
                            @endif
                            @foreach($occupations as $occ)
                                <option value="{{ $occ }}" @selected(! $occupationLegacy && old('occupation', $resident->occupation) === $occ)>{{ $occ }}</option>
                            @endforeach
                        </select>
                        @error('occupation')<p class="lw-form-error">{{ $message }}</p>@enderror
                        <p class="lw-panel-field-hint lw-mt-1 is-hidden" id="head-recap-hint">
                            Agama dan pekerjaan wajib diisi jika warga ditandai sebagai kepala keluarga (untuk rekap kelurahan).
                        </p>
                    @else
                        {{ $resident->occupation ?: '—' }}
                    @endif
                </td>
            </tr>
            <tr>
                <th scope="row">Status perkawinan</th>
                <td>
                    @if($isEdit)
                        <select name="marital_status">
                            <option value="">— Pilih —</option>
                            @foreach($demo['marital_statuses'] ?? [] as $status)
                                <option value="{{ $status }}" @selected(old('marital_status', $resident->marital_status) === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    @else
                        {{ $resident->marital_status ?: '—' }}
                    @endif
                </td>
            </tr>
            <tr>
                <th scope="row">Hubungan dalam KK</th>
                <td>
                    @if($isEdit)
                        <select name="relationship_preset" id="relationship_preset" class="lw-mb-1">
                            <option value="">— Pilih —</option>
                            @foreach($relationshipOptions as $opt)
                                <option value="{{ $opt }}" @selected($relationshipPreset === $opt)>{{ $opt }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="relationship_to_head" id="relationship_to_head" maxlength="30"
                            value="{{ old('relationship_to_head', $relationshipPreset === 'Lainnya' ? $relationshipCustom : ($relationshipPreset ?: $currentRelationship)) }}"
                            placeholder="Isi hubungan dalam KK"
                            @if($relationshipPreset !== 'Lainnya' && filled($relationshipPreset)) readonly @endif>
                        @error('relationship_to_head')<p class="lw-form-error">{{ $message }}</p>@enderror
                    @else
                        {{ $resident->relationship_to_head ?: ($resident->is_head_of_family ? 'Kepala Keluarga' : '—') }}
                    @endif
                </td>
            </tr>

            <tr class="lw-rt-resident-detail-section">
                <th colspan="2">Kontak & sistem</th>
            </tr>
            <tr>
                <th scope="row">Nomor HP/ WhatsApp</th>
                <td>
                    @if($isEdit)
                        <x-phone-input name="phone" :value="old('phone', $resident->phone)" />
                        <p class="lw-panel-field-hint">Nomor untuk verifikasi layanan portal. Perbarui jika kartu lama tidak aktif.</p>
                    @else
                        {{ $resident->phone ?: '—' }}
                    @endif
                </td>
            </tr>
            <tr>
                <th scope="row">Notifikasi WhatsApp</th>
                <td>
                    @if($isEdit)
                        @include('partials.whatsapp-notify-locked', [
                            'label' => 'Aktif',
                            'checkClass' => 'lw-rt-edit-check',
                        ])
                    @else
                        @if($resident->hasLatestWhatsappNotificationFailed())
                            <span class="lw-badge lw-badge--amber">Gagal terkirim</span>
                        @elseif($resident->whatsapp_notify)
                            <span class="lw-badge lw-badge--green">Aktif</span>
                        @else
                            <span class="lw-badge lw-badge--muted">Nonaktif</span>
                        @endif
                    @endif
                </td>
            </tr>
            @if($household)
                <tr>
                    <th scope="row">Kategori sumber</th>
                    <td>{{ $household->dataSourceLabel() }}</td>
                </tr>
            @endif

            @if($resident->verified_at)
                <tr>
                    <th scope="row">Diverifikasi RT</th>
                    <td>{{ $resident->verified_at->timezone('Asia/Jayapura')->format('d/m/Y H:i') }}
                        @if($resident->verifier) · {{ $resident->verifier->name }} @endif
                    </td>
                </tr>
            @endif
            @if($resident->verification_notes)
                <tr>
                    <th scope="row">Catatan verifikasi</th>
                    <td class="lw-pre-wrap">{{ $resident->verification_notes }}</td>
                </tr>
            @endif
            @if($resident->domicile_status?->isArchived() && empty($skipArchiveSection))
                <tr>
                    <th scope="row">Tanggal arsip</th>
                    <td>{{ $resident->departed_at?->timezone('Asia/Jayapura')->format('d/m/Y') ?? '—' }}</td>
                </tr>
                @if($resident->departure_notes)
                    <tr>
                        <th scope="row">Catatan keluar</th>
                        <td class="lw-pre-wrap">{{ $resident->departure_notes }}</td>
                    </tr>
                @endif
            @endif
        </tbody>
    </table>
</div>
