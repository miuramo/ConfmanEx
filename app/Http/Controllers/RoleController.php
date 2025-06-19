<?php

namespace App\Http\Controllers;

use App\Exports\BiddingResultExportFromView;
use App\Exports\RoleMembersExportFromView;
use App\Models\Category;
use App\Models\MailTemplate;
use App\Models\Paper;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class RoleController extends Controller
{

    public function top(string $name)
    {
        // if (!Role::checkRoleUser($name, Auth::id())){
        //     abort(403);
        // }
        if (!auth()->user()->can('role', $name)) {
            if ($name == "reviewer" && auth()->user()->can('role', 'metareviewer')) {
                return redirect()->route('role.top', ["role" => "metareviewer"]);
                // reviewerはmetareviewerも見ることができる。
            } else if ($name == "pub" && auth()->user()->can('role', 'web')) {
                return redirect()->route('role.top', ["role" => "web"]);
            } else if ($name == "author") {
                return redirect()->route('paper.index');
            } else {
                abort(403);
            }
        }
        // $role = Role::where("name",$name)->first();
        $role = Role::findByIdOrName($name);
        return view('role/top', ["role" => 1])->with(["name" => $name, "role" => $role]);
    }

    /**
     * 権限の編集
     */
    public function edit(string $name)
    {
        $role = Role::findByIdOrName($name);
        $aboveroles = $role->aboveRoles();
        if (!auth()->user()->can('role_any', $aboveroles)) abort(403);

        $users = $role->users;
        $roles = Role::orderBy("id")->get();
        return view('role/edit', ["role" => $name])->with(compact("users", "role", "roles"));
    }

    /**
     * Ajax search からのRoleへのユーザ追加
     */
    public function add_to_role(string $name, int $uid)
    {
        $role = Role::findByIdOrName($name);
        $aboveroles = $role->aboveRoles();
        if (!auth()->user()->can('role_any', $aboveroles)) abort(403);
        if (is_numeric($uid)) {
            $u = User::find($uid);
            if ($u != null) {
                $u->roles()->syncWithoutDetaching($role);
            }
            return redirect()->route('role.edit', ["role" => $name]);
        }
    }

    /**
     * role.editpost
     */
    public function editpost(Request $req, string $name)
    {
        $role = Role::findByIdOrName($name);
        $aboveroles = $role->aboveRoles();
        if (!auth()->user()->can('role_any', $aboveroles)) abort(403);

        if ($req->has("action") && $req->input("action") == "excel") {
            return Excel::download(new RoleMembersExportFromView($role), "role_{$name}_members.xlsx");
        } else if ($req->has("action") && $req->input("action") == "otherroles") {
            // valueがonの要素をあつめる。u_{uid}になっているので、とりだす。
            $target_users = []; // uid (integer) の配列
            foreach ($req->all() as $k => $v) {
                if ($v == 'on' && strpos($k, 'u_') === 0) {
                    $uid = explode("_", $k)[1];
                    if (is_numeric($uid)) $target_users[] = $uid;
                }
            }
            $target_roles = []; // Roleオブジェクト の配列
            foreach ($req->all() as $kk => $vv) {
                if ($vv == 'on' && strpos($kk, 'ROLE_') === 0) {
                    $kkary = explode("_", $kk);
                    if (isset($kkary[1])) {
                        $rid = $kkary[1];
                        if (is_numeric($rid)) $target_roles[] = Role::find($rid);
                    }
                }
            }
            foreach ($target_users as $uuid) {
                $u = User::find($uuid);
                if ($u != null) {
                    foreach ($target_roles as $tRole) {
                        if ($tRole->containsUser($uuid)) continue;
                        $u->roles()->syncWithoutDetaching($tRole);
                    }
                }
            }
            return redirect()->route('role.edit', ["role" => $name])->with('feedback.success', "他のRoleを追加しました。");
        } else if ($req->has("action") && $req->input("action") == "leaverole") { //脱退、削除
            $target_users = []; // uid (integer) の配列
            foreach ($req->all() as $k => $v) {
                if ($v == 'on' && strpos($k, 'u_') === 0) {
                    $uid = explode("_", $k)[1];
                    if (is_numeric($uid)) $target_users[] = $uid;
                }
            }
            foreach ($target_users as $uuid) {
                $u = User::find($uuid);
                if ($u != null) {
                    $u->roles()->detach($role);
                }
            }
            return redirect()->route('role.edit', ["role" => $name])->with('feedback.success', "選択したユーザのRoleを削除しました。");
        } else if ($req->has("action") && $req->input("action") == "addtemplate") {
            // valueがonの要素をあつめる。u_{uid}になっているので、とりだす。
            $target_users = [];
            foreach ($req->all() as $k => $v) {
                if ($v == 'on' && strpos($k, 'u_') === 0) {
                    $uid = explode("_", $k)[1];
                    if (is_numeric($uid)) $target_users[] = $uid;
                }
            }
            if (count($target_users) > 0) {
                $targetmt = MailTemplate::find($req->input("mailtemplate"));
                if ($targetmt == null) {
                    return redirect()->route('role.edit', ["role" => $name])->with('feedback.error', "雛形が選択されていませんでした。");
                }
                $mt = $targetmt->replicate();
                $mt->from = "[:MAILFROM:]";
                $mt->to = "userid(" . implode(", ", $target_users) . ")";
                $mt->user_id = auth()->user()->id;
                $mt->lastsent = null;
                $mt->updated_at = date("Y-m-d H:i:s");
                $mt->save();

                return redirect()->route('mt.index',)->with('feedback.success', "指定したユーザに送信する雛形(ID: {$mt->id})を作成しました。");
            }
            return redirect()->route('role.edit', ["role" => $name])->with('feedback.error', "送信予定のユーザにチェックをいれてください。");
        } else if ($req->has("action") && $req->input("action") == "mailsend") {
            // valueがonの要素をあつめる。u_{uid}になっているので、とりだす。
            $target_users = [];
            foreach ($req->all() as $k => $v) {
                if ($v == 'on' && strpos($k, 'u_') === 0) {
                    $uid = explode("_", $k)[1];
                    if (is_numeric($uid)) $target_users[] = $uid;
                }
            }
            if (count($target_users) > 0) {
                MailTemplate::bundleUser($target_users, $req->input("subject"), $req->input("body"));
                return redirect()->route('role.edit', ["role" => $name])->with('feedback.success', "メールを送信しました。");
            }
            return redirect()->route('role.edit', ["role" => $name])->with('feedback.error', "メールを送信するユーザにチェックをいれてください。");
        } else if ($req->has("action") && $req->input("action") == "editnotify") {
            // info($req->all());
            foreach ($req->all() as $k => $v) {
                if ($v == 'on' && strpos($k, 'u_') === 0) {
                    $uid = explode("_", $k)[1];
                    if (is_numeric($uid)) {
                        $u = User::find($uid);
                        if ($u != null) {
                            $u->roles()->updateExistingPivot($role->id, ['mailnotify' => $req->input("notify") == 'on']);
                        }
                    }
                }
            }
            return redirect()->route('role.edit', ["role" => $name])->with('feedback.success', "mailnotifyを変更しました。");
        } else if ($req->has("action") && $req->input("action") == "adduser") {
            $adduser = $req->input("adduser");
            $lines = explode("\n", $adduser);
            $lines = array_map("trim", $lines);
            foreach ($lines as $line) {
                $line = str_replace("（", "(", $line);
                $line = str_replace("）", ")", $line);
                $line = str_replace("(", "\t", $line);
                $line = str_replace(")", "\t", $line);
                $ary = explode("\t", $line);
                $ary = array_map("trim", $ary);
                if (count($ary) == 3) {
                    $uu = User::where("email", $ary[2])->first();
                    if ($uu == null) {
                        $uu = User::factory()->create([
                            'name' => $ary[0],
                            'affil' => $ary[1],
                            'email' => $ary[2],
                            'password' => Hash::make(Str::random(10)),
                        ]);
                    }
                    // auto_role_member でRoleがつくかもしれないので、チェックする。
                    $role = Role::findByIdOrName($name);
                    if (!$role->containsUser($uu->id)) { // ふくまれていなければ
                        $uu->roles()->syncWithoutDetaching($role);
                    }
                } else if (count($ary) == 1) {
                    $u = User::where("email", $ary[0])->first();
                    if ($u != null) {
                        $role = Role::findByIdOrName($name);
                        if (!$role->containsUser($u->id)) { // ふくまれていなければ
                            $u->roles()->syncWithoutDetaching($role);
                        }
                    }
                }
            }
            return redirect()->route('role.edit', ["role" => $name]);
        }
    }

    public function leave(Role $role, User $user)
    {
        $aboveroles = $role->aboveRoles();
        if (!auth()->user()->can('role_any', $aboveroles)) abort(403);

        $user->roles()->detach($role);
        return redirect()->route('role.edit', ["role" => $role->name]);
    }

    /**
     * 査読割り当て
     */
    public function revassign(Role $role, Category $cat)
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        // 査読者がBiddingしてくれない場合もあるので、ここで抽出しておく。
        Review::extractAllCoAuthorRigais();

        $reviewers = $role->users;
        $roles = Role::where("name", "like", "%reviewer")->get();
        $papers = $cat->paperswithpdf;
        $cats = Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
        return view('role.revassign', ["role" => $role, "cat" => $cat])->with(compact("reviewers", "role", "roles", "cat", "papers", "cats"));
    }

    public function revassignpost(Request $req, Role $role, Category $cat)
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        set_time_limit(120);
        if ($req->has("paper_id") && $req->has("user_id") && $req->has("status")) {
            $status = $req->input("status");
            $paper_id = $req->input("paper_id");
            $user_id = $req->input("user_id");
            Review::review_assign($paper_id, $user_id, $status);
            $colors = ["teal", "cyan", "red"];
            if ($status == 0) return "";
            return "<span class=\"text-2xl text-{$colors[$status]}-500\">★</span>";
        } else {
            return "FAILED";
        }
    }

    public function revassign_excel(Role $role, Category $cat)
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        Review::extractAllCoAuthorRigais();
        // $reviewers = $role->users;
        // $roles = Role::where("name", "like", "%reviewer")->get();
        // $papers = $cat->paperswithpdf;
        // $cats = Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
        return Excel::download(new BiddingResultExportFromView($cat, $role), "bidding_{$cat->name}.xlsx");
    }

    // TODO: call Review:randomAssign and analyze
    public function revassign_random(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        $roles = Role::where("name", "like", "%reviewer")->get();
        $cats = Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
        if ($req->has("action")) {
            if ($req->input("action") == "assign") {
                $num = $req->input("num");
                $repnum = [$num[5], $num[4]];
                $catids = $req->input("cat");
                $exclude = $req->input("exclude");
                if ($exclude) {
                    $exclude = explode(",", $exclude);
                } else {
                    $exclude = [];
                }
                dispatch(function () use ($repnum, $catids, $exclude) {
                    Review::randomAssign($repnum, $catids, $exclude);
                });
                sleep(3);
                return redirect()->route('role.revassign_random')->with('feedback.success', "割り当て処理をバックグラウンドで実行しています。進捗状況をみるには、このページを再読み込みしてください。微調整が必要な場合は、下の「査読割り当て」ボタンから行ってください。");
            } else if ($req->input("action") == "reset") {
                $catids = $req->input("cat");
                Review::whereIn("paper_id", Paper::whereIn("category_id", $catids)->pluck("id"))->delete();
                return redirect()->route('role.revassign_random')->with('feedback.success', "割り当てをリセットしました。");
            }
        } else {
            $out = [];
        }
        // $result = 
        return view('role.revassign_random')->with(compact("roles", "cats", "out"));
    }
}
