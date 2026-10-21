<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\UserRole;
use App\Models\RunningYear;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database idempotently.
     */
    public function run(): void
    {
        // 1. Seed or update Default Admin User
        $adminEmail = 'emdart.dev2000@gmail.com';
        $user = User::where('email', $adminEmail)->first();

        if (!$user) {
            $user = User::create([
                'id' => (string) Str::uuid(),
                'name' => 'Emdad',
                'email' => $adminEmail,
                'password' => Hash::make('12345678'),
            ]);

            Profile::create([
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'display_name' => 'Emdad',
            ]);

            UserRole::create([
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'role' => 'admin',
            ]);

            $this->command->info("Admin user created: {$adminEmail}");
        } else {
            $user->update([
                'name' => 'Emdad',
            ]);

            Profile::updateOrCreate(
                ['user_id' => $user->id],
                ['display_name' => 'Emdad']
            );

            UserRole::firstOrCreate(
                ['user_id' => $user->id, 'role' => 'admin']
            );

            $this->command->info("Admin user verified: {$adminEmail}");
        }

        // 2. Seed Running Years if empty
        if (DB::table('running_years')->count() === 0) {
            DB::table('running_years')->insert([
                [
                    'id' => (string) Str::uuid(),
                    'year_label' => '2026',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'id' => (string) Str::uuid(),
                    'year_label' => '2025',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            ]);
            $this->command->info("Running years (2026, 2025) seeded.");
        }

        // 3. Seed Workers with Bengali & English names if empty
        if (DB::table('workers')->count() === 0) {
            $workers = [
                ['name_bn' => 'কবির', 'name_en' => 'Kabir'],
                ['name_bn' => 'মাসুদ', 'name_en' => 'Masud'],
                ['name_bn' => 'সেন্টু + কবির', 'name_en' => 'Sentu + Kabir'],
                ['name_bn' => 'সোহান + ইউনুস', 'name_en' => 'Sohan + Yunus'],
                ['name_bn' => 'জোবায়ের', 'name_en' => 'Zobayer'],
                ['name_bn' => 'সেন্টু', 'name_en' => 'Sentu'],
                ['name_bn' => 'আকাশ', 'name_en' => 'Akash'],
                ['name_bn' => 'ছানা + সালাম', 'name_en' => 'Chana + Salam'],
                ['name_bn' => 'রায়হান', 'name_en' => 'Rayhan'],
                ['name_bn' => 'শহিদুল', 'name_en' => 'Shahidul'],
                ['name_bn' => 'কাদের', 'name_en' => 'Qader'],
                ['name_bn' => 'ওয়াসিম', 'name_en' => 'Wasim'],
                ['name_bn' => 'ইউনুস', 'name_en' => 'Yunus'],
                ['name_bn' => 'আসাদুল ময়মনসিংহ', 'name_en' => 'Asadul Mymensingh'],
                ['name_bn' => 'বিক্রম', 'name_en' => 'Bikrom'],
                ['name_bn' => 'রানা', 'name_en' => 'Rana'],
                ['name_bn' => 'ইলিয়াস', 'name_en' => 'Ilias'],
                ['name_bn' => 'জুয়েল', 'name_en' => 'Jewel'],
                ['name_bn' => 'সাঈদ', 'name_en' => 'Sayed'],
                ['name_bn' => 'আলহাজ', 'name_en' => 'Alhaj'],
                ['name_bn' => 'আশরাফুল', 'name_en' => 'Ashraful'],
                ['name_bn' => 'রবিউল শ্যার্ট', 'name_en' => 'Rabiul Short'],
                ['name_bn' => 'কাঞ্চন', 'name_en' => 'Kanchan'],
                ['name_bn' => 'সাইফুল', 'name_en' => 'Saiful'],
                ['name_bn' => 'সোহান', 'name_en' => 'Sohan'],
                ['name_bn' => 'শিমুল', 'name_en' => 'Shimul'],
                ['name_bn' => 'আসাদুল রংপুর', 'name_en' => 'Asadul Rangpur'],
                ['name_bn' => 'মিন্টু', 'name_en' => 'Mintu'],
                ['name_bn' => 'রবিউল বর প্যান্ট', 'name_en' => 'Rabiul Boro Pant'],
            ];

            foreach ($workers as $w) {
                DB::table('workers')->insert([
                    'id' => (string) Str::uuid(),
                    'name_bn' => $w['name_bn'],
                    'name_en' => $w['name_en'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $this->command->info(count($workers) . " workers seeded with bilingual names.");
        } else {
            $this->command->info(DB::table('workers')->count() . " workers already present.");
        }

        $this->command->info("TailorPro database seeding completed successfully.");
    }
}
