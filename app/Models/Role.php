<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Role extends Model
{
    use HasFactory;
    use FindByIdOrNameTrait; // Role::findByIdOrName(id数値でも nameでもよい)

    // 暫定ロール ここで設定したものはSeederで作成される。
    public static $roles = [
        'admin' => '管理者',
        'manager' => '設定マネージャ',
        'pc' => 'PC',
        'metareviewer' => 'メタ査読者',
        'reviewer' => '査読者',
        'pub' => '出版',
        'web' => 'Web',
        'acc' => '会計',
        'award' => '投票',
        'demo' => 'デモ',
        'local' => 'ローカル',
        'cast' => '中継',
        'ban' => '懇親会',
        'exe' => '運営委員',
        // 'author' => '著者',
        // 'participant' => '参加者',
    ];


    protected $fillable = [
        'name',
        'desc'
    ];

    public function users()
    {
        $tbl = 'role_user';
        return $this->belongsToMany(User::class, $tbl)->withPivot('mailnotify')->orderBy('affil');//->using(RolesUser::class);
    }

    public function containsUser(int $user_id): bool
    {
        return $this->users()->where("user_id", $user_id)->exists();
    }

    public static function checkRoleUser(string|int $role_id, int $user_id){
        if (is_integer($role_id)){
            $role = Role::find($role_id);
        } else if (is_string($role_id)){
            $role = Role::where("name", $role_id)->first();
        }
        return $role->containsUser($user_id);
    }

    /**
     * このRoleよりも、idが小さいRoleのnameを | でつないだもの
     * ただし、長さが0だったら、admin を追加する。
     */
    public function aboveRoles()
    {
        $roles = Role::orderBy("id")->get();
        $ary = [];
        foreach($roles as $role){
            if ($this->id >= $role->id) break;
            $ary []= $role->name;
        }
        if (count($ary)==0) $ary[] = "admin";
        return implode("|", $ary);
    }

    /**
     * テスト用：tinkerから呼び出す
     * demo = 10
     * metareviewer|reviewer|pc|pub|award|acc|demo|web|wc|admin
     */
    public static function resetRolesExcept(int $user_id, $roles){
        $user = User::find($user_id);
        if (is_string($roles)){
            $roles = explode("|", $roles);
            $roles = Role::whereIn("name", $roles)->pluck("id")->toArray();
        }
        $user->roles()->detach();
        foreach($roles as $role){
            $user->roles()->syncWithoutDetaching($role);
        }
        return $roles;
    }
    // テスト用：tinkerから呼び出す
    public static function setRolesExcept(int $user_id, $roles){
        $user = User::find($user_id);
        if (is_string($roles)){
            $roles = explode("|", $roles);
            $roles = Role::whereIn("name", $roles)->pluck("id")->toArray();
            info($roles);
        }
        $all = Role::pluck("id")->toArray();
        foreach($all as $role){
            $user->roles()->syncWithoutDetaching($role);
        }
        foreach($roles as $role){
            $user->roles()->detach($role);
        }
        return $user->roles;
    }

}
