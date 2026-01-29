<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ValidationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Validation::factory()->create([
            'event_id' => 1,
            'orderint' => 1,
            'name' => '区分・他学会・学生確認',
            'script' => "\$res[] = \$this->chk_kubun(\$ary);
\$res[] = \$this->chk_othergakkai(\$ary);
\$res[] = \$this->chk_student(\$ary);
// foreach(\$this->enq_key_value() as \$k=>\$v){ \$res[] = \$k.\": \".\$v; }",
            'closure' => "\$closure = function(\$ary) { };
\$res[] = \$closure(\$ary);",
        ]);
    }
}
