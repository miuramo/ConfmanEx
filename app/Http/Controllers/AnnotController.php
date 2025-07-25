<?php

namespace App\Http\Controllers;

use App\Models\Annot;
use App\Models\AnnotPaper;
use App\Models\Paper;
use App\Models\Setting;
use Illuminate\Http\Request;

class AnnotController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('annot.index');
    }

    /**
     * Show the form for creating a new resource.
     * Paperを作成する
     */
    public function create()
    {
        if (!Setting::isTrue('enable_annotpaper')) abort(403, 'AnnotPaper Invalid or Disabled');
        //
        return view('annot.create');
    }

    /**
     * Store a newly created resource in storage.
     * Paperを保存する
     */
    public function store(Request $request)
    {
        $paper = Paper::find($request->paper_id);
        if (!$paper) abort(403);
        AnnotPaper::create([
            'paper_id' => $request->paper_id,
            'user_id' => auth()->id(),
            'file_id' => $paper->pdf_file_id,
        ]);
        return redirect()->route('annot.create')->with('feedback.success', 'AnnotPaperを作成しました');
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(int $annopaper, int $page = 1)
    {
        $apaper = AnnotPaper::find($annopaper);
        if (!$apaper) abort(403);
        if ($apaper->is_public == false && $apaper->user_id != auth()->id()) abort(403, 'AnnotPaper is not public');
        return view('annot.show')->with(compact('apaper', 'page'));
        //
    }
    /**
     * 送信元Pageは annot / num = (show) apaper, apaper has paper_id, user_id, file_id
     * Update the specified resource in storage.
     * Annotationを更新する（保存する。もし無ければ作る）
     */
    public function postsubmit(Request $req, Annot $annot)
    {
        // annot_paper_id, page, content
        // (paper_idはannot_paper_idから取得)
        $apaper = AnnotPaper::find($req->annot_paper_id);
        if (!$apaper) abort(403);
        if ($apaper->is_public == false && $apaper->user_id != auth()->id()) abort(403, 'AnnotPaper is not public');
        $an = Annot::firstOrCreate([
            'annot_paper_id' => $req->annot_paper_id,
            'page' => $req->page,
            'user_id' => auth()->id(),
        ], [
            'iine' => 0,
            'paper_id' => $apaper->paper_id,
        ]);
        $an->content = $req->content;
        $an->save();
        return "OK, saved";
        //
    }
    public function comment_json(int $annopaper, int $page = 1)
    {
        $apaper = AnnotPaper::find($annopaper);
        if (!$apaper) abort(403);
        if ($apaper->is_public == false && $apaper->user_id != auth()->id()) abort(403, 'AnnotPaper is not public');

        $final = $apaper->get_fabric_objects($page);
        return response()->json($final);
        //
    }

    public function setpublic(Request $req, AnnotPaper $annot)
    {
        if ($annot->user_id != auth()->id()) abort(403);
        $annot->is_public = $req->is_public;
        $annot->save();
        return redirect()->route('annot.create', ['annot' => $annot->id])->with('feedback.success', 'AnnotPaperの公開設定を変更しました');
        //
    }


    
    


    
}
