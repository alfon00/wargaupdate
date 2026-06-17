<?php

namespace Tests\Unit;

use App\Support\LetterKopFields;
use Tests\TestCase;

class LetterKopFieldsTest extends TestCase
{
    public function test_nomor_surat_baris_uses_four_segment_format(): void
    {
        $line = LetterKopFields::nomorSuratBaris('RT008/06/2026/0001', '008', '005');

        $this->assertSame('RT008/06/2026/0001', $line);
    }

    public function test_distrik_and_kelurahan_labels(): void
    {
        $this->assertSame('DISTRIK WANIA', LetterKopFields::distrikSuratLabel('Distrik Wania'));
        $this->assertSame('KELURAHAN INAUGA', LetterKopFields::kelurahanSuratLabel('Kelurahan Inauga'));
    }

    public function test_kop_logo_img_tag_embeds_local_file_as_base64(): void
    {
        $logoPath = public_path('images/brand/logo-kabupaten-mimika.png');

        $this->assertFileExists($logoPath);

        $tag = LetterKopFields::kopLogoImgTag();

        $this->assertStringContainsString('data:image/png;base64,', $tag);
        $this->assertStringContainsString('<img src="data:image/png;base64,', $tag);
        $this->assertStringNotContainsString('http://', $tag);
        $this->assertStringNotContainsString('https://', $tag);
    }
}
