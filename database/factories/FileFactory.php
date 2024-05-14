<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Storage;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $path = 'public/files';
        // ディレクトリがなければ作成する
        if (!Storage::exists($path)) {
            Storage::makeDirectory($path);
        }
        return [
            'fname' => $this->faker->image(storage_path('app/'.$path), 320, 240, null, false)
        ];
    }
}
