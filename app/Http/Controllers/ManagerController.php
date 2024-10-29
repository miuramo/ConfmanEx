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
        return redirect()->route('admin.dashboard');
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




    public function mailtest()
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        if (!auth()->user()->id == 1) abort(403);
        $papers = Paper::all();
        $mts = MailTemplate::all();
        foreach ($mts as $mt) {
            foreach ($papers as $paper) {
                (new ForAuthor($paper, $mt))->process_send();
                // Mail::send(new ForAuthor($paper, $mt));
            }
        }
        return redirect()->route('admin.admindb');
    }

    public function test9w()
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        Test9w::dispatch();
        // $this->ocr9w();
        ExportHintFileJob::dispatch();
        return redirect()->route('admin.dashboard')->with('feedback.success', 'テストQueueを実行しました。再読み込みして各種設定→LAST_QUEUEWORK_DATEが更新されていることを確認してください。');
    }

    public function ocr9w()
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        File::rebuildOcrTsv();
        // OcrJob::dispatch();
        return redirect()->route('admin.dashboard')->with('feedback.success', 'OCR Queueを実行しました。');
    }
    
    public function paperauthorhead(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc')) abort(403);
        $sets = Setting::where("name", "like", "SKIP_HEAD_%")->where("valid", true)->get();
        $papers = Paper::whereNotNull("pdf_file_id")->get();
        if ($req->input('action')=='titleupdate'){ // 第3要素のタイトルで書き換える
            foreach($papers as $paper){
                $title = $paper->title_candidate();
                foreach($sets as $set){
                    $title = str_replace($set->value,"",$title);
                }
                // authorheadが含まれていたら
                $pos = mb_strpos($title, mb_substr($paper->authorhead,0,2));
                if ( $pos > -1 && $pos !== false){
                    $title = mb_substr($title, 0, $pos);
                }
                $paper->title = $title;
                $paper->save();
            }
            return redirect()->route('admin.paperauthorhead')->with('feedback.success', 'タイトルを一括更新しました');
        }
        if ($req->input('action')=='setfirstauthor_ifnull'){ // ★★第一著者未設定★★ について、第一著者の苗字を設定する
            foreach($papers as $paper){
                if (mb_strlen($paper->authorhead) < 1) {
                    $myouji = explode(" ",$paper->paperowner->name)[0];
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

        return view('admin.paperauthorhead')->with(compact("papers","sets"));
    }

}
