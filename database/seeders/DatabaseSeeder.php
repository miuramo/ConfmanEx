<?php

namespace Database\Seeders;

use App\Models\Bidding;
use App\Models\Contact;
use App\Models\Paper;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (\App\Models\User::count() == 0) {
            \App\Models\User::factory()->create([
                'name' => env('INITIAL_NAME'),   //'First User',
                'email' => env('INITIAL_EMAIL'), //'firstuser@example.com',
                'affil' => env('INITIAL_AFFIL'), //'Example',
                'password' => Hash::make(env('INITIAL_PASSWORD')),
            ]);
        }
        if (\App\Models\Role::count() == 0) {
            foreach (\App\Models\Role::$roles as $name => $desc) {
                $tmp = \App\Models\Role::create([
                    'name' => $name,
                    'desc' => $desc,
                    'abbr' => $name,
                    'navi' => \App\Models\Role::$role_navi[$name] ?? '',
                    'orderint' => ($name == 'admin') ? 900 : 10,
                ]);
                $tmp->users()->syncWithoutDetaching(1);
            }
        }
        $u1 = \App\Models\User::find(1);
        if ($u1->name == null && $u1->email == null) {
            $u1->name = "First User";
            $u1->email = "firstuser@example.com";
            $u1->affil = "Example";
            $u1->save();
        }

        $this->call([
            EnqueteSeeder::class,
            EnqueteConfigSeeder::class,
            EnqueteItemSeeder::class,
            BiddingSeeder::class,
            ViewpointSeeder::class,
            CategorySeeder::class,
            ConfirmSeeder::class,
            AcceptSeeder::class,
            SettingSeeder::class,
            MailTemplateSeeder::class,
            EventSeeder::class,
            EventConfigSeeder::class,
            ValidationSeeder::class,
        ]);
    }
}
