<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Models\LogModify;
use App\Models\Paper;
use App\Models\Review;
use App\Models\Role;
use App\Models\Submit;
use App\Policies\FilePolicy;
use App\Policies\LogAccessPolicy;
use App\Policies\LogModifyPolicy;
use App\Policies\PaperPolicy;
use DateTime;
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
            $roles = explode("|", $roles_str);

            // 1つ1つチェックして、どれかOKならtrueを返す。
            foreach ($roles as $role_id) {
                if ($user->can('role', $role_id)) return true;
            }
            return false;
        });

        /**
         * 論文情報を作成したオーナー
         */
        Gate::define('edit_paper', function ($user, $paper) {
            if ($paper->owner === $user->id) {
                $ret = "user uid{$user->id} is owner of pid{$paper->id}";
            } else {
                $ret = "NOT ALLOWED user uid{$user->id} is not owner of pid{$paper->id} powner{$paper->owner}";
            }
            return ($paper->owner === $user->id);
        });

        /**
         * 登録された共著者
         */
        Gate::define('show_paper', function ($user, $paper) {
            // if ($paper->owner === $user->id) $ret = "user uid{$user->id} is owner of pid{$paper->id}";
            // else if ($paper->isCoAuthorEmail($user->email)) {
            //     $ret = "user uid{$user->id} is coauthor of pid{$paper->id}";
            // } else {
            //     $ret = "NOT ALLOWED: show_paper";
            // }
            if ($paper->owner === $user->id) return true;
            return $paper->isCoAuthorEmail($user->email);
        });

        Gate::define('am_i_meta', function ($user, $paper) {
            $am_i_meta = $user->can('role_any', 'pc|metareviewer');
            // もし、自分がこの論文の一般査読者なら、metareviewerRoleがあったとしてもam_i_meta = false にする
            $rev = Review::where('paper_id', $paper->id)
                ->where('user_id', $user->id)
                ->where('ismeta', false)
                ->first();
            if ($rev) {
                $am_i_meta = false;
            }
            return $am_i_meta;
        });

        /**
         * カテゴリの管理権限
         */
        Gate::define('manage_cat', function ($user, $category) {
            // もし、PC長なら、true
            if ($user->can('role', 'pc')) return true;
            // そうでなければ、cat_id が0以外のRoleを調べる
            $catid_roles = Role::where('cat_id', $category)->get();
            foreach ($catid_roles as $role) {
                // いずれかのRoleに所属していれば、true
                if ($role->containsUser($user->id)) return true;
            }
            //catcsv についても調査
            $catcsv_roles = Role::where('catcsv', 'like', '%' . $category . '%')->get(); // ここではLIKEでざっくりと絞り込む。
            foreach ($catcsv_roles as $role) {
                $csv = explode(',', $role->catcsv);
                if (!in_array($category, $csv)) continue;
                // いずれかのRoleに所属していれば、true
                if ($role->containsUser($user->id)) return true;
            }
            return false;
        });
        Gate::define('manage_cat_any', function ($user) {
            $catid_roles = Role::where('cat_id', '>', 0)->get();
            foreach ($catid_roles as $role) {
                // いずれかのRoleに所属していれば、true
                if ($role->containsUser($user->id)) return true;
            }
            //catcsv についても調査
            $catcsv_roles = Role::whereNotNull('catcsv')->get(); // ここではLIKEでざっくりと絞り込む。
            foreach ($catcsv_roles as $role) {
                // いずれかのRoleに所属していれば、true
                if ($role->containsUser($user->id)) return true;
            }
            return false;
        });

        /**
         * アクセプトされた論文を持っているか（参加登録） デフォルトでは、共著者の論文も含む
         */
        Gate::define('has_accepted_papers', function ($user, $isinclude_coauthor = true) {
            // アクセプトされた論文を持っているか
            $accPIDs = $user->accepted_papers_as_owner();
            if (count($accPIDs) > 0) return true; // オーナーになっている論文で採録があるならtrue
            if (!$isinclude_coauthor) return false; // 共著者の論文は含めないなら、ここで終了
            // つぎに、共著者の論文についてチェック
            $accPIDs = $user->accepted_papers_as_coauthor();
            return count($accPIDs) > 0;
        });

        /** 投稿完了済みの論文を持っているか（参加登録） 共著者の論文も含む */
        Gate::define('has_submitted_papers', function ($user, $isinclude_coauthor = true) {
            // 投稿完了済みの論文を持っているか
            $subPIDs = Paper::where('owner', $user->id)
                ->where('accepted', true)
                ->get()->pluck("id")->toArray();
            // info("Submitted Paper IDs:"); // あとで消す
            // info($subPIDs); // あとで消す
            if (count($subPIDs) > 0) return true; // オーナーになっている論文で投稿完了があるならtrue
            if (!$isinclude_coauthor) return false; // 共著者の論文は含めないなら、ここで終了
            // つぎに、共著者の論文についてチェック
            $coPIDs = $user->coauthor_papers()->pluck("id")->toArray();
            $subPIDs = Paper::whereIn('id', $coPIDs)
                ->where('accepted', true)
                ->get()->pluck("id")->toArray();
            // info("Submitted Paper IDs (including coauthor):"); // あとで消す
            // info($subPIDs); // あとで消す
            return count($subPIDs) > 0; // オーナーになっている論文で投稿完了があるならtrue
        });

        // 現在が早期申込期間内か
        Gate::define('is_now_early', function () {
            $early_end = \App\Models\Setting::getval('REG_EARLY_LIMIT');
            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $early_end) !== 1) {
                Log::warning("REG_EARLY_LIMIT is not set correctly. value={$early_end}");
                return false;
            }
            return new DateTime() <= new DateTime($early_end . " 23:59:59");
        });
    }
}
