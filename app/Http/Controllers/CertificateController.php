<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CertificateController extends Controller
{
    public function index(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc|pub|web|award')) abort(403);
        return view('certificate.index');
    }
    //
    public function itmsettings(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc|pub|web|award')) abort(403);

        $tableName = 'certificates';
                // copy_id がセットされていたら、行をコピーする
        if ($req->has('copy_id')) {
            DB::transaction(function () use ($req) {
                $copy_id = $req->input('copy_id');
                $enqitm = Certificate::find($copy_id);
                $newdatum = $enqitm->replicate(); // copy data
                $newdatum->orderint++;
                $newdatum->save();
            });
            return redirect()->route('certificate.itmsettings')->with('feedback.success', '項目をコピーしました');
                // ->with('altlink', '<a href="' . route('certificate.itmsettings', ["reorder" => 10, "enq_id" => $enq_id, "enq_name" => $req->input('enq_name')]) . '">orderintを調整</a>');
        }
        // del_id がセットされていたら、行を削除する
        if ($req->has('del_id')) {
            $del_id = $req->input('del_id');
            Certificate::destroy($del_id);
            return redirect()->route('certificate.itmsettings')->with('feedback.success', '項目を削除しました');
        }
        if ($req->has('reorder')) {
            Certificate::reorderint($req->input('reorder') ?? 10); // orderint を再割り当てする
            return redirect()->route('certificate.itmsettings')->with('feedback.success', 'orderintを調整しました');
        }
        $coldetails = AdminController::column_details($tableName);
        $coldetails['COPY'] = 'COPY';
        $ary = ['COPY', 'orderint', 'winner', 'awardname', 'year', 'eventname', 'creator', 'company', 'date', 'content', 'presenter', 'template'];
        $cold2 = [];
        foreach ($ary as $f) {
            if (isset($coldetails[$f])) $cold2[$f] = $coldetails[$f];
        }
        $coldetails = $cold2;
        $title = "表彰状の編集";

        $domain = config('database.default');
        $db_name = config('database.connections.' . str_replace('.', '_', $domain) . '.database');

        $whereBy['id'] = $req->input("id");
        $tableComments = AdminController::get_table_comments($db_name, $tableName);
        $data = DB::table($tableName)->orderBy('orderint')->limit(100)->get()->toArray();
        $numdata = DB::table($tableName)->count();
        $back_link_href = route("certificate.index");
        $back_link_label = "表彰状一覧に戻る";
        return view('admin.crudtable2')->with(compact(
            "tableName",
            "coldetails",
            "data",
            "whereBy",
            "numdata",
            "tableComments",
            "title",
            "back_link_href",
            "back_link_label",
        ));
    }

    public function export(bool $forPrint = false)
    {
        if (!auth()->user()->can('role_any', 'pc|pub|web|award')) abort(403);
        $certificates = Certificate::orderBy('orderint')->get();

        // HTTP loopbackを避けてDB直接呼び出し
        $key = Setting::getval('AWARDJSON_DLKEY');
        $json = app(SubmitController::class)->json_bta($key);
        $boothData = json_decode($json, true);

        $pptxContent = Certificate::exportPPTX($certificates, $boothData, $forPrint);
        // ダウンロード
        $filename = 'certificates_' . date('Ymd_His') . '.pptx';
        return response()->streamDownload(function () use ($pptxContent) {
            echo $pptxContent;
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }
}
