<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ✅ إنشاء مستخدم Admin افتراضي (إن لم يكن موجودًا)
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'مدير النظام',
                'password' => Hash::make('123456'),
            ]
        );

        // ✅ طباعة توضيحية في الكونسول (لتأكيد إنشاء المستخدم)
        if ($admin->wasRecentlyCreated) {
            $this->command->info('✅ تم إنشاء مستخدم المدير الافتراضي بنجاح:');
        } else {
            $this->command->warn('ℹ️ مستخدم المدير موجود مسبقًا، لم يتم إنشاؤه من جديد.');
        }

        $this->command->line("👤 اسم المستخدم: admin");
        $this->command->line("🔑 كلمة المرور: 123456\n");

        // يمكنك إضافة مستخدمين آخرين للتجربة لاحقًا إن رغبت
        // User::factory()->create([
        //     'name' => 'مستخدم تجريبي',
        //     'username' => 'testuser',
        //     'password' => Hash::make('password'),
        // ]);
    }
}
