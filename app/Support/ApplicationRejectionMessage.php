<?php

namespace App\Support;

use App\Models\Application;

class ApplicationRejectionMessage
{
    public static function template(Application $application): string
    {
        $template = config('kelurahan.wa_permohonan_reject_delete');

        if (filled($template)) {
            return str_replace(
                ['{nama}', '{nik}', '{portal}'],
                [
                    $application->applicantName(),
                    $application->applicantNik() ?? '—',
                    config('kelurahan.portal_nama'),
                ],
                $template
            );
        }

        return 'Mohon maaf kepada warga dengan identitas'
            ."\nNama: {$application->applicantName()}"
            ."\nNIK: ".($application->applicantNik() ?? '—')
            ."\n\nPermohonan surat Anda tidak dapat diproses. Silakan ajukan ulang melalui portal "
            .config('kelurahan.portal_nama').'.';
    }
}
