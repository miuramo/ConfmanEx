<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBbMesRequest;
use App\Http\Requests\UpdateBbMesRequest;
use App\Mail\BbNotify;
use App\Models\Bb;
use App\Models\BbMes;
use App\Models\File;
use App\Models\Setting;
use Illuminate\Http\Request;

class BbMesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(Request $req, int $bbid, string $key2)
    {
        // check bb
        $key = $req->input("key");
        $bb = Bb::with("paper")->where('id', $bbid)->where('key', $key)->first();
        if ($bb == null) abort(403, 'bb not found');
        if (strlen($req->input("mes")) < 1) {
            return redirect()->route('bb.show', ['bb' => $bbid, 'key' => $key2])->with('feedback.error', "メッセージを入力してください。");
        }
        $bbmes = BbMes::create([
            'bb_id' => $bb->id,
            'user_id' => auth()->id(),
            'subject' => $req->input("sub"),
            'mes' => $req->input("mes"),
        ]);

        if ($req->has('bbfile')) {
            $tmp = $req->file("bbfile");
            // もし、bb->paper->owner == auth()->id() なら、paperに紐付ける
            if ($bb->paper->owner == auth()->id()) {
                $file = File::createnew($tmp, $bb->paper->id);
            } else {
                $file = File::createnew($tmp);
            }
            $file->bb_mes_id = $bbmes->id;
            $file->pending = 1;
            $file->save();
        } else {
        }

        //メール通知
        (new BbNotify($bb, $bbmes))->process_send();

        return redirect()->route('bb.show', ['bb' => $bbid, 'key' => $key2])->with('feedback.success', "書き込みました。関係者にメールで通知しました。");
    }

    /**
     * 掲示板にアップロードされたファイルを採用する
     */
    public function adopt(Request $req, int $bbid, string $key)
    {
        if (!auth()->user()->can('role_any', 'admin|manager|pc|pub|web|demo|metareviewer')) abort(403, 'Unauthorized action');
        $bb = Bb::with("paper")->with("category")->where('id', $bbid)->where('key', $key)->first();
        if ($bb == null) abort(403, 'bb not found');

        // もし、取り下げなら、invalid & deleted にする
        if ($req->action == "reject") {
            $file_id = $req->input("file_id");
            $rejectfile = \App\Models\File::find($file_id);
            $rejectfile->valid = 0;
            $rejectfile->deleted = 1;
            $rejectfile->pending = 0;
            $rejectfile->save();
            $memo = "(fileid={$file_id}, name={$rejectfile->origname}) について、未採用＆措置済みにしました。\n";
            $bbmes = BbMes::create([
                'bb_id' => $bb->id,
                'user_id' => auth()->id(),
                'subject' => "未採用ファイルを措置済みにしました",
                'mes' => $memo,
            ]);
            //メール通知
            (new BbNotify($bb, $bbmes))->process_send();
    
            return redirect()->route('bb.show', ['bb' => $bbid, 'key' => $key])->with('feedback.success', "ファイルを未採用＆措置済みにし、本掲示板にその旨を通知しました。");    
        }


        $file_desc = Setting::getval('FILE_DESCRIPTIONS');
        $ft = json_decode($file_desc, true);

        $file_id = $req->input("file_id");
        $newfile = \App\Models\File::find($file_id);
        $ftype = $req->input("ftype");
        // 記録のため、差し替えの場合は、以前のファイルIDを記録
        if ($ftype == "pdf") {
            $old_file_id = $bb->paper->pdf_file_id;
            $bb->paper->pdf_file_id = $file_id;
        } else if ($ftype == "img") {
            $old_file_id = $bb->paper->img_file_id;
            $bb->paper->img_file_id = $file_id;
        } else if ($ftype == "video") {
            $old_file_id = $bb->paper->video_file_id;
            $bb->paper->video_file_id = $file_id;
        } else if ($ftype == "altpdf") {
            $old_file_id = $bb->paper->altpdf_file_id;
            $bb->paper->altpdf_file_id = $file_id;
        } else {
            $old_file_id = null;
        }
        // 論文情報を保存
        $bb->paper->save();
        $memo = "PaperID:{$bb->paper->id} の {$ft[$ftype]}ファイルを差し替えました。(new fileid={$file_id})  ";
        // 新しいファイルのPaperIDをセット
        $newfile->paper_id = $bb->paper->id;
        $newfile->pending = 0;
        $newfile->save();
        // もし古いファイルがあれば、deleted = 1 にする
        if ($old_file_id) {
            $oldfile = \App\Models\File::find($old_file_id);
            $oldfile->deleted = 1;
            $oldfile->save();
            $memo .= "(old fileid={$old_file_id})\n";
        } else {
            $memo .= "以前の{$ft[$ftype]}ファイルはありませんでした。\n";
        }

        $bbmes = BbMes::create([
            'bb_id' => $bb->id,
            'user_id' => auth()->id(),
            'subject' => "{$ft[$ftype]}ファイルを差し替えました",
            'mes' => $memo,
        ]);
        //メール通知
        (new BbNotify($bb, $bbmes))->process_send();

        return redirect()->route('bb.show', ['bb' => $bbid, 'key' => $key])->with('feedback.success', "ファイルを採用し、本掲示板にその旨を通知しました。");
    }


    /**
     * Display the specified resource.
     */
    public function show(BbMes $bbMes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BbMes $bbMes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBbMesRequest $request, BbMes $bbMes)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BbMes $bbMes)
    {
        //
    }
}
