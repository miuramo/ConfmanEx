<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMailTemplateRequest;
use App\Http\Requests\UpdateMailTemplateRequest;
use App\Models\Accept;
use App\Models\MailTemplate;
use App\Models\Paper;
use App\Models\Submit;
use Illuminate\Http\Request;
use Illuminate\Mail\Markdown;
use App\Mail\ForAuthor;

class MailTemplateController extends Controller
{
    /**
     * Display a listing of the resource.
     * List
     */
    public function index()
    {
        if (!auth()->user()->can('role_any', 'manager|pc')) {
            if (!auth()->user()->can('manage_cat_any')) abort(403);
        }
        $mts = MailTemplate::orderBy('updated_at', 'desc')->get();
        return view('mailtempre.index')->with(compact("mts"));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMailTemplateRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     * Preview and send
     */
    public function show(Request $req, MailTemplate $mt)
    {
        if (!auth()->user()->can('role_any', 'manager|pc')) {
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
    public function edit(MailTemplate $mailTemplate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMailTemplateRequest $request, MailTemplate $mailTemplate)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MailTemplate $mailTemplate)
    {
        //
    }
    /**
     * まとめてコピー、または削除
     */
    public function bundle(Request $req)
    {
        if (!auth()->user()->can('role_any', 'manager|pc')) {
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
            }
        }
        return redirect()->route('mt.index')->with('feedback.error', "メール雛形を選択してから操作してください。");
    }
}
