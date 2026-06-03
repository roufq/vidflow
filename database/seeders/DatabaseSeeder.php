<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat Role
        $superAdminRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'super-admin']);
        $userRole = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'user']);

        // 2. Buat Akun Super Admin
        $admin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'admin@vidflow.id',
            'password' => bcrypt('password'),
            'plan' => 'business', // Admin bebas pakai fitur apa saja
        ]);
        $admin->assignRole($superAdminRole);

        // 3. Buat Akun User - Paket FREE
        $freeUser = User::factory()->create([
            'name' => 'John (Free)',
            'email' => 'free@vidflow.id',
            'password' => bcrypt('password'),
            'plan' => 'free',
        ]);
        $freeUser->assignRole($userRole);

        // 4. Buat Akun User - Paket PRO
        $proUser = User::factory()->create([
            'name' => 'Jane (Pro)',
            'email' => 'pro@vidflow.id',
            'password' => bcrypt('password'),
            'plan' => 'pro',
        ]);
        $proUser->assignRole($userRole);

        // 5. Buat Akun User - Paket BUSINESS
        $businessUser = User::factory()->create([
            'name' => 'Corp (Business)',
            'email' => 'business@vidflow.id',
            'password' => bcrypt('password'),
            'plan' => 'business',
        ]);
        $businessUser->assignRole($userRole);
    }
}
