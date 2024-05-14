<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\LogModify;
use App\Models\Paper;
use App\Models\Role;
use App\Policies\FilePolicy;
use App\Policies\LogAccessPolicy;
use App\Policies\LogModifyPolicy;
use App\Policies\PaperPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Paper::class => PaperPolicy::class,
        File::class => FilePolicy::class,
        LogModify::class => LogModifyPolicy::class,
        LogAccess::class => LogAccessPolicy::class,
        //
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('admin', function ($user) {
            $admin = Role::firstOrCreate(
                [
                    'name' => 'admin',
                ]
            );
            return $admin->users()->where("user_id", $user->id)->exists();
        });

        /**
         * $role_id は id数値でも nameでもよい。なんならObjectでもよい
         */
        Gate::define('role', function ($user, $role_id) {
            $role = Role::findByIdOrName($role_id);
            if ($role == null) return false;
            return $role->users()->where("user_id", $user->id)->exists();
        });
        /**
         * どれか1つのRole
         */
        Gate::define('role_any', function ($user, string $roles_str) {
            $roles = explode("|",$roles_str);

            // 1つ1つチェックして、どれかOKならtrueを返す。
            foreach($roles as $role_id){
                if ($user->can('role', $role_id)) return true;
            }
            return false;
        });


        Gate::define('edit_paper', function ($user, $paper){
            if ($paper->owner === $user->id) {
                $ret = "user uid{$user->id} is owner of pid{$paper->id}";
            } else {
                $ret = "NOT ALLOWED user uid{$user->id} is not owner of pid{$paper->id} powner{$paper->owner}";
            }
            return ($paper->owner === $user->id);
        });

        Gate::define('show_paper', function ($user, $paper){
            if ($paper->owner === $user->id) $ret = "user uid{$user->id} is owner of pid{$paper->id}";
            else if ($paper->isCoAuthorEmail($user->email)){
                $ret = "user uid{$user->id} is coauthor of pid{$paper->id}";
            } else {
                $ret = "NOT ALLOWED: show_paper";
            }
            if ($paper->owner === $user->id) return true;
            return $paper->isCoAuthorEmail($user->email);
        });
    }
}
