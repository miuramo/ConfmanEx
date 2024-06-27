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
use App\Models\Viewpoint;
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

class AdminController extends Controller
{
    public function dashboard()
    {
        if (!auth()->user()->can('role_any', 'admin|manager')) abort(403);

        $roles = auth()->user()->roles;
        $roleall = Role::all();

        Setting::firstOrCreate([
            'name' => "NAME_OF_META",
        ], [
            'value' => "メタ査読者",
            'isnumber' => false,
            'isbool' => false,
        ]);
        Setting::firstOrCreate([
            'name' => "SKIP_BIBINFO",
        ], [
            'value' => '["keyword","etitle","eabst","ekeyword"]',
            'isnumber' => false,
            'isbool' => false,
        ]);
        Setting::firstOrCreate([
            'name' => "FILEPUT_DIR",
        ], [
            'value' => "z2024",
            'isnumber' => false,
            'isbool' => false,
        ]);
        Setting::firstOrCreate([
            'name' => "PC_MEMBER",
        ], [
            'value' => "",
            'isnumber' => false,
            'isbool' => false,
        ]);
        Setting::firstOrCreate([
            'name' => "REVIEWER_MEMBER",
        ], [
            'value' => "",
            'isnumber' => false,
            'isbool' => false,
        ]);
        // 表彰状用JSON のダウンロードキー
        $temporal_key = Setting::findByIdOrName("CONFTITLE_YEAR", "value") . Str::random(10);
        Setting::firstOrCreate([
            'name' => "AWARDJSON_DLKEY",
        ], [
            'value' => $temporal_key,
            'misc' => "表彰状生成用JSON Download Key",
            'isnumber' => false,
            'isbool' => false,
        ]);
        Setting::firstOrCreate([
            'name' => "LAST_QUEUEWORK_DATE",
        ], [
            'value' => "(TestQueueWork未実行)",
            'isnumber' => false,
            'isbool' => false,
        ]);
        Setting::firstOrCreate([
            'name' => "TUTORIAL_URL",
        ], [
            'value' => "https://exconf.istlab.info/SSS_tutorial.mp4",
            'isnumber' => false,
            'isbool' => false,
        ]);
        Setting::firstOrCreate([
            'name' => "CROP_YHWX",
        ], [
            'value' => "[80,500, 1100,-1]",
            'isnumber' => false,
            'isbool' => false,
            'misc' => '最後のXが負数だとセンタリング計算でXを求める'
        ]);

        // Viewpoint::change_separator();

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
            return redirect()->route('admin.dashboard')->with('feedback.error', '無効にしたいメールアドレスを入力してください。');
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

        return redirect()->route('admin.dashboard')->with('feedback.success', 'すべてのPaperの投稿連絡用メールアドレスから削除しました。' . implode(",", $ids));
    }

    public function paperlist(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        // Formからのカテゴリ選択を配列にいれる
        $targets = [];
        foreach ($req->all() as $k => $v) {
            if (strpos($k, "targetcat") === 0) $targets[] = $v;
        }
        if (count($targets) == 0) $targets =  [1, 2, 3];

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
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        // Formからのカテゴリ選択を配列にいれる
        $targets = [];
        foreach ($req->all() as $k => $v) {
            if (strpos($k, "targetcat") === 0) $targets[] = $v;
        }
        if (count($targets) == 0) $targets =  [1, 2, 3];
        $target_str = implode("", $targets);
        return Excel::download(new PapersExportFromView($targets), "paperlist_{$target_str}.xlsx");
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
    public function zipdownload(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
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

    private function column_details($tableName)
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
    private function get_db_tables()
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
    private function get_table_comments($dbName, $tableName)
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
        if (!auth()->user()->can('role_any', 'admin|manager|pc|pub')) abort(403); // Note: 出版担当もbibinfochkから修正できる。
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


    /** カテゴリごとの査読進行管理設定 */
    public function catsetting(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        $coldetails = $this->column_details('categories');
        if ($req->has("toukou")) { // 投稿関係
            $ary = ['name', 'pdf_page_min', 'pdf_page_max', 'pdf_accept_start', 'pdf_accept_end', 'pdf_accept_revise', 'openstart', 'openend', 'upperlimit'];
            $cold2 = [];
            foreach ($ary as $f) {
                $cold2[$f] = $coldetails[$f];
            }
            $coldetails = $cold2;
            $title = "投稿受付管理";
        } else { // 査読関係
            foreach ($coldetails as $field => $type) {
                if (strpos($field, "status__") !== 0 && $field != 'name') {
                    unset($coldetails[$field]);
                }
            }
            $title = "査読進行管理";
        }

        $domain = config('database.default');
        $db_name = config('database.connections.' . str_replace('.', '_', $domain) . '.database');

        $whereBy = [];
        $tableName = 'categories';
        $tableComments = $this->get_table_comments($db_name, $tableName);
        $data = DB::table($tableName)->orderBy('id')->limit(100)->get()->toArray();
        $numdata = DB::table($tableName)->count();
        return view('admin.crudtable2')->with(compact("tableName", "coldetails", "data", "whereBy", "numdata", "tableComments", "title"));
    }

    /**
     * RevConflict を truncate する。
     */
    public function resetbidding()
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        RevConflict::truncate();
        return redirect()->route('admin.dashboard')->with('feedback.success', '利害表明とBiddingをすべてリセットしました');
    }
    /**
     * UserのsoftDeleted を 完全削除 する。
     */
    public function forcedelete()
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        User::onlyTrashed()->whereNotNull('id')->forceDelete();
        return redirect()->route('admin.dashboard')->with('feedback.success', 'User softDeleted を完全削除しました');
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
        DB::table('paper_contact')->truncate();

        LogModify::truncate();
        LogAccess::truncate();
        LogCreate::truncate();
        LogForbidden::truncate();

        Bb::truncate();
        BbMes::truncate();

        return redirect()->route('admin.dashboard')->with('feedback.success', '投稿をすべてリセットしました');
    }

    /**
     * 必要なプログラムがインストールされているか？の確認
     */
    public function check_exefiles()
    {
        $in = [
            "pdftoppm -v", "convert -version", "md5sum --version", "file -v", "pdfinfo -v", "node -v", "npm -v",
            "composer -V", "tesseract -v", "tesseract --list-langs", "php -i"
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
