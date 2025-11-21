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
        // 🔵 الطريقة المفضلة: إنشاء أو تحديث admin
        $admin = User::updateOrCreate(
            ['username' => 'admin'], // الشرط (مفتاح البحث)

            // البيانات التي سيتم إنشاؤها أو تحديثها
            [
                'name' => 'مدير النظام',
                'password' => Hash::make('123456'),
            ]
        );

        // 🎯 طباعة توضيحية في الكونسول
        if ($admin->wasRecentlyCreated) {
            $this->command->info('✅ تم إنشاء مستخدم المدير الافتراضي بنجاح.');
        } else {
            $this->command->warn('ℹ️ مستخدم المدير موجود مسبقًا — تم تحديث بياناته.');
        }

        $this->command->line("👤 اسم المستخدم: admin");
        $this->command->line("🔑 كلمة المرور: 123456\n");
    }
}
