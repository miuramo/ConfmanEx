<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Notifications\FirstEntryNotification;
use App\Notifications\ResetPasswordNotification4FirstEntry;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;
    use FindByIdOrNameTrait; // Role::findByIdOrName(id数値でも nameでもよい)
    use SoftDeletes;

    // protected $dispatchesEvents = [
    //     'created' => UserCreated::class,
    //     'updated' => UserUpdated::class,
    // ];

    // 初期状態のName
    public static string $initialName = ""; // "投稿 太郎";
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'yomi',
        'affil',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function papers()
    {
        return $this->hasMany(Paper::class, 'owner');
    }
    public function roles()
    {
        $instance = new Role();
        return (new \App\Relations\BTMwithListener(
            $instance->newQuery(),
            $this,
            'role_user',
            'user_id',
            'role_id',
            $this->getKeyName(),
            $instance->getKeyName()
        ))->withListener(function ($operation, $parent, $related, $relatedId) {
            if ($relatedId instanceof Role) {
            } else {
                $relatedId = Role::find($relatedId);
            }
            Log::info("{$operation}: {$related->getTable()} ID {$relatedId->id} ({$relatedId->desc}) <=> {$parent->getTable()} ID {$parent->getKey()} {$parent->name}");
            // Log::info("{$operation}: {$related->getTable()} ID {$relatedId->id} ({$relatedId->desc}) <=> {$parent->getTable()} ID {$parent->getKey()} {$parent->name}");
            // Log::info("{$operation}: {$related->getTable()} ID {$relatedId} {$parent->id} <=> {$parent->getTable()} ID {$parent->getKey()} {$parent->name} ({$parent->affil}) {$parent->email} {$operation} role {$relatedId}");
        });            //

        // $tbl = 'role_user';
        // return $this->belongsToMany(Role::class, $tbl)->withPivot('mailnotify')->orderBy('orderint')->orderBy('roles.id'); //->using(RolesUser::class);
        // $table_fields = Schema::getColumnListing($tbl);
        // return $this->belongsToMany(User::class, $tbl, 'role_id', 'user_id');// ->withPivot($table_fields)->using(RolesUser::class);
    }

    // attach をラップする関数（role_userの追加削除をログに記録するため）
    /*
    public function syncRolesWithLogging(array $newRoleIds)
    {
        info("syncRolesWithLogging: for user {$this->id} {$this->name} {$this->affil} {$this->email}");
        info("syncRolesWithLogging: newRoleIds: " . implode(',', $newRoleIds));

        // 呼び出し前の関連ID一覧を取得
        $before = $this->roles()
            ->select('role_user.role_id')
            ->pluck('role_user.role_id')
            ->toArray();

        // syncWithoutDetaching を実行（新規のみ追加）
        $this->roles()->syncWithoutDetaching($newRoleIds);

        // 呼び出し後の関連ID一覧を取得
        $after = $this->roles()
            ->select('role_user.role_id')
            ->pluck('role_user.role_id')
            ->toArray();

        // 新しく追加されたIDを検出
        $added = array_diff($after, $before);

        // ログ記録やイベント発火など
        foreach ($added as $roleId) {
            \Log::info("User {$this->id} {$this->name} ({$this->affil}) {$this->email} attached new role {$roleId}");
        }
        return $added;
    }
    // detach をラップする関数（role_userの追加削除をログに記録するため）
    public function detachRolesWithLogging(int $roleId)
    {
        // info("detachRolesWithLogging: {$roleId} for user {$this->id} {$this->name} {$this->affil} {$this->email}");
        // 呼び出し前の関連ID一覧を取得
        $before = $this->roles()
            ->select('role_user.role_id')
            ->pluck('role_user.role_id')
            ->toArray();

        // detach を実行
        $this->roles()->detach($roleId);

        // 呼び出し後の関連ID一覧を取得
        $after = $this->roles()
            ->select('role_user.role_id')
            ->pluck('role_user.role_id')
            ->toArray();

        // 削除されたIDを検出
        $removed = array_diff($before, $after);

        // ログ記録やイベント発火など
        foreach ($removed as $roleId) {
            \Log::info("User {$this->id} {$this->name} ({$this->affil}) {$this->email} detached role {$roleId}");
        }
        return $removed;
    }*/

    public function contact()
    {
        return $this->belongsTo(Contact::class, "contact_id");
    }

    // エディターの最高権限を文字列で返す
    public function maxRole()
    {
        $roles = $this->roles;
        foreach ($roles as $role) {
            if ($role->name == "pc") return "pc";
            if ($role->name == "metareviewer") return "metareviewer";
            if ($role->name == "reviewer") return "reviewer";
        }
        return "author";
    }

    public function is_pc_member()
    {
        return $this->maxRole() !== "author";
    }

    /**
     * 初回のみ、パスワード再設定メールを変更している。see User.php
     */
    public function sendPasswordResetNotification($token)
    {
        if ($this->name == User::$initialName) {
            $this->notify(new FirstEntryNotification($token));
        } else {
            parent::sendPasswordResetNotification($token);
        }
    }

    /**
     * 共著者分のPaper
     */
    public function coauthor_papers(): Collection
    {
        try {
            $contact = Contact::where('email', $this->email)->first();

            if ($contact == null) return new Collection();
            return $contact->papers->whereNotIn('owner', $this->id)->sortBy("id");
        } catch (ModelNotFoundException $ex) {
            return new Collection();
        }
        return new Collection();
    }

    /**
     * 最終アクセス
     */
    public function last_access(): string
    {
        $last = LogAccess::where("uid", $this->id)->orderBy("created_at", "desc")->first();
        return ($last == null) ? "---" : $last->created_at;
    }

    /**
     * デバッグ用の表示
     */
    public function print_coauthor_papers()
    {
        // owner papers
        $contact = Contact::where('email', $this->email)->first();
        foreach ($contact->papers->whereIn('owner', $this->id)->sortBy("id") as $p) {
            echo "uid {$this->id} OWN pid: {$p->id} , title: {$p->title}\n";
        }
        foreach ($contact->papers->whereNotIn('owner', $this->id)->sortBy("id") as $p) {
            echo "uid {$this->id} CO  pid: {$p->id} , title: {$p->title}\n";
        }
    }

    /**
     * 共著表示バリデーション、テスト用のデータ
     */
    public function coary($rettype = "ret")
    {
        $ret = [];
        $mypids = [];
        $contact = Contact::where('email', $this->email)->first();
        // 自分が投稿者
        foreach ($contact->papers->whereIn('owner', $this->id)->sortBy("id") as $p) {
            $ret["/paper/{$p->id}/edit"] = 200;
            $ret["/paper/{$p->id}"] = 200;
            $mypids[] = $p->id;
        }
        // 共著
        foreach ($contact->papers->whereNotIn('owner', $this->id)->sortBy("id") as $p) {
            $ret["/paper/{$p->id}/edit"] = 403;
            $ret["/paper/{$p->id}"] = 200;
            $mypids[] = $p->id;
        }
        // それ以外
        foreach (Paper::whereNotIn('id', $mypids)->get() as $p) {
            $ret["/paper/{$p->id}/edit"] = 403;
            $ret["/paper/{$p->id}"] = 403;
        }
        if ($rettype == "ret") return $ret;
        else return $mypids;
    }

    // テスト用：適当にBiddingをする
    public function test_revconflict()
    {
        $mypids = $this->coary("mypids");
        foreach (Paper::whereNotIn('id', $mypids)->get() as $p) {
            $revcon = RevConflict::firstOrCreate([
                'user_id' => $this->id,
                'paper_id' => $p->id,
                'bidding_id' => 5,
            ]);
        }
    }

    public function get_mail_to_cc()
    {
        $cclist = [];
        return ["to" => $this->email, "cc" => $cclist];
    }
    public function id_03d()
    {
        return sprintf("uid%d %s", $this->id, $this->name);
    }

    /**
     * Contactを直す（投稿リセットをすると、Contactを壊してしまう？）
     */
    public function fix_broken_contact()
    {
        DB::transaction(function () {
            $con = Contact::firstOrCreate([
                'email' => $this->email,
            ]);
            $this->contact_id = $con->id;
            $this->save();
        });
    }
    /**
     * Userが存在しないContactを参照していたら、直す
     */
    public static function fix_broken_contact_all()
    {
        // 存在していないContactを参照していたら、作成しなおす
        $con = Contact::pluck("id")->toArray();
        $uary = User::whereNotIn("contact_id", $con)->get();
        foreach ($uary as $u) {
            $u->fix_broken_contact();
        }

        // 存在しているが、email が違う場合は、作成しなおす
        $uary2 = User::with("contact")->get();
        foreach ($uary2 as $u) {
            if ($u->contact->email != $u->email) {
                $u->fix_broken_contact();
            }
        }
    }

    /**
     * 氏名が全角スペース区切りだったり、半角スペース複数区切りだったりするのを修正
     */
    public static function fix_username_space_all()
    {
        $uary = User::all();
        foreach ($uary as $u) {
            $name = preg_replace('/\s+/', ' ', trim($u->name));
            $name = preg_replace('/　+/', ' ', trim($name));
            if ($name != $u->name) {
                info("Fix username for uid {$u->id}: '{$u->name}' => '{$name}'");
                $u->name = $name;
                $u->save();
            }
            $yomi = preg_replace('/\s+/', ' ', trim($u->yomi));
            $yomi = preg_replace('/　+/', ' ', trim($yomi));
            if ($yomi != $u->yomi) {
                info("Fix useryomi for uid {$u->id}: '{$u->yomi}' => '{$yomi}'");
                $u->yomi = $yomi;
                $u->save();
            }
        }
    }

    public function accepted_papers_as_owner()
    {
        $accPIDs = Submit::with('paper')->whereHas('paper', function ($query) {
            $query->where('owner', $this->id);
        })->whereHas("accept", function ($query) {
            $query->where("judge", ">", 0);
        })->get()->pluck("paper_id")->toArray();
        return $accPIDs;
    }
    public function accepted_papers_as_coauthor()
    {
        $coPIDs = $this->coauthor_papers()->pluck("id")->toArray();
        $accPIDs = Submit::with('paper')->whereHas('paper', function ($query) use ($coPIDs) {
            $query->whereIn('id', $coPIDs);
        })->whereHas("accept", function ($query) {
            $query->where("judge", ">", 0);
        })->get()->pluck("paper_id")->toArray();
        return $accPIDs;
    }
    public function accepted_papers_as_any()
    {
        $accPIDs = $this->accepted_papers_as_owner();
        $cooPIDs = $this->accepted_papers_as_coauthor();
        foreach ($cooPIDs as $p) {
            if (!in_array($p, $accPIDs)) $accPIDs[] = $p;
        }
        return $accPIDs;
    }
}
