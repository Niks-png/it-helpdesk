<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Demo accounts for trying the app.
     * shortcut: hardcoded test users, replace with real municipal accounts (e.g. Active Directory) before real use.
     */
    public function run(): void
    {
        User::updateOrCreate(['email' => 'darbinieks@test.local'], [
            'name' => 'Demo Darbinieks',
            'password' => 'darbinieks123',
            'role' => 'employee',
        ]);

        User::updateOrCreate(['email' => 'admin@test.local'], [
            'name' => 'Demo IT administrators',
            'password' => 'admin123',
            'role' => 'admin',
        ]);
    }
}
