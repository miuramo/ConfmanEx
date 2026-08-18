<?php

namespace Database\Factories;

use App\Models\Certificate;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Certificate>
 */
class CertificateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'orderint' => 1,
            'winner' => '',
            'awardname' => '優秀論文賞',
            'year' => Setting::getval('CONFTITLE_YEAR'),
            'eventname' => '情報教育シンポジウム' . Setting::getval('CONFTITLE_YEAR'),
            'creator' => '@IPSJ',
            'company' => '情報処理学会',
            'date' => '令和８年８月２１日',
            'content' => '貴殿が[:company:][:eventname:]にて発表された「[:title:]」は特に優秀な論文であり、情報教育の発展に貢献することを認めここに表彰いたします。',
            'presenter' => "[:date:]\r\n一般社団法人 [:company:]\r\n[:eventname:]\r\nプログラム委員長XX XX\r\n実行委員長YY YY\r\n大会委員長ZZ ZZ",
            'template' => 'default',
        ];
    }
}
