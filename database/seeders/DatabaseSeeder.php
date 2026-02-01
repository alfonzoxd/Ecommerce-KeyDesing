<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Alfonzo',
            'last_name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('hola2005'),
            'role' => 'admin',
            'phone' => '999999999'
        ]);

    }
}
