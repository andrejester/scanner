<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions from menu
        $menuPermissions = menu();
        $allPermissions = [];

        foreach ($menuPermissions as $key => $value) {
            foreach ($value as $key1 => $value1) {
                $keyData = strtolower($key1);
                foreach ($value1 as $value2) {
                    $permissionName = $keyData . "_" . strtolower($value2);
                    $allPermissions[] = $permissionName;

                    // Check if permission already exists before creating
                    if (!Permission::where('name', $permissionName)->exists()) {
                        Permission::create(['name' => $permissionName]);
                    }
                }
            }
        }

        // Create roles
        $this->createRoles($allPermissions);

        // Create default users
        $this->createDefaultUsers();
    }

    /**
     * Create roles with permissions
     */
    private function createRoles(array $allPermissions): void
    {
        // Super Admin Role - Full access to everything including role management
        if (!Role::where('name', 'Super Admin')->exists()) {
            $superAdminRole = Role::create(['name' => 'Super Admin']);
            $superAdminRole->givePermissionTo(Permission::all());
        } else {
            $superAdminRole = Role::where('name', 'Super Admin')->first();
            $superAdminRole->syncPermissions(Permission::all());
        }

        // Admin Role - Full access except role/permission management
        if (!Role::where('name', 'Admin')->exists()) {
            $adminRole = Role::create(['name' => 'Admin']);
            // Get all permissions except role and permission management
            $adminPermissions = Permission::whereIn(
                'name',
                array_filter($allPermissions, function ($perm) {
                    return !str_contains($perm, 'role_') && !str_contains($perm, 'permission_');
                })
            )->get();
            $adminRole->givePermissionTo($adminPermissions);
        } else {
            $adminRole = Role::where('name', 'Admin')->first();
            $adminPermissions = Permission::whereIn(
                'name',
                array_filter($allPermissions, function ($perm) {
                    return !str_contains($perm, 'role_') && !str_contains($perm, 'permission_');
                })
            )->get();
            $adminRole->syncPermissions($adminPermissions);
        }

        // Guest Role - Read-only access
        if (!Role::where('name', 'Guest')->exists()) {
            $guestRole = Role::create(['name' => 'Guest']);
            // Only view/read permissions
            $guestPermissions = Permission::whereIn(
                'name',
                array_filter($allPermissions, function ($perm) {
                    return str_contains($perm, '_view') ||
                        str_contains($perm, '_read') ||
                        str_contains($perm, '_list') ||
                        str_contains($perm, '_show');
                })
            )->get();
            $guestRole->givePermissionTo($guestPermissions);
        } else {
            $guestRole = Role::where('name', 'Guest')->first();
            $guestPermissions = Permission::whereIn(
                'name',
                array_filter($allPermissions, function ($perm) {
                    return str_contains($perm, '_view') ||
                        str_contains($perm, '_read') ||
                        str_contains($perm, '_list') ||
                        str_contains($perm, '_show');
                })
            )->get();
            $guestRole->syncPermissions($guestPermissions);
        }
    }

    /**
     * Create default users for each role
     */
    private function createDefaultUsers(): void
    {
        $defaultPassword = '123456789';

        // Super Admin User
        $superAdminEmail = 'superadmin@test.com';
        if (!User::where('email', $superAdminEmail)->exists()) {
            $superAdmin = User::create([
                'name' => 'Super Admin',
                'email' => $superAdminEmail,
                'password' => Hash::make($defaultPassword),
                'email_verified_at' => now(),
            ]);
            $superAdmin->assignRole('Super Admin');
        }

        // Admin User
        $adminEmail = 'admin@test.com';
        if (!User::where('email', $adminEmail)->exists()) {
            $admin = User::create([
                'name' => 'Admin',
                'email' => $adminEmail,
                'password' => Hash::make($defaultPassword),
                'email_verified_at' => now(),
            ]);
            $admin->assignRole('Admin');
        }

        // Guest User
        $guestEmail = 'guest@test.com';
        if (!User::where('email', $guestEmail)->exists()) {
            $guest = User::create([
                'name' => 'Guest',
                'email' => $guestEmail,
                'password' => Hash::make($defaultPassword),
                'email_verified_at' => now(),
            ]);
            $guest->assignRole('Guest');
        }
    }
}
