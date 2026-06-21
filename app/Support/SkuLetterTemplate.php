<?php

namespace App\Support;

class SkuLetterTemplate
{
    public static function bodyHtml(): string
    {
        return <<<'HTML'
<div class="kop">
    <h3>SURAT KETERANGAN USAHA</h3>
    <p><strong>Pengantar Rukun Tetangga</strong></p>
    <p>{{rt}} · {{kelurahan}}</p>
    <p>Nomor: {{nomor_surat}}</p>
</div>
<div class="body">
    <p>Yang bertanda tangan di bawah ini, Ketua {{rt}}, {{kelurahan}}, {{distrik}}, {{kabupaten}}, {{provinsi}}, dengan ini menerangkan bahwa:</p>
    <table class="data-table">
        <tr><td width="32%">Nama</td><td>: {{nama}}</td></tr>
        <tr><td>NIK</td><td>: {{nik}}</td></tr>
        <tr><td>Tempat/Tgl Lahir</td><td>: {{ttl}}</td></tr>
        <tr><td>Jenis Kelamin</td><td>: {{jenis_kelamin}}</td></tr>
        <tr><td>Pekerjaan</td><td>: {{pekerjaan}}</td></tr>
        <tr><td>Alamat</td><td>: {{alamat}}</td></tr>
    </table>
    <p>Benar-benar memiliki usaha:</p>
    <table class="data-table">
        <tr><td width="32%">Nama Usaha</td><td>: {{nama_usaha}}</td></tr>
        <tr><td>Jenis Usaha</td><td>: {{jenis_usaha}}</td></tr>
        <tr><td>Alamat Usaha</td><td>: {{alamat_usaha}}</td></tr>
    </table>
    <p>Usaha tersebut berdomisili di wilayah {{rt}}, {{kelurahan}}, dan surat ini dibuat untuk keperluan: <strong>{{keperluan}}</strong>.</p>
    <p>Demikian surat keterangan ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya.</p>
</div>
<p class="tempat-tanggal">{{kelurahan}}, {{tanggal}}</p>
<table class="ttd">
    <tr>
        <td width="50%"></td>
        <td width="50%" class="ttd-right">
            <p>Ketua {{rt}},</p>
            <div class="ttd-sign-block">
                <div class="ttd-img">{{ttd_gambar}}</div>
            </div>
            <p class="ttd-nama"><strong>{{ketua_rt}}</strong></p>
        </td>
    </tr>
</table>
HTML;
    }
}
