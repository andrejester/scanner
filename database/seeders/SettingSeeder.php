<?php

namespace Database\Seeders;

use App\Models\System\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Setting::create([
            'company_name' => env('APP_NAME'),
            'company_slug' => Str::slug(
                env('APP_NAME')
            ),
            'company_email' => 'info@perusahaancontoh.com',
            'company_phone' => '08123456789',
            'company_address' => 'Jl. Contoh No. 123, Kota Contoh',
            'company_maps' => 'https://goo.gl/maps/xyz123',
            'company_photo' => 'https://via.placeholder.com/150',
            'company_whatsapp' => '08123456789',
            'company_website' => 'https://www.perusahaancontoh.com',
            'company_summary' => 'Perusahaan Contoh adalah perusahaan yang bergerak di bidang contoh.',
            'company_deskripsi' => 'Perusahaan Contoh menyediakan berbagai layanan dan produk yang berkualitas untuk memenuhi kebutuhan pelanggan.',
            'company_visi' => 'Visi',
            'company_misi' => 'Misi',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
