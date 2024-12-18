<?php

namespace Database\Factories;

use App\Models\File;
use App\Models\Paper;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'affil' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * ロールをつける。ロールはすでにあるものを名前で指定して使う。
     * @param string $roles metareviewer|reviewer|pc|...
     */
    public function withRoles(string $roles = "")
    {
        if (is_string($roles)){
            $roles = explode("|", $roles);
            $roleids = Role::whereIn("name", $roles)->pluck("id")->toArray();
            // ここで、rolesにはRoleのidが入っている。
        }
        return $this->afterCreating(function (User $user) use ($roleids) {
            foreach($roleids as $roleid){
                $user->roles()->syncWithoutDetaching($roleid);
            }
        });
    }

    public function withPapers(int $num = 1, int $cat_id = 1)
    {
        return $this->afterCreating(function (User $user) use ($num, $cat_id) {
            for($i=0; $i<$num; $i++){
                $pdf = File::create([
                    'fname' => fake()->word() . ".pdf",
                    'origname' => fake()->word() . ".pdf",
                    'mime' => "application/pdf",
                    'key' => strtolower(bin2hex(random_bytes(16))),
                    'user_id' => $user->id,
                    'pagenum' => 6,
                ]);
                $paper = Paper::create([
                    'category_id' => $cat_id,
                    'contactemails' => $user->email,
                    'owner' => $user->id,
                    'title' => fake()->sentence(),
                    'pdf_file_id' => $pdf->id,
                ]);
                $paper->updateContacts();
                $pdf->paper_id = $paper->id;
                $pdf->save();
            }
            // Paper::factory($num)->create(['owner' => $user->id]);
        });
    }
}
