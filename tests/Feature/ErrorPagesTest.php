<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    public function test_custom_404_page_uses_themed_layout(): void
    {
        $response = $this->get('/halaman-yang-pasti-tidak-ada-404-test');

        $response->assertStatus(404);
        $response->assertSee('Status 404');
        $response->assertSee('Halaman Tidak Ditemukan');
        $response->assertSee('Kembali ke Beranda');
        $response->assertSee('/system-errors/error.css?v=20260401', false);
        $response->assertSee('Fallback statis aktif');
    }

    public function test_custom_500_page_renders_expected_copy(): void
    {
        $response = $this->get('/__error-preview/500');

        $response->assertStatus(500);
        $response->assertSee('Status 500');
        $response->assertSee('Gangguan Server');
        $response->assertSee('Fallback statis aktif');
    }

    public function test_custom_501_page_renders_expected_copy(): void
    {
        $response = $this->get('/__error-preview/501');

        $response->assertStatus(501);
        $response->assertSee('Status 501');
        $response->assertSee('Fitur Belum Tersedia');
        $response->assertSee('Fallback statis aktif');
    }

    public function test_custom_503_page_renders_expected_copy(): void
    {
        $response = $this->get('/__error-preview/503');

        $response->assertStatus(503);
        $response->assertSee('Status 503');
        $response->assertSee('Layanan Sedang Pemeliharaan');
        $response->assertSee('Fallback statis aktif');
    }
}
