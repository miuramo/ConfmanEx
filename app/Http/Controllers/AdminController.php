<?php

namespace App\Http\Controllers;

use App\Exports\PapersExport4Hiroba;
use App\Exports\PapersExportFromView;
use App\Jobs\ExportHintFileJob;
use App\Jobs\Test9w;
use App\Mail\DisableEmail;
use App\Mail\ForAuthor;
use App\Models\Affil;
use App\Models\Bb;
use App\Models\BbMes;
use App\Models\Confirm;
use App\Models\Contact;
use App\Models\EnqueteAnswer;
use App\Models\File;
use App\Models\LogAccess;
use App\Models\LogCreate;
use App\Models\LogForbidden;
use App\Models\LogModify;
use App\Models\MailTemplate;
use App\Models\Paper;
use App\Models\Regist;
use App\Models\RevConflict;
use App\Models\Review;
use App\Models\Role;
use App\Models\Score;
use App\Models\Setting;
use App\Models\Submit;
use App\Models\User;
use App\Models\Viewpoint;
use App\Models\Vote;
use App\Models\VoteItem;
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
use STS\ZipStream\Facades\Zip;
use ZipArchive;

class AdminController extends Controller
{
    public function dashboard()
    {
        if (!auth()->user()->can('role_any', 'admin|manager')) abort(403);

        $roles = auth()->user()->roles;
        $roleall = Role::all();

        // Setting seeder
        Setting::seeder();
        // Confirm seeder
        Confirm::seeder_policy();

        // Userが存在しないContactを参照していたら、直す
        User::fix_broken_contact_all();

        return view('admin.admindb')->with(compact("roles", "roleall"));
    }

    public function disable_email(Request $req)
    {
        if (!auth()->user()->can('role_any', 'admin|manager')) abort(403);

        $em = $req->input("invalid_email");
        $dryrun = $req->input("dryrun");
        if (strlen($em) < 4) {
            return redirect()->route('role.top', ['role' => 'admin'])->with('feedback.error', '無効にしたいメールアドレスを入力してください。');
        }

        // Contactから辿れる、papersについて、投稿連絡用メールアドレスcontactemails から抜く。抜いた後でcontactsリレーションを更新。
        $contact = Contact::findByIdOrName($em, null, "email");
        $ids = [];
        if ($contact != null && $contact->papers != null) {
            foreach ($contact->papers as $paper) {
                $ids[] = $paper->id_03d();
                if ($dryrun == null || $dryrun != "DRYRUN") {
                    $paper2 = Paper::with("contacts")->find($paper->id);
                    $paper2->remove_contact($contact); // ここでの修正は、log_modifiesに反映されない
                    // メール送信（またはスプール） TODO: mail send
                    $paper2->pendingMail(new DisableEmail($paper2, $em));
                }
            }
        }

        return redirect()->route('role.top', ['role' => 'admin'])->with('feedback.success', 'すべてのPaperの投稿連絡用メールアドレスから削除しました。' . implode(",", $ids));
    }

    public function paperlist(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc')) {
            if (!auth()->user()->can('manage_cat_any')) abort(403);
        }
        // Formからのカテゴリ選択を配列にいれる
        $targets = [];
        foreach ($req->all() as $k => $v) {
            if (strpos($k, "targetcat") === 0) $targets[] = $v;
        }
        // if (count($targets) == 0) $targets =  [1, 2, 3];
        // if (count($targets) == 0) $targets =  [1, 2, 3];

        $all = Paper::whereIn('category_id', $targets)->get();
        $roles = auth()->user()->roles;
        // アンケート showonpaperindex
        // [paperid][enqid][name1] = value1
        // [paperid][enqid][name2] = value2
        $enqans = EnqueteAnswer::getAnswers();
        $target_str = implode("", $targets);
        if ($req->has("action") && $req->input("action") == "excel") {
            return Excel::download(new PapersExportFromView($targets), "paperlist_{$target_str}.xlsx");
        }
        $targets = array_flip($targets);
        return view('admin.paperlist')->with(compact("all", "roles", "enqans", "targets"));
    }
    /**
     * じつはあまり使われない。すべての投稿論文リストのときのみ。
     */
    public function paperlist_excel(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc')) {
            if (!auth()->user()->can('manage_cat_any')) abort(403);
        }
        // Formからのカテゴリ選択を配列にいれる
        $targets = [];
        foreach ($req->all() as $k => $v) {
            if (strpos($k, "targetcat") === 0) $targets[] = $v;
        }
        // if (count($targets) == 0) $targets =  [1, 2, 3];
        $target_str = implode("", $targets);
        return Excel::download(new PapersExportFromView($targets), "paperlist_{$target_str}.xlsx");
    }

    /**
     * 指定したPaper、および関連付けされたファイルを、論理削除する。また、ファイル状況を確認する。
     */
    public function deletepaper(int $cat_id, Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc')) {
            if (!auth()->user()->can('manage_cat', $cat_id)) abort(403);
        }
        $all = Paper::withTrashed()->where("category_id", $cat_id)->orderBy('deleted_at', 'asc')->orderBy('id')->get();
        if ($req->has("action")) {
            foreach ($req->input("pid") as $n => $pid) {
                $paper = Paper::withTrashed()->find($pid);
                if ($paper != null) {
                    if ($req->input("action") == "revoke") {
                        $paper->deleted_at = null;
                        $paper->save();
                        $mes = "復活";
                    } else if ($req->input("action") == "delete") {
                        $paper->softdelete_me();
                        $mes = "論理削除";
                    }
                }
            }
            return redirect()->route('admin.deletepaper', ['cat' => $cat_id])->with('feedback.success', '投稿を' . $mes . 'しました');
        }
        return view('admin.deletepaper')->with(compact("all", "cat_id"));
    }
    public function timestamp(int $cat_id, Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc')) {
            if (!auth()->user()->can('manage_cat', $cat_id)) abort(403);
        }
        if ($req->has("action")) {
            foreach ($req->input("pid") as $n => $pid) {
                $paper = Paper::withTrashed()->find($pid);
                if ($paper != null) {
                    if ($req->input("action") == "revoke") {
                        $paper->deleted_at = null;
                        $paper->save();
                        $mes = "復活";
                    } else if ($req->input("action") == "delete") {
                        $paper->softdelete_me();
                        $mes = "論理削除";
                    }
                }
            }
            return redirect()->route('admin.timestamp', ['cat' => $cat_id])->with('feedback.success', '投稿を' . $mes . 'しました');
        }
        $all = Paper::withTrashed()->where("category_id", $cat_id)->orderBy('deleted_at', 'asc')->orderBy('id')->get();

        $now = date("Y-m-d H:i:s");
        $before24h = date("Y-m-d H:i:s", strtotime($now) - 24 * 60 * 60);
        // 24時間経過後の投稿で、PDFファイルなし、タイトルなしを抽出。
        $past = Paper::where("category_id", $cat_id)->whereNull("title")->where('created_at', '<', $before24h)
            ->whereNull('pdf_file_id')
            ->select('id', 'owner')->pluck('owner', 'id')->toArray();
        return view('admin.timestamp')->with(compact("all", "cat_id", "past"));
    }


    /**
     * 情報学広場
     */
    public function hiroba_excel()
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        // Formからのカテゴリ選択を配列にいれる
        $targets =  [1, 2, 3];
        return Excel::download(new PapersExport4Hiroba(), "hiroba.xlsx");
    }


    /**
     * ZIP file download for PC
     */
    public function zipdownloadstream(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc')) {
            if (!auth()->user()->can('manage_cat_any')) abort(403);
        }
        // Formからのカテゴリ選択を配列にいれる
        $targets = [];
        $filetypes = []; // pdf, video, img, altpdf
        foreach ($req->all() as $k => $v) {
            if (strpos($k, "targetcat") === 0) $targets[] = $v;
            if (strpos($k, "filetype") === 0) $filetypes[] = $v;
        }
        if (count($targets) > 0) {
            // find Target Papers
            $papers = Paper::whereIn('category_id', $targets)->get();
            $zipFN = 'files.zip';
            $zipstream = Zip::create($zipFN);
            foreach ($papers as $paper) {
                $paper->addFilesToZipStream($zipstream, $filetypes);
            }
            // Zipアーカイブをストリーミングでダウンロード
            return $zipstream;
        }
        return response()->json(['message' => 'ここは実行されない。'], 500);
        // return view('admin.zipdownload')->with(compact("targets","filetypes"));
    }
    /**
     * ZIP file download for PC
     */
    public function zipdownload(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc')) {
            if (!auth()->user()->can('manage_cat_any')) abort(403);
        }
        // Formからのカテゴリ選択を配列にいれる
        $targets = [];
        $filetypes = []; // pdf, video, img, altpdf
        foreach ($req->all() as $k => $v) {
            if (strpos($k, "targetcat") === 0) $targets[] = $v;
            if (strpos($k, "filetype") === 0) $filetypes[] = $v;
        }
        if (count($targets) > 0) {
            // find Target Papers
            $papers = Paper::whereIn('category_id', $targets)->get();
            $zipFN = 'files.zip';
            $zipFP = storage_path('app/' . $zipFN);
            $zip = new ZipArchive();
            if ($zip->open($zipFP, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
                foreach ($papers as $paper) {
                    $paper->addFilesToZip($zip, $filetypes);
                }
                $zip->close();

                // Zipアーカイブをダウンロード
                return response()->download($zipFP)->deleteFileAfterSend(true);
            } else {
                return response()->json(['message' => 'Zipファイルを作成できませんでした。'], 500);
            }
        }
        return response()->json(['message' => 'ここは実行されない。'], 500);
        // return view('admin.zipdownload')->with(compact("targets","filetypes"));
    }

    /**
     * ZIP file download for PC by paper ids （登壇不採択→デモ投稿をデモ担当者が確認するため）
     */
    public function zipdownloadstream_paperids(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc')) {
            if (!auth()->user()->can('manage_cat_any')) abort(403);
        }
        $pid_csv = $req->input("pid_csv");
        $pid_array = explode(",", $pid_csv);
        $filetypes = ['pdf'];
        if (count($pid_array) > 0) {
            // find Target Papers
            $papers = Paper::whereIn('id', $pid_array)->get();
            $zipFN = 'files.zip';
            $zipstream = Zip::create($zipFN);
            foreach ($papers as $paper) {
                $paper->addFilesToZipStream($zipstream, $filetypes);
            }
            // Zipアーカイブをストリーミングでダウンロード
            return $zipstream;
        }
        return response()->json(['message' => 'ここは実行されない。'], 500);
    }



    /**
     * ファイルの状況を表示する。また、ロックをしたり、解除したりする。
     */
    public function filelist(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        // Formからのカテゴリ選択を配列にいれる
        $targets = [];
        $filetypes = []; // pdf, video, img, altpdf
        foreach ($req->all() as $k => $v) {
            if (strpos($k, "targetcat") === 0) $targets[] = $v;
            if (strpos($k, "filetype") === 0) $filetypes[] = $v;
        }
        return view('admin.filelist')->with(compact("targets", "filetypes"));
    }

    public static function column_details($tableName)
    {
        $driver = DB::connection()->getDriverName();
        $coldetails = [];
        if ($driver === 'sqlite') {
            $columns = DB::select("pragma table_info('{$tableName}')");
            foreach ($columns as $cc) {
                $coldetails[$cc->name] = $cc->type;
            }
        } else if ($driver === 'mysql') {
            $columns = DB::select("show full columns from `{$tableName}`");
            // カラム名とデータ型の取得
            foreach ($columns as $colary) {
                $coldetails[$colary->Field] = $colary->Type;
            }
        }
        // Type() のかっこ以下は取り除く
        foreach ($coldetails as $f => $t) {
            $pos = strpos($t, '(');
            if ($pos > 0) {
                $coldetails[$f] = substr($t, 0, $pos);
            }
        }
        return $coldetails;
    }
    public static function get_db_tables()
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'sqlite' || $driver === 'sqlite_testing') {
            $_tables = DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'");
            $tables = array_map(function ($item) {
                return $item->name;
            }, $_tables);
        } else if ($driver === 'mysql') {
            $_tables = DB::select('SHOW TABLES');
            $tables = [];
            foreach ($_tables as $nnn => $obj) {
                foreach ($obj as $nnnn => $tn) {
                    $tables[] = $tn;
                }
            } // ここは1回しかまわらないはず
        }
        sort($tables);
        return $tables;
    }
    public static function get_table_comments($dbName, $tableName)
    {
        $driver = DB::connection()->getDriverName();
        $coldetails = [];
        if ($driver === 'sqlite') {
            $columns = DB::select("pragma table_info('{$tableName}')");
            foreach ($columns as $cc) {
                $coldetails[$cc->name] = $cc->name;
            }
        } else if ($driver === 'mysql') {
            $columns = DB::select("SELECT COLUMN_NAME, COLUMN_COMMENT FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '{$dbName}' AND TABLE_NAME = '{$tableName}'");
            // カラム名とデータ型の取得
            foreach ($columns as $colary) {
                $coldetails[$colary->COLUMN_NAME] = $colary->COLUMN_COMMENT;
            }
        }
        return $coldetails;
    }
    public function crud(Request $req)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc')) abort(403);
        //テーブル指定があるか？
        // $connection = config('database.default');
        $driver = DB::connection()->getDriverName();
        // dd($driver);
        if ($req->has('table')) {
            $tableName = $req->table;
            $coldetails = $this->column_details($tableName); //テーブルのスキーマ情報

            $whereBy = [];
            $row = null;
            if ($req->isMethod("post")) {
                $all = $req->all();
                foreach ($all as $fname => $fval) {
                    if (strpos($fname, "whereBy__") === 0 && $fval != null) {
                        $whereBy[substr($fname, 9)] = $fval;
                    }
                }
            }
            if ($req->has('row')) { //単一行編集モード
                $whereBy['id'] = $req->input('row');
                $row = $req->input('row');
            }
            if (count($whereBy) == 0) {
                if (isset($coldetails['created_at'])) {
                    $data = DB::table($tableName)->orderByDesc('created_at')->limit(100)->get()->toArray();
                } else if (isset($coldetails['id'])) {
                    $data = DB::table($tableName)->orderBy('id')->limit(100)->get()->toArray();
                } else {
                    $data = DB::table($tableName)->limit(100)->get()->toArray();
                }
                $numdata = DB::table($tableName)->count();
            } else {
                $query = DB::table($tableName);
                foreach ($whereBy as $fn => $fv) {
                    $lowertype = strtolower($coldetails[$fn]);
                    if ($lowertype == 'integer') {
                        $query = $query->where($fn, '=', $fv);
                    } else {
                        $query = $query->where($fn, 'LIKE', '%' . $fv . '%');
                    }
                }
                if (isset($coldetails['orderint'])) {
                    $query = $query->orderBy('orderint');
                }
                $numdata = $query->count();
                $data = $query->limit(100)->get()->toArray();
            }
            $view = ($req->has('row')) ? 'admin.crudrow' : 'admin.crudtable';
            return view($view)->with(compact("tableName", "coldetails", "data", "whereBy", "numdata", "row"));
        } else {
            // dump($connection);
            $tables = $this->get_db_tables();
            $tableDataCounts = [];
            foreach ($tables as $tn) {
                // テーブルのデータ数を取得
                $tableDataCount = DB::table($tn)->count();
                // テーブル名とデータ数を格納
                $tableDataCounts[$tn] = $tableDataCount;
            }
            // dd($tableDataCounts);
            return view('admin.cruddb')->with(compact("tables", "tableDataCounts"));
        }
    }
    /**
     * crud table編集
     */
    public function crudpost(Request $req)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc|pub|demo|web')) abort(403); // Note: 出版担当もbibinfochkから修正できる。
        if ($req->input("dtype") == "tinyint") {
            $row = DB::select("SELECT `{$req->input("field")}` as field FROM {$req->input("table")} WHERE id={$req->input("data_id")} limit 1");
            $currentVal = intval($row[0]->field);
            $newVal = 1 - intval($currentVal);
            $updateQuery = "UPDATE {$req->input("table")} set `{$req->input("field")}` = :value where id = :id";
            DB::statement($updateQuery, ['value' => $newVal, 'id' => $req->input("data_id")]);
            return "TOGGLE {$newVal} {$req->input("tdid")}";
        } else {
            $updateQuery = "UPDATE {$req->input("table")} set `{$req->input("field")}` = :value where id = :id";
            DB::statement($updateQuery, ['value' => $req->input("val"), 'id' => $req->input("data_id")]);
            return "OK {$req->input("tdid")}";
        }
    }
    public function crudnew(Request $req)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc')) abort(403);
        $tableName = $req->input("table");
        $eloModelName = 'App\\Models\\' . Str::studly(Str::singular($tableName)); //　studly でUpperCamelCaseにする
        if (class_exists($eloModelName)) {
            eval("{$eloModelName}::factory()->create();");
        }
        return redirect()->route('admin.crud', ['table' => $tableName]);
    }
    public function crudtruncate(Request $req)
    {
        if (!auth()->user()->can('role_any', 'admin|manager')) abort(403);
        $tableName = $req->input("table");
        $eloModelName = 'App\\Models\\' . Str::studly(Str::singular($tableName)); //　studly でUpperCamelCaseにする
        if (class_exists($eloModelName)) {
            eval("{$eloModelName}::truncate();");
        } else {
            DB::statement("TRUNCATE TABLE {$tableName}");
        }
        return redirect()->route('admin.crud', ['table' => $tableName]);
    }
    public function crudcopy(Request $req)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc')) abort(403);
        $tableName = $req->input("table");
        $row = $req->input("row");
        $eloModelName = 'App\\Models\\' . Str::studly(Str::singular($tableName)); //　studly でUpperCamelCaseにする
        if (class_exists($eloModelName)) {
            eval("\$datum = {$eloModelName}::find({$row});");
            if (isset($datum)) {
                $newdatum = $datum->replicate(); // copy data
                $newdatum->save();
                return redirect()->route('admin.crud', ['table' => $tableName, 'row' => $newdatum->id]);
            }
        }
        return redirect()->route('admin.crud', ['table' => $tableName]);
    }
    public function cruddelete(Request $req)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc')) abort(403);
        $tableName = $req->input("table");
        $row = $req->input("row");
        $eloModelName = 'App\\Models\\' . Str::studly(Str::singular($tableName)); //　studly でUpperCamelCaseにする
        if (class_exists($eloModelName)) {
            eval("\$datum = {$eloModelName}::find({$row});");
            if (isset($datum)) {
                $datum->delete();
            }
        }
        return redirect()->route('admin.crud', ['table' => $tableName]);
    }


    public function crudajax(Request $req)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc')) abort(403);
        if ($req->has('table')) {
            $table = $req->input('table');
            $model = Str::studly(Str::singular($table));
            return view('admin.crudajax')->with(compact("model"));
        } else {
            return redirect('admin.cruddb');
        }
    }

    public function crudchkdelete(Request $req)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|ec')) abort(403);
        $tableName = $req->input("table");
        $eloModelName = 'App\\Models\\' . Str::studly(Str::singular($tableName)); //　studly でUpperCamelCaseにする
        if (class_exists($eloModelName)) {
            $dids = $req->input('did');
            $eloModelName::whereIn('id', $dids)->forceDelete();
        }
        return redirect()->route('admin.crud', ['table' => $tableName]);
    }

    /** カテゴリごとの査読進行管理設定 */
    public function catsetting(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        $coldetails = $this->column_details('categories');
        $note = null;
        if ($req->has("toukou")) { // 投稿関係
            $ary = [
                'name',
                'pdf_page_min',
                'pdf_page_max',
                'pdf_accept_start',
                'pdf_accept_end',
                'pdf_accept_revise',
                'can_upload_not_accepted',
                'pdf_disable_delete', // PDFファイルの削除を禁止する
                'openstart',
                'openend',
                'upperlimit',
                'show_bibinfo_btn',
                'extract_title'
            ];
            $cold2 = [];
            foreach ($ary as $f) {
                $cold2[$f] = $coldetails[$f];
            }
            $coldetails = $cold2;
            $title = "投稿受付管理";
            $note = "査読中（revedit_on = 1 && revreturn_on = 0）は「書誌情報の設定ボタンを表示する」を設定しても、著者の編集画面に表示しません。";
        } else if ($req->has("mandatoryfile")) { // 必須ファイル関係
            $ary = ['name', 'accept_video', 'accept_pptx', 'accept_img', 'img_max_width', 'img_max_height', 'accept_altpdf', 'altpdf_page_min', 'altpdf_page_max', 'altpdf_accept_start', 'altpdf_accept_end'];
            $cold2 = [];
            foreach ($ary as $f) {
                $cold2[$f] = $coldetails[$f];
            }
            $coldetails = $cold2;
            $title = "サプリメントファイル受付管理";
        } else if ($req->has("leadtext")) { // 必須ファイル関係
            $ary = ['name', 'leadtext', 'midtext'];
            $cold2 = [];
            foreach ($ary as $f) {
                $cold2[$f] = $coldetails[$f];
            }
            $coldetails = $cold2;
            $title = "カテゴリ固有の案内(リード文など)";
        } else { // 査読関係
            foreach ($coldetails as $field => $type) {
                if (strpos($field, "status__") !== 0 && $field != 'name') {
                    unset($coldetails[$field]);
                }
            }
            $title = "査読進行管理";
            $note = "査読中（revedit_on = 1 && revreturn_on = 0）は「書誌情報の設定ボタン」や「回答可能なアンケート」を著者の編集画面に表示しません。<br>著者が関係する掲示板リンクは、revreturn_on = 1 のときに表示します。revbb_onは影響しません。";
        }

        $domain = config('database.default');
        $db_name = config('database.connections.' . str_replace('.', '_', $domain) . '.database');

        $whereBy = [];
        $tableName = 'categories';
        $tableComments = $this->get_table_comments($db_name, $tableName);
        $data = DB::table($tableName)->orderBy('id')->limit(100)->get()->toArray();
        $numdata = DB::table($tableName)->count();
        return view('admin.crudtable2')->with(compact("tableName", "coldetails", "data", "whereBy", "numdata", "tableComments", "title", "note"));
    }

    /**
     * RevConflict を truncate する。
     */
    public function resetbidding()
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        RevConflict::truncate();
        return redirect()->route('role.top', ['role' => 'admin'])->with('feedback.success', '利害表明とBiddingをすべてリセットしました');
    }
    /**
     * UserのsoftDeleted を 完全削除 する。
     */
    public function forcedelete()
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        User::onlyTrashed()->whereNotNull('id')->forceDelete();
        return redirect()->route('role.top', ['role' => 'admin'])->with('feedback.success', 'User softDeleted を完全削除しました');
    }

    /**
     * AWARDJSON_DLKEY の生成。ただし、先頭4文字がCONFTITLE_YEARと異なっている場合のみ。
     */
    public function gen_dlkey()
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        // 現在の設定
        $current = Setting::getval("AWARDJSON_DLKEY");
        // 現在の年設定
        $year = Setting::getval("CONFTITLE_YEAR");
        if (substr($current, 0, 4) == $year) {
            return redirect()->route('role.top', ['role' => 'admin'])->with('feedback.error', '今年のAWARDJSON_DLKEYはすでに生成されているため、生成をキャンセルしました。');
        }
        $temporal_key = Setting::getval("CONFTITLE_YEAR") . Str::random(10);
        Setting::setval("AWARDJSON_DLKEY", $temporal_key);

        return redirect()->route('role.top', ['role' => 'admin'])->with('feedback.success', 'ダウンロードキーを生成しました。');
    }


    /**
     * 投稿をすべてリセットする。ファイルも消す。ログも消す。
     */
    public function resetpaper()
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);

        $all = File::all();
        foreach ($all as $f) {
            $f->remove_the_file();
            $f->delete_me();
        }
        Paper::truncate();
        Contact::truncate();
        EnqueteAnswer::truncate();
        Submit::truncate();
        RevConflict::truncate();
        Review::truncate();
        Score::truncate();
        Affil::truncate();
        DB::table('paper_contact')->truncate();

        LogModify::truncate();
        // LogAccess::truncate();
        LogCreate::truncate();
        LogForbidden::truncate();

        Bb::truncate();
        BbMes::truncate();

        return redirect()->route('role.top', ['role' => 'admin'])->with('feedback.success', '投稿をすべてリセットしました');
    }

    /**
     * 参加登録をすべてリセットする。registsテーブルをtruncateする。
     */
    public function resetregist()
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        Regist::truncate();
        return redirect()->route('role.top', ['role' => 'admin'])->with('feedback.success', '参加登録(regists)をすべてリセットしました');
    }
    /**
     * アクセスログをすべてリセットする。log_accessテーブルをtruncateする。
     */
    public function resetaccesslog()
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        LogAccess::truncate();
        return redirect()->route('role.top', ['role' => 'admin'])->with('feedback.success', 'アクセスログをすべてリセットしました');
    }

    /**
     * ユーザ管理
     */
    public function users()
    {
        if (!auth()->user()->can('role_any', 'admin|manager')) abort(403);
        return view('admin.users');
    }

    /**
     * 必要なプログラムがインストールされているか？の確認
     */
    public function check_exefiles()
    {
        $in = [
            "pdftoppm -v",
            "convert -version",
            "md5sum --version",
            "file -v",
            "pdfinfo -v",
            "node -v",
            "npm -v",
            "ffmpeg -version",
            "composer -V",
            "tesseract -v",
            "tesseract --list-langs",
            "php -i"
        ];
        $out = [];
        foreach ($in as $com) {
            $out[$com] = shell_exec($com . " 2>&1");
        }
        return view('admin.chkexefiles')->with(compact("in", "out"));
    }

    /**
     * dump sql for backup
     */
    public function passdumpsql(Request $req)
    {
        if (!auth()->user()->can('role', 'admin')) abort(403);
        $pass = Str::random(30);
        if ($req->has("password")) $pass = $req->input("password");
        $app_public_filedir = storage_path(File::apf());
        $mysql = config('database.default');
        $db_name = config('database.connections.' . str_replace('.', '_', $mysql) . '.database');
        //フォルダがなければ作成する
        if (!file_exists($app_public_filedir)) {
            mkdir($app_public_filedir, 0777, true);
        }
        chdir($app_public_filedir);
        shell_exec("mysqldump -u {$db_name} -p{$db_name} {$db_name} > dump.sql");
        shell_exec("zip -e --password={$pass} passdumpsql.zip dump.sql");
        return response()->file(
            $app_public_filedir . "/passdumpsql.zip",
            [
                'Content-Disposition' => 'attachment; filename="passdumpsql.zip"',
                'X-Exconf-DumpPass' => '"' . $pass . '"'
            ]
        );
    }
}
