<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Akun Direksi (Super Admin)
        User::updateOrCreate(
            ['email' => env('ADMIN_EMAIL', 'direksi@hera.ac.id')],
            [
                'name'     => 'Direksi HERA',
                'email'    => env('ADMIN_EMAIL', 'direksi@hera.ac.id'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'test12345')),
                'role'     => 'direksi',
            ]
        );

        // Akun Petugas (Operator)
        User::updateOrCreate(
            ['email' => 'petugas@hera.ac.id'],
            [
                'name'     => 'Petugas Monitoring',
                'email'    => 'petugas@hera.ac.id',
                'password' => Hash::make('test12345'),
                'role'     => 'petugas',
            ]
        );
    }
}
