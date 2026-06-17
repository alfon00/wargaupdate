<?php

namespace App\Support;

class LetterPreviewHtml
{
    /**
     * Ambil isi body dari dokumen HTML penuh untuk pratinjau inline (tanpa tag style ganda).
     */
    public static function extractFragment(string $html): string
    {
        if ($html === '' || ! str_contains($html, '<')) {
            return '';
        }

        if (preg_match('/<body[^>]*>(.*)<\/body>/is', $html, $bodyMatch)) {
            $body = trim($bodyMatch[1]);
            if ($body !== '') {
                return $body;
            }
        }

        return preg_replace('/<style[^>]*>.*?<\/style>/is', '', $html) ?? $html;
    }

    /**
     * Ambil isi tag style dari dokumen HTML penuh (untuk pratinjau tab baru).
     */
    public static function extractStyles(string $html): string
    {
        if ($html === '' || ! str_contains($html, '<style')) {
            return '';
        }

        if (preg_match('/<style[^>]*>(.*?)<\/style>/is', $html, $styleMatch)) {
            return trim($styleMatch[1]);
        }

        return '';
    }

    public static function looksLikeLetterHtml(string $html): bool
    {
        if ($html === '' || ! str_contains($html, '<')) {
            return false;
        }

        if (str_contains($html, 'class="kop"') || str_contains($html, 'class="doc-title"')) {
            return true;
        }

        if (preg_match('/<body[^>]*>(.*)<\/body>/is', $html, $bodyMatch)) {
            return strlen(trim(strip_tags($bodyMatch[1]))) > 80;
        }

        return strlen(strip_tags($html)) > 80;
    }
}
