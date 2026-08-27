<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
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
        User::query()->updateOrCreate([
            'email' => env('ADMIN_EMAIL', 'admin@mytask.local'),
        ], [
            'nickname' => env('ADMIN_NICKNAME', 'Administrador'),
            'password' => env('ADMIN_PASSWORD', 'change-me'),
            'role' => RoleEnum::Admin,
        ]);

        User::factory()->user()->count(5)->create();
    }
}
