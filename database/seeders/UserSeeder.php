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
            ['email' => 'direksi@hera.ac.id'],
            [
                'name'     => 'Direksi HERA',
                'email'    => 'direksi@hera.ac.id',
                'password' => Hash::make('hera12345'),
                'role'     => 'direksi',
            ]
        );

        // Akun Petugas (Operator)
        User::updateOrCreate(
            ['email' => 'petugas@hera.ac.id'],
            [
                'name'     => 'Petugas Monitoring',
                'email'    => 'petugas@hera.ac.id',
                'password' => Hash::make('hera12345'),
                'role'     => 'petugas',
            ]
        );
    }
}
