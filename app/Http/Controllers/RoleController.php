<?php

namespace App\Http\Controllers;

use App\Exports\RoleMembersExportFromView;
use App\Models\Category;
use App\Models\MailTemplate;
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
            abort(403);
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
                if ($v == 'on' && strpos($k, 'u_') == 0) {
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
                        $u->roles()->attach($tRole);
                    }
                }
            }
            return redirect()->route('role.edit', ["role" => $name])->with('feedback.success', "他のRoleを追加しました。");
        } else if ($req->has("action") && $req->input("action") == "mailsend") {
            // valueがonの要素をあつめる。u_{uid}になっているので、とりだす。
            $target_users = [];
            foreach ($req->all() as $k => $v) {
                if ($v == 'on' && strpos($k, 'u_') == 0) {
                    $uid = explode("_", $k)[1];
                    if (is_numeric($uid)) $target_users[] = $uid;
                }
            }
            if (count($target_users) > 0) {
                MailTemplate::bundleUser($target_users, $req->input("subject"), $req->input("body"));
                return redirect()->route('role.edit', ["role" => $name])->with('feedback.success', "メールを送信しました。");
            }
            return redirect()->route('role.edit', ["role" => $name])->with('feedback.success', "メールを送信するユーザにチェックをいれてください。");
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
                    $uu->roles()->attach($role);
                } else if (count($ary) == 1) {
                    $u = User::where("email", $ary[0])->first();
                    if ($u != null) {
                        $u->roles()->attach($role);
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
        $papers = $cat->papers;
        $cats = Category::select('id', 'name')->get()->pluck('name', 'id')->toArray();
        return view('role.revassign', ["role" => $role, "cat" => $cat])->with(compact("reviewers", "role", "roles", "cat", "papers", "cats"));
    }
    public function revassignpost(Request $req, Role $role, Category $cat)
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
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
}
