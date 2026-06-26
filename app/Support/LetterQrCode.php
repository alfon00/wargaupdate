<?php

namespace App\Support;

use App\Models\Application;
use chillerlan\QRCode\Output\QROutputInterface;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class LetterQrCode
{
    public static function verificationUrl(Application $application, string $verificationToken): ?string
    {
        $token = trim($verificationToken);
        if ($token === '') {
            return null;
        }

        return LetterVerificationLink::urlForToken($token);
    }

    public static function imgTag(string $verificationUrl): string
    {
        if ($verificationUrl === '') {
            return '<span class="ttd-qrcode-placeholder">&nbsp;</span>';
        }

        try {
            $options = new QROptions([
                'outputType' => QROutputInterface::GDIMAGE_PNG,
                'outputBase64' => true,
                'scale' => 5,
                'quietzoneSize' => 2,
            ]);
            $dataUri = (new QRCode($options))->render($verificationUrl);
        } catch (\Throwable) {
            return '<span class="ttd-qrcode-placeholder">&nbsp;</span>';
        }

        return '<img src="'.$dataUri.'" alt="QR code verifikasi surat" class="ttd-qrcode-img">';
    }

    public static function block(Application $application, string $verificationToken): string
    {
        $url = self::verificationUrl($application, $verificationToken);
        $img = self::imgTag($url ?? '');

        return '<div class="ttd-qrcode-block">'
            .$img
            .'<p class="ttd-qrcode-caption">Verifikasi keaslian surat</p>'
            .'</div>';
    }
}
