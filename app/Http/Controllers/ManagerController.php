<?php

namespace App\Http\Controllers;

use App\Exports\PapersExport4Hiroba;
use App\Exports\PapersExportFromView;
use App\Jobs\ExportHintFileJob;
use App\Jobs\Test9w;
use App\Mail\DisableEmail;
use App\Mail\ForAuthor;
use App\Models\Bb;
use App\Models\BbMes;
use App\Models\Contact;
use App\Models\EnqueteAnswer;
use App\Models\File;
use App\Models\LogAccess;
use App\Models\LogCreate;
use App\Models\LogForbidden;
use App\Models\LogModify;
use App\Models\MailTemplate;
use App\Models\Paper;
use App\Models\RevConflict;
use App\Models\Review;
use App\Models\Role;
use App\Models\Setting;
use App\Models\Submit;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Str;

use ZipArchive;

class ManagerController extends Controller
{

    public function rebuildPDFThumb()
    {
        if (!auth()->user()->can('role_any', 'admin|manager')) abort(403);
        File::rebuildPDFThumb();
        return redirect()->route('role.top', ['role'=>'admin']);
    }


    /**
     * CROP Imageの確認と再作成
     */
    public function paperlist_headimg()
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        $all = Paper::whereNotNull('pdf_file_id')->get();

        return view('admin.paperlist_headimg')->with(compact("all"));
    }
    public function paperlist_headimg_recrop()
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        $all = Paper::whereNotNull('pdf_file_id')->get();
        foreach ($all as $paper) {
            $paper->pdf_file->altimg_recrop();
        }
        return redirect()->route('admin.paperlist_headimg')->with('feedback.success', 'タイトル画像の再クロップを開始しました。');
    }

    public function addInvitedPaper(Request $req)
    {
        if (!auth()->user()->can('role_any', 'admin|pc')) abort(403);
        if ($req->has('action') && $req->input('catid') != null) {
            try {
                $paper = Paper::create([
                    'category_id' => $req->input("catid"),
                    'contactemails' => Auth::user()->email,
                    'owner' => Auth::user()->id,
                    'title' => $req->input("title"),
                    'authorlist' => $req->input("authorlist"),
                    'abst' => $req->input("abst"),
                ]);
            } catch (QueryException $e) {
                return redirect()->route('paper.create')->with('feedback.error', "QueryException on Paper create");
            }
            // find corresponding submit (created by observer)
            $submit = Submit::where('paper_id', $paper->id)->first();
            $submit->accept_id = $req->input("accid");
            $submit->save();

            return redirect()->route('add_invited_paper')->with('feedback.success', '招待論文を追加しました (pid=' . $paper->id . ', subid=' . $submit->id.')');
        }

        return view('admin.add_invited_paper');
    }


    

    public function test9w()
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        Test9w::dispatch();
        // $this->ocr9w();
        ExportHintFileJob::dispatch();
        return redirect()->route('role.top', ['role'=>'admin'])->with('feedback.success', 'テストQueueを実行しました。再読み込みして各種設定→LAST_QUEUEWORK_DATEが更新されていることを確認してください。');
    }

    

    public function paperauthorhead(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        $sets = Setting::where("name", "like", "SKIP_HEAD_%")->where("valid", true)->get();
        $papers = Paper::whereNotNull("pdf_file_id")->get();
        if ($req->input('action') == 'titleupdate') { // 第3要素のタイトルで書き換える
            foreach ($papers as $paper) {
                $title = $paper->title_candidate();
                foreach ($sets as $set) {
                    $title = str_replace($set->value, "", $title);
                }
                // authorheadが含まれていたら
                $pos = mb_strpos($title, mb_substr($paper->authorhead, 0, 2));
                if ($pos > -1 && $pos !== false) {
                    $title = mb_substr($title, 0, $pos);
                }
                $paper->title = $title;
                $paper->save();
            }
            return redirect()->route('admin.paperauthorhead')->with('feedback.success', 'タイトルを一括更新しました');
        }
        if ($req->input('action') == 'setfirstauthor_ifnull') { // ★★第一著者未設定★★ について、第一著者の苗字を設定する
            foreach ($papers as $paper) {
                if (mb_strlen($paper->authorhead) < 1 && $paper->category_id != 3) {
                    $myouji = explode(" ", $paper->paperowner->name)[0];
                    $paper->authorhead = $myouji;
                    $paper->save();
                }
            }
            return redirect()->route('admin.paperauthorhead')->with('feedback.success', '★★第一著者未設定★★ について、第一著者の苗字を設定しました');
        }
        if ($req->has('authorheads')) { // テキストエリアがある場合
            $lines = explode("\n", $req->input('authorheads'));
            $lines = array_map("trim", $lines);
            foreach ($lines as $n => $line) {
                $ary = explode(";;", $line);
                $ary = array_map("trim", $ary);
                $paper = Paper::find($ary[0]);
                $paper->authorhead = $ary[1];
                $paper->save();
                // info($ary);
            }
            return redirect()->route('admin.paperauthorhead')->with('feedback.success', 'updated');
        }

        return view('admin.paperauthorhead')->with(compact("papers", "sets"));
    }

    public function importpaperjson(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);

        $out = "";
        $src = "";
        $updated = false;
        if ($req->has('paperjson')) { // テキストエリアがある場合
            // info($req->input('action'));
            $src = $req->input('paperjson');
            $jsons = json_decode($req->input('paperjson'), true);
            foreach ($jsons as $json) {
                $booth = $json['id'];
                if (is_numeric($booth)) {
                    $booth = (int)$booth;
                }
                $sub = Submit::where('booth', $booth)->first();
                if ($sub) {
                    if ($sub->paper->title != $json['title']) {
                        $out .= $json['title'] . "  " . $sub->paper_id . " ;; " . $json['id'] . " ;; " . "\n";
                        $out .= "" . $sub->paper->title . "\n";
                        if ($req->input('action') == 'doreplace') {
                            $sub->paper->title = $json['title'];
                            $sub->paper->save();
                        }
                    }
                }
            }
            if ($req->input('action') == 'doreplace') {
                $updated = true;
            }
        }
        return view('admin.importpaperjson')->with(compact("out", "src", "updated"));
    }

    public function upsearch(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc|admin')) abort(403);
        if ($req->has('query')) {
            $search = $req->input('query');

            $uresults = User::where('name', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%')
                ->orWhere('id', 'like', '%' . $search . '%')
                ->get();
            $presults = Paper::where('title', 'like', '%' . $search . '%')
                ->orWhere('id', 'like', '%' . $search . '%')
                ->orWhere('authorlist', 'like', '%' . $search . '%')
                ->get();

            return response()->json(['u' => $uresults, 'p' => $presults, 'id' => auth()->id()]);
        }
        return view('admin.upsearch'); //->with(compact("out"));
    }

    public function user_yomi_post(Request $req)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc')) abort(403);

        $roleid = $req->input('roleid');
        $input = $req->input('yomiinput');
        $lines = explode("\n", $input);
        $lines = array_map("trim", $lines);
        $count = 0;
        foreach ($lines as $line) {
            // $lineにふくまれる全角スペースを半角スペースに変換し、タブも半角スペースに変換する
            $line = str_replace("　", " ", $line);
            $line = str_replace("\t", " ", $line);
            // 連続する半角スペースを;;に変換する
            $line = preg_replace('/[ ]+/', ';;', $line);
            $ary = explode(";;", $line);
            $ary = array_map("trim", $ary);
            if (count($ary) >= 2) {
                $user = User::where("name", $ary[0]." ".$ary[1])->whereIn("id", function($query) use ($roleid) {
                    $query->select('user_id')
                        ->from('role_user')
                        ->where('role_id', $roleid);
                })->first();
                if ($user) {
                    $user->yomi = $ary[2]." ".$ary[3];
                    $user->save();
                    $count++;
                }
            }
        }

        return redirect()->route('role.edit', ['role' => $req->input('rolename')])->with('feedback.success', 'ユーザーの読み仮名を ' . $count . ' 件登録しました。');
    }
}
