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
        // ✅ إنشاء مستخدم Admin افتراضي (إن لم يكن موجودًا)
        User::firstOrCreate(
            ['username' => 'admin'], // يعتمد على عمود "username"
            [
                'password' => Hash::make('123456'),
            ]
        );

        // (اختياري) يمكنك إضافة مستخدم تجريبي آخر
        // User::factory()->create([
        //     'username' => 'testuser',
        //     'password' => Hash::make('password'),
        // ]);
    }
}
