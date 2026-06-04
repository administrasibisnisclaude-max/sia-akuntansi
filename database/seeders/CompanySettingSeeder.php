<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CompanySettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'company_name'    => 'PT. SIA Akuntansi',
            'company_tagline' => 'Sistem Informasi Akuntansi',
            'company_address' => 'Jl. Contoh No. 1, Jakarta',
            'company_city'    => 'Jakarta',
            'company_phone'   => '021-12345678',
            'company_email'   => 'info@siaakuntansi.com',
            'company_website' => 'www.siaakuntansi.com',
            'company_npwp'    => '',
            'company_logo'    => '',
            'invoice_footer'  => 'Terima kasih atas kepercayaan Anda.',
            'invoice_terms'   => 'Pembayaran jatuh tempo sesuai tanggal yang tertera.',
        ];

        foreach ($settings as $key => $value) {
            DB::table('company_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now(), 'created_at' => now()]
            );
        }
    }
}
