<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = [
            [
                'name' => 'Admin User',
                'firstname' => 'Administrator',
                'lastname' => 'Admin',
                'phone' => '000',
                'bio' => 'Super Admin',
                'email' => 'admin@test.com',
                'password' => Hash::make('111'),
                'role'  => 'Admin',
                'photo' => 'https://loremflickr.com/150/150?random=15',
                'google2fa_secret' => '46YHPIMGZGTNDSGF',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Tambahkan lebih banyak pengguna jika diperlukan
        ];

        foreach ($users as $user) {
            DB::table('users')->updateOrInsert(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
