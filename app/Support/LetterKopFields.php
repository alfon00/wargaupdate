<?php

namespace App\Support;

use App\Models\Application;
use App\Models\RtProfile;

class LetterKopFields
{
    /**
     * @return array<string, string>
     */
    public static function forApplication(Application $application): array
    {
        $application->loadMissing(['resident.household.rtProfile', 'assignedRtProfile']);

        $rt = $application->resolvedRtProfile() ?? $application->resident?->household?->rtProfile;

        $rtNomor = RtProfile::normalizeRtNumber($rt?->rt_number);
        $rwNomor = static::normalizeRwNumber($rt?->rw_number);

        $kelurahan = $rt?->kelurahan ?? config('kelurahan.nama');
        $desa = static::desaUppercase($kelurahan);
        $kecamatan = $rt?->kecamatan ?? config('kelurahan.distrik');
        $kabupaten = $rt?->kota ?? config('kelurahan.kabupaten');
        $provinsi = $rt?->provinsi ?? config('kelurahan.provinsi');

        $dusun = trim((string) config('kelurahan.letter_kop.dusun', ''));
        $kodePos = trim((string) config('kelurahan.letter_kop.kode_pos', ''));
        $alamatKantor = $rt?->alamat_kantor
            ?: trim((string) config('kelurahan.letter_kop.alamat_kantor_default', ''));

        $dusunClause = $dusun !== '' ? ' Dusun '.$dusun : '';
        $tempatSurat = static::tempatTandaTangan($kelurahan);

        return [
            'logo_kop' => static::kopLogoImgTag(),
            'pemerintah_kabupaten' => static::pemerintahKabupatenLabel($kabupaten),
            'kecamatan_surat' => static::kecamatanLabel($kecamatan),
            'distrik_surat' => static::distrikSuratLabel($kecamatan),
            'kelurahan_surat' => static::kelurahanSuratLabel($kelurahan),
            'desa' => $desa,
            'kelurahan' => $kelurahan,
            'kabupaten' => $kabupaten,
            'provinsi' => $provinsi,
            'kecamatan' => $kecamatan,
            'alamat_kantor' => $alamatKantor,
            'alamat_kantor_kop' => static::alamatKopLine($alamatKantor, $desa, $kodePos),
            'rt_nomor' => $rtNomor,
            'rw_nomor' => $rwNomor,
            'rt' => $rt?->displayName() ?? 'RT '.$rtNomor,
            'rw' => $rt?->rw_number ?? $rwNomor,
            'dusun' => $dusun,
            'dusun_clause' => $dusunClause,
            'kode_pos' => $kodePos,
            'tempat_surat' => $tempatSurat,
            'tahun_surat' => now()->format('Y'),
            'tahun_surat_pendek' => now()->format('y'),
        ];
    }

    public static function nomorSuratBaris(string $nomorSurat, string $rtNomor, string $rwNomor): string
    {
        unset($rtNomor, $rwNomor);

        $segments = array_values(array_filter(explode('/', trim($nomorSurat)), fn (string $part) => $part !== ''));

        if (count($segments) >= 4) {
            return implode(' / ', array_slice($segments, 0, 4));
        }

        while (count($segments) < 4) {
            $segments[] = str_repeat('.', 8);
        }

        return implode(' / ', $segments);
    }

    public static function kopLogoPlaceholderTag(): string
    {
        return '<div class="kop-logo-placeholder" aria-hidden="true">&nbsp;</div>';
    }

    public static function kopLogoImgTag(): string
    {
        $path = config('kelurahan.letter_kop.logo', config('kelurahan.portal_logo'));

        if (! is_string($path) || $path === '') {
            return static::kopLogoPlaceholderTag();
        }

        $relativePath = ltrim($path, '/');
        $absolutePath = public_path($relativePath);

        if (is_file($absolutePath)) {
            return static::localImageToImgTag(
                $absolutePath,
                'Logo Kabupaten Mimika',
                'max-height:90px;max-width:90px',
            );
        }

        return static::kopLogoPlaceholderTag();
    }

    public static function localImageToImgTag(string $absolutePath, string $alt = '', string $style = ''): string
    {
        if (! is_file($absolutePath)) {
            return '';
        }

        $raw = file_get_contents($absolutePath);
        if ($raw === false || $raw === '') {
            return '';
        }

        $extension = strtolower(pathinfo($absolutePath, PATHINFO_EXTENSION));
        $mime = match ($extension) {
            'webp' => 'image/webp',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            default => 'image/png',
        };

        $dataUri = 'data:'.$mime.';base64,'.base64_encode($raw);
        $styleAttr = $style !== '' ? ' style="'.$style.'"' : '';

        return '<img src="'.$dataUri.'" alt="'.e($alt).'"'.$styleAttr.'>';
    }

    public static function normalizeRwNumber(?string $rwNumber): string
    {
        $digits = preg_replace('/\D/', '', $rwNumber ?? '') ?: '0';

        return str_pad($digits, 3, '0', STR_PAD_LEFT);
    }

    public static function desaUppercase(?string $kelurahan): string
    {
        $name = trim((string) $kelurahan);
        if ($name === '') {
            $name = (string) config('kelurahan.nama');
        }

        $name = preg_replace('/^kelurahan\s+/iu', '', $name) ?? $name;

        return mb_strtoupper($name, 'UTF-8');
    }

    public static function pemerintahKabupatenLabel(string $kabupaten): string
    {
        $label = trim($kabupaten);
        if ($label === '') {
            return 'PEMERINTAH '.mb_strtoupper((string) config('kelurahan.kabupaten'), 'UTF-8');
        }

        if (! preg_match('/^kabupaten\s+/iu', $label)) {
            $label = 'Kabupaten '.$label;
        }

        return 'PEMERINTAH '.mb_strtoupper($label, 'UTF-8');
    }

    public static function kecamatanLabel(string $kecamatan): string
    {
        return static::distrikSuratLabel($kecamatan);
    }

    public static function distrikSuratLabel(string $distrik): string
    {
        $label = trim($distrik);
        if ($label === '') {
            $label = (string) config('kelurahan.distrik', 'Distrik Wania');
        }

        $label = preg_replace('/^(kecamatan|distrik)\s+/iu', '', $label) ?? $label;

        return 'DISTRIK '.mb_strtoupper($label, 'UTF-8');
    }

    public static function kelurahanSuratLabel(?string $kelurahan): string
    {
        $name = preg_replace('/^kelurahan\s+/iu', '', trim((string) $kelurahan)) ?? trim((string) $kelurahan);
        if ($name === '') {
            $name = preg_replace('/^kelurahan\s+/iu', '', (string) config('kelurahan.nama')) ?? (string) config('kelurahan.nama');
        }

        return 'KELURAHAN '.mb_strtoupper($name, 'UTF-8');
    }

    public static function tempatTandaTangan(string $kelurahan): string
    {
        $name = preg_replace('/^kelurahan\s+/iu', '', trim($kelurahan)) ?? trim($kelurahan);

        return $name !== '' ? $name : (string) config('kelurahan.nama');
    }

    public static function alamatKopLine(string $alamatKantor, string $desa, string $kodePos): string
    {
        if ($alamatKantor !== '') {
            return $alamatKantor;
        }

        $parts = array_filter([
            $desa !== '' ? 'Desa '.$desa : null,
            $kodePos !== '' ? 'Kode Pos '.$kodePos : null,
        ]);

        return implode(' ', $parts);
    }
}
