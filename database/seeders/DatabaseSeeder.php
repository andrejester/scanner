<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User Seeder
        $this->call(PermissionSeeder::class);
        $this->call(SettingSeeder::class);
        $this->call(UserSeeder::class);

        // Master Seeders
        $this->call(MasterBannerAdsSeeder::class);
        $this->call(MasterBannerSeeder::class);
        $this->call(MasterDownloadCategorySeeder::class);
        $this->call(MasterDownloadSeeder::class);
        $this->call(MasterFaqSeeder::class);
        $this->call(MasterFileScannerLogSeeder::class);
        $this->call(MasterInboxSeeder::class);
        $this->call(MasterLayananKamiSeeder::class);
        $this->call(MasterSambutanDirekturSeeder::class);
        $this->call(MasterPortofolioCategorySeeder::class);
        $this->call(MasterPortofolioSeeder::class);
        $this->call(MasterPostCategorySeeder::class);
        $this->call(MasterPostTagSeeder::class);
        $this->call(MasterPostSeeder::class);
        $this->call(MasterPostCommentSeeder::class);
        $this->call(MasterVideoCategorySeeder::class);
        $this->call(MasterVideoSeeder::class);
    }
}
