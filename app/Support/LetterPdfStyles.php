<?php

namespace App\Support;

class LetterPdfStyles
{
    public static function css(): string
    {
        return <<<'CSS'
            @page{size:A4 portrait;margin:2cm 2.5cm}
            body{font-family:"Times New Roman",Times,serif;font-size:12pt;line-height:1.5;margin:0;color:#111}
            .kop{margin-bottom:4px}
            .kop-table{border-collapse:collapse;width:100%}
            .kop-logo{width:18%;vertical-align:middle;text-align:center}
            .kop-logo img{max-height:90px;max-width:90px}
            .kop-logo-placeholder{display:inline-block;width:90px;height:90px;border:1px dashed #888;background:#fafafa}
            .kop-text{text-align:center;vertical-align:middle;width:64%}
            .kop-line{margin:0;padding:0;font-size:12pt;line-height:1.35;font-weight:700}
            .kop-line--gov{font-size:12pt;font-weight:700;letter-spacing:.02em}
            .kop-line--kelurahan{font-size:12pt;font-weight:700;letter-spacing:.02em;margin-top:1px}
            .kop-spacer{width:18%}
            .kop-rule--double{margin:8px 0 14px}
            .kop-rule__thick{border-top:3px solid #111;height:0;margin:0}
            .kop-rule__thin{border-top:1px solid #111;height:0;margin:2px 0 0}
            .doc-title{text-align:center;font-size:12pt;font-weight:700;text-decoration:underline;margin:10px 0 8px;letter-spacing:.03em;text-transform:uppercase}
            .doc-number{text-align:center;margin:0 0 16px;font-size:12pt;font-weight:700}
            .doc-number-label{letter-spacing:.08em}
            .body{text-align:justify}
            .opening{margin:0 0 14px;text-indent:0;line-height:1.55}
            .field-table{width:100%;margin:4px 0 10px;border-collapse:collapse}
            .field-table td{padding:4px 0;vertical-align:top;font-size:12pt}
            .field-table--usaha{margin-top:8px}
            .field-table--purpose{margin-top:4px;margin-bottom:8px}
            .field-label{width:38%;white-space:nowrap}
            .field-label--spaced-nama{letter-spacing:.28em}
            .field-label--spaced-nik{letter-spacing:.35em}
            .field-label--spaced-pekerjaan{letter-spacing:.12em}
            .field-label--spaced-agama{letter-spacing:.22em}
            .field-label--spaced-alamat{letter-spacing:.18em}
            .field-sep{width:2%;padding-right:6px;text-align:center}
            .field-value{width:60%;border-bottom:1px dotted #333;padding-bottom:2px;min-height:1.25em}
            .field-value--purpose{white-space:pre-wrap;line-height:1.5}
            .field-value--line{border-bottom:1px dotted #333;height:1.5em}
            .purpose-extra-row td{padding-top:6px}
            .closing{margin:12px 0 0;line-height:1.55}
            .ttd{margin-top:24px;width:100%;border-collapse:collapse}
            .ttd-spacer{width:50%}
            .ttd-right{width:50%;text-align:center;vertical-align:top;font-size:12pt;overflow:visible}
            .ttd-right p{margin:2px 0;line-height:1.45}
            .ttd-place{margin-bottom:6px}
            .ttd-place-line{text-decoration:underline}
            .ttd-hormat{margin-top:4px}
            .ttd-role{margin-top:2px}
            .ttd-sign-block{width:100%;margin:12px auto 0;line-height:0;text-align:center}
            .ttd-sign-block--with-cap{margin-bottom:0}
            .ttd-sign-stack{position:relative;width:100%;height:2.9cm;margin:0 auto;overflow:visible}
            .ttd-sign-stack--with-cap{height:2.9cm;overflow:visible}
            .ttd-sign-stack .ttd-img{position:absolute;left:0;right:0;bottom:0;text-align:center;line-height:0;z-index:1}
            .ttd-sign-stack .ttd-img img{display:inline-block;max-width:95%;max-height:2.8cm;width:auto;height:auto;object-fit:contain}
            .ttd-sign-stack--with-cap .ttd-img img{max-width:98%;max-height:2.2cm}
            .ttd-sign-stack--with-cap .ttd-cap{position:absolute;top:0;left:50%;margin-left:6px;line-height:0;z-index:2}
            .ttd-sign-stack--with-cap .ttd-cap img{display:block;max-height:4.2cm;max-width:4.2cm;object-fit:contain}
            .ttd-sign-placeholder{display:block;width:100%;height:2.8cm}
            .ttd-sign-stack--with-cap .ttd-sign-placeholder{height:2.2cm}
            .ttd-nama-paren,.ttd-nama{margin-top:4px;font-size:12pt;font-family:"Times New Roman",Times,serif}
            .ttd-nama-paren{text-decoration:underline;text-transform:uppercase;letter-spacing:.04em}
            .ttd-sign-block--with-cap + .ttd-nama-paren,.ttd-sign-block--with-cap + .ttd-nama{margin-top:1cm;line-height:1.45}
CSS;
    }
}
