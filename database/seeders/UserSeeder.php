<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder {
    public function run(): void {
        User::create([
            'name' => 'Developer User',
            'email' => 'developer@test.com',
            'password' => Hash::make('password'),
            'role' => 'developer'
        ]);

        User::create([
            'name' => 'Kantor User',
            'email' => 'kantor@test.com',
            'password' => Hash::make('password'),
            'role' => 'kantor'
        ]);

        User::create([
            'name' => 'Pabrik User',
            'email' => 'pabrik@test.com',
            'password' => Hash::make('password'),
            'role' => 'pabrik'
        ]);

        User::create([
            'name' => 'Owner User',
            'email' => 'owner@test.com',
            'password' => Hash::make('password'),
            'role' => 'owner'
        ]);
    }
}

