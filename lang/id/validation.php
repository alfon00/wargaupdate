<?php

return [

    'required' => ':attribute wajib diisi.',
    'file' => ':attribute harus berupa berkas.',
    'max' => [
        'file' => ':attribute tidak boleh lebih dari :max kilobita.',
    ],
    'mimes' => ':attribute harus berformat: :values.',
    'uploaded' => ':attribute gagal diunggah. Pastikan ukuran maks. 5 MB, format PDF/JPG/PNG, dan coba lagi.',
    'unique' => ':attribute sudah terdaftar.',
    'distinct' => ':attribute tidak boleh sama dengan anggota lain.',

    'attributes' => [
        'family_card_number' => 'Nomor KK',
        'document_kk' => 'Kartu Keluarga (KK)',
        'document_ktp' => 'KTP Kepala KK',
        'documents' => 'Lampiran tambahan',
        'documents.*' => 'Lampiran tambahan',
        'members.*.nik' => 'NIK anggota',
        'members.*.name' => 'Nama anggota',
        'members.*.document_id' => 'Berkas identitas anggota',
    ],

];
