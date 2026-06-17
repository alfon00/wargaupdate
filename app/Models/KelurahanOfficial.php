<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class KelurahanOfficial extends Model
{
    protected $fillable = [
        'role',
        'jabatan',
        'nama',
        'photo_path',
        'telepon',
        'whatsapp',
        'email',
        'alamat_kantor',
        'jam_layanan',
        'visi',
        'misi',
    ];

    public static function lurah(): self
    {
        $defaults = config('kelurahan.lurah', []);

        return static::firstOrCreate(
            ['role' => 'lurah'],
            [
                'jabatan' => $defaults['jabatan'] ?? 'Lurah Kelurahan Inauga',
                'nama' => $defaults['nama'] ?? null,
                'telepon' => $defaults['telepon'] ?? null,
                'whatsapp' => $defaults['whatsapp'] ?? null,
                'email' => $defaults['email'] ?? null,
                'alamat_kantor' => $defaults['alamat_kantor'] ?? null,
                'jam_layanan' => $defaults['jam_layanan'] ?? null,
                'visi' => $defaults['visi'] ?? null,
                'misi' => $defaults['misi'] ?? null,
            ]
        );
    }

    /** @return array<string, mixed> */
    public static function publicLurahArray(): array
    {
        $official = static::lurah();
        $defaults = config('kelurahan.lurah', []);

        $photo = $official->photoUrl();
        if (! $photo && filled($defaults['photo'] ?? null)) {
            $photo = asset($defaults['photo']);
        }

        return [
            'jabatan' => $official->jabatan ?: ($defaults['jabatan'] ?? null),
            'nama' => $official->nama ?: ($defaults['nama'] ?? null),
            'photo' => $photo,
            'telepon' => $official->telepon ?: ($defaults['telepon'] ?? null),
            'whatsapp' => $official->whatsapp ?: ($defaults['whatsapp'] ?? null),
            'email' => $official->email ?: ($defaults['email'] ?? null),
            'alamat_kantor' => $official->alamat_kantor ?: ($defaults['alamat_kantor'] ?? null),
            'jam_layanan' => $official->jam_layanan ?: ($defaults['jam_layanan'] ?? null),
            'visi' => $official->visi ?: ($defaults['visi'] ?? null),
            'misi' => $official->misi ?: ($defaults['misi'] ?? null),
        ];
    }

    public function hasUploadedPhoto(): bool
    {
        if (! $this->photo_path) {
            return false;
        }

        if (str_starts_with($this->photo_path, 'http')) {
            return true;
        }

        return Storage::disk('public')->exists($this->photo_path);
    }

    public function photoUrl(): ?string
    {
        if (! $this->hasUploadedPhoto()) {
            return null;
        }

        if (str_starts_with($this->photo_path, 'http')) {
            return $this->photo_path;
        }

        return Storage::disk('public')->url($this->photo_path);
    }
}
