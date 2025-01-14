<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $confname = env('CONFNAME', "SSS");
        $confyear = env('CONFYEAR', date('Y'));
        $mailfrom = "ssstoukou@istlab.info"; // "toukouadmin@interaction-ipsj.org"
        Setting::factory()->create([
            'name' => "CONFTITLE",
            'value' => $confname . $confyear,
        ]);
        Setting::factory()->create([
            'name' => "CONFTITLE_BASE",
            'value' => $confname,
        ]);
        Setting::factory()->create([
            'name' => "CONFTITLE_YEAR",
            'value' => $confyear,
        ]);
        Setting::factory()->create([
            'name' => "MAILFROM",
            'value' => $mailfrom,
        ]);
        Setting::factory()->create([
            'name' => "CONF_URL",
            'value' => "https://".strtolower($confname).$confyear.".istlab.info/",
        ]);
        Setting::factory()->create([
            'name' => "PSEUDOTESTSITE",
            'value' => "false",
            'isnumber' => false,
            'isbool' => true,
        ]);
        Setting::factory()->create([
            'name' => "CONFMAN_EX",
            'value' => "true",
            'isnumber' => false,
            'isbool' => true,
        ]);
        Setting::factory()->create([
            'name' => "FILEPUT_DIR",
            'value' => "z" . $confyear,
            'isnumber' => false,
            'isbool' => false,
        ]);
    }
}
