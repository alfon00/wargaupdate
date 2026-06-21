<?php

namespace App\Support;

class SuratPengantarTemplate
{
    public static function bodyHtml(): string
    {
        return static::kopBlock()
            .static::titleBlock()
            .static::openingBlock()
            .static::dataFieldsBlock()
            .static::purposeBlock()
            .static::closingBlock()
            .static::ttdBlock();
    }

    public static function bodyForServiceCode(string $code): string
    {
        unset($code);

        return static::bodyHtml();
    }

    public static function kopBlock(): string
    {
        return <<<'HTML'
<div class="kop">
    <table class="kop-table" width="100%">
        <tr>
            <td class="kop-logo">{{logo_kop}}</td>
            <td class="kop-text">
                <p class="kop-line kop-line--gov">{{pemerintah_kabupaten}}</p>
                <p class="kop-line kop-line--gov">{{distrik_surat}}</p>
                <p class="kop-line kop-line--kelurahan">{{kelurahan_surat}}</p>
            </td>
            <td class="kop-spacer"></td>
        </tr>
    </table>
    <div class="kop-rule kop-rule--double">
        <div class="kop-rule__thick"></div>
        <div class="kop-rule__thin"></div>
    </div>
</div>
HTML;
    }

    public static function titleBlock(): string
    {
        return <<<'HTML'
<h1 class="doc-title">SURAT PENGANTAR RUKUN TETANGGA RT {{rt_nomor}}</h1>
<p class="doc-number"><span class="doc-number-label">Nomor</span> : {{nomor_surat_baris}}</p>
HTML;
    }

    public static function openingBlock(): string
    {
        return <<<'HTML'
<div class="body">
    <p class="opening">Saya yang bertandatangan di bawah ini atas nama Ketua RT {{rt_nomor}} Kelurahan {{tempat_surat}} Distrik Wania Kabupaten Mimika, menerangkan bahwa :</p>
HTML;
    }

    public static function dataFieldsBlock(): string
    {
        return <<<'HTML'
    <table class="field-table">
        <tr><td class="field-label field-label--spaced-nama">N a m a</td><td class="field-sep">:</td><td class="field-value">{{nama}}</td></tr>
        <tr><td class="field-label field-label--spaced-nik">N I K</td><td class="field-sep">:</td><td class="field-value">{{nik}}</td></tr>
        <tr><td class="field-label">Tempat Tanggal Lahir</td><td class="field-sep">:</td><td class="field-value">{{ttl}}</td></tr>
        <tr><td class="field-label field-label--spaced-pekerjaan">P e k e r j a a n</td><td class="field-sep">:</td><td class="field-value">{{pekerjaan}}</td></tr>
        <tr><td class="field-label field-label--spaced-agama">A g a m a</td><td class="field-sep">:</td><td class="field-value">{{agama}}</td></tr>
        <tr><td class="field-label">Status Pernikahan</td><td class="field-sep">:</td><td class="field-value">{{status_perkawinan}}</td></tr>
        <tr><td class="field-label">Kewarga Negaraan</td><td class="field-sep">:</td><td class="field-value">{{kewarganegaraan}}</td></tr>
        <tr><td class="field-label field-label--spaced-alamat">A l a m a t</td><td class="field-sep">:</td><td class="field-value">{{alamat}}</td></tr>
    </table>
HTML;
    }

    public static function purposeBlock(): string
    {
        return <<<'HTML'
    <table class="field-table field-table--purpose">
        <tr>
            <td class="field-label">Maksud dan Keperluan</td>
            <td class="field-sep">:</td>
            <td class="field-value field-value--purpose">{{keperluan}}</td>
        </tr>
    </table>
HTML;
    }

    public static function closingBlock(?string $text = null): string
    {
        $closing = $text ?? 'Demikian Surat Pengantar ini kami berikan guna proses ketingkat selanjutnya.';

        return <<<HTML
    <p class="closing">{$closing}</p>
</div>
HTML;
    }

    public static function ttdBlock(): string
    {
        return <<<'HTML'
<table class="ttd" width="100%">
    <tr>
        <td class="ttd-spacer"></td>
        <td class="ttd-right">
            <p class="ttd-place"><span class="ttd-place-line">{{tempat_surat}}, {{tanggal}}</span></p>
            <p class="ttd-hormat">Hormat kami,</p>
            <p class="ttd-role">Pengurus RT {{rt_nomor}}</p>
            {{ttd_tanda_cap}}
            <p class="ttd-nama-paren">( {{ketua_rt}} )</p>
        </td>
    </tr>
</table>
HTML;
    }
}
