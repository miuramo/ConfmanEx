<?php

namespace App\Http\Controllers;

use App\Exports\MailTemplatesExport;
use App\Http\Requests\StoreMailTemplateRequest;
use App\Http\Requests\UpdateMailTemplateRequest;
use App\Imports\MailTemplatesImport;
use App\Models\Accept;
use App\Models\MailTemplate;
use App\Models\Paper;
use App\Models\Submit;
use Illuminate\Http\Request;
use Illuminate\Mail\Markdown;
use App\Mail\ForAuthor;
use App\Models\File;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class MailTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     * List
     */
    public function index(Request $req)
    {
        if (!auth()->user()->can('role_any', 'manager|pc|pub')) {
            if (!auth()->user()->can('manage_cat_any')) abort(403);
        }
        if ($req->has('kw')) {
            $keywords = $req->input('kw');
            $kws = preg_split('/[\x{0020}\x{3000}]+/u', $keywords); // 半角または全角スペースで分割
            $query = MailTemplate::query();
            if (count($kws) > 0) {
                foreach ($kws as $keyword) {
                    $query->where(function ($subQuery) use ($keyword) {
                        $subQuery->where('name', 'like', "%{$keyword}%")
                            ->orWhere('subject', 'like', "%{$keyword}%")
                            ->orWhere('to', 'like', "{$keyword}%")
                            ->orWhere('body', 'like', "%{$keyword}%");
                    });
                }
            }
            $mts = $query->orderBy('updated_at', 'desc')->get();
            return view('mailtempre.index')->with(compact("mts", "keywords"));
        } else {
            $mts = MailTemplate::orderBy('updated_at', 'desc')->get();
            return view('mailtempre.index')->with(compact("mts"));
        }
    }

    

    /**
     * Display the specified resource.
     * Preview and send
     */
    public function show(Request $req, MailTemplate $mt)
    {
        if (!auth()->user()->can('role_any', 'manager|pc|pub')) {
            if (!auth()->user()->can('manage_cat_any')) abort(403);
        }
        $numsend = $mt->numpaper();
        $first_item = $mt->first_item();
        if ($first_item == null) return redirect()->route('mt.index')->with('feedback.error', "Toに該当する投稿がありません。（またはユーザがいません。）");
        $replacetxt = $mt->getreplacetxt($first_item);
        $markdown = Markdown::parse($mt->make_body($replacetxt));
        $subject = $mt->make_subject($replacetxt);
        // dummy
        if ($req->has("dosend")) {
            $targets = $mt->targets();
            if (isset($targets)) foreach ($targets as $target) {
                (new ForAuthor($target, $mt))->process_send();
            }
            $mt->lastsent = date("Y-m-d H:i:s");
            $mt->user_id = auth()->user()->id;
            $mt->save();
            return redirect()->route('mt.show', ['mt' => $mt])->with('feedback.success', "{$numsend}件、送信しました");
        }
        return view('mailtempre.show')->with(compact("mt", "markdown", "subject", "first_item"));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MailTemplate $mt)
    {
        if (!auth()->user()->can('role_any', 'manager|pc|pub')) {
            if (!auth()->user()->can('manage_cat_any')) abort(403);
        }
        return view('mailtempre.edit')->with(compact("mt"));
        //
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $req)
    {
        if (!auth()->user()->can('role_any', 'manager|pc|pub')) {
            if (!auth()->user()->can('manage_cat_any')) abort(403);
        }
        $id = $req->input("id");
        $mt = MailTemplate::find($id);
        if ($mt != null) {
            $mt->subject = $req->input("subject");
            $mt->body = $req->input("body");
            $mt->to = $req->input("to");
            $mt->name = $req->input("name");
            $mt->user_id = auth()->user()->id;
            $mt->save();
            if ($req->expectsJson()) return response()->json(['result' => '保存成功']);
            return redirect()->route('mt.edit', ['mt' => $id])->with('feedback.success', "メール雛形を保存しました。");
        }
        if ($req->expectsJson()) return response()->json(['result' => '保存失敗']);
        return redirect()->route('mt.index')->with('feedback.error', "保存できませんでした。");
        //
    }


    

    
    /**
     * まとめてコピー、または削除、またはExcelExport
     */
    public function bundle(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc|pub')) {
            if (!auth()->user()->can('manage_cat_any')) abort(403);
        }
        // valueがonの要素をあつめる。mt_{mtid}になっているので、とりだす。
        $targetmts = [];
        foreach ($req->all() as $k => $v) {
            if ($v == 'on' && strpos($k, 'mt_') === 0) {
                $mtid = explode("_", $k)[1];
                if (is_numeric($mtid)) $targetmts[] = $mtid;
            }
        }
        if ($req->has("action")) {
            if ($req->input("action") == "delete") {
                if (count($targetmts) > 0) {
                    foreach ($targetmts as $mtid) {
                        MailTemplate::destroy($mtid);
                    }
                    return redirect()->route('mt.index')->with('feedback.success', "メール雛形を削除しました。");
                }
            } else if ($req->input("action") == "copy") {
                if (count($targetmts) > 0) {
                    foreach ($targetmts as $mtid) {
                        MailTemplate::makecopy($mtid);
                    }
                    return redirect()->route('mt.index')->with('feedback.success', "メール雛形をコピーしました。");
                }
            } else if ($req->input("action") == "export") {
                if (count($targetmts) > 0) {
                    return Excel::download(new MailTemplatesExport($targetmts), 'mail_templates.xlsx');
                }
            }
        }
        return redirect()->route('mt.index')->with('feedback.error', "メール雛形を選択してから操作してください。");
    }

    public function import(FormRequest $req)
    {
        $tmp = $req->file("file");
        $hashname = $tmp->hashName();
        $tmp->storeAs(File::pf(), $hashname);
        $fullpath = storage_path(File::apf() . '/' . $hashname);
        Excel::import(new MailTemplatesImport, $fullpath);
        return redirect(route('mt.index'))->with('feedback.success', 'メール雛形をインポートしました。');
    }

    /**
     * メール雛形の絞り込み
     */
    public function mtsearch(Request $req)
    {
        if (!auth()->user()->can('role_any', 'manager|pc|pub')) {
            if (!auth()->user()->can('manage_cat_any')) abort(403);
        }
        if ($req->has('query') && $req->input('query') != '') {
            $search = $req->input('query');
            $kws = preg_split('/[\x{0020}\x{3000}]+/u', $search); // 半角または全角スペースで分割
            $query = DB::table('mail_templates');
            // info("mtsearch: query is " . $search);
            if (count($kws) > 0) {
                foreach ($kws as $keyword) {
                    $query->where(function ($subQuery) use ($keyword) {
                        $subQuery->where('name', 'like', "%{$keyword}%")
                            ->orWhere('subject', 'like', "%{$keyword}%")
                            ->orWhere('to', 'like', "{$keyword}%")
                            ->orWhere('body', 'like', "%{$keyword}%");
                    });
                }
                $results = $query->orderBy('updated_at', 'desc')->get();
            }
            return response()->json(['mt' => $results, 'id' => auth()->id()]);
        } else {
            // info("mtsearch: query not found");
            $query = DB::table('mail_templates');
            $results = $query->orderBy('updated_at', 'desc')->get();
            return response()->json(['mt' => $results, 'id' => auth()->id()]);
        }
        return view('mt.index'); //基本的にはつかわない
    }
}
