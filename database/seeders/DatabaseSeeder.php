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
        User::create([
            'name' => 'Owner Ngacoan',
            'username' => 'ownerngacoan',
            'email' => 'ownerngacoan@gmail.com',
            'password' => Hash::make('owner1234'),
            'role' => 'owner',
        ]);

        User::create([
            'name' => 'Pengelola Ngacoan',
            'username' => 'pengelolangacoan',
            'email' => 'pengelolangacoan@gmail.com',
            'password' => Hash::make('pengelola1234'),
            'role' => 'pengelola',
        ]);


    }
}
