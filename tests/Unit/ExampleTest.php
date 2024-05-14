<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_just_show_testenv(): void
    {
        $this->assertTrue(true);
        dump('APP_ENV : '.env('APP_ENV'));
        dump('DB_CONNECTION : ' . env('DB_CONNECTION'));
        dump('DB_DATABASE : '. env('DB_DATABASE'));
    }
}
