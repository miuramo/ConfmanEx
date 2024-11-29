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
        //
    }

    /**
     * Show the form for creating a new resource.
     * Paperを作成する
     */
    public function create()
    {
        $enabled_annotpaper = Setting::findByIdOrName('enable_annotpaper');
        if (!$enabled_annotpaper->valid) abort(403, 'AnnotPaper Invalid');
        if ($enabled_annotpaper->value !== 'true') abort(403, 'AnnotPaper Disabled');
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
    public function show(int $annopaper)
    {
        $apaper = AnnotPaper::find($annopaper);
        if (!$apaper) abort(403);
        return view('annot.show')->with(compact('apaper'));
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
        $an = Annot::firstOrCreate([
            'annot_paper_id' => $req->annot_paper_id,
            'page' => $req->page,
            'user_id' => auth()->id(),
        ], [
            'iine' => 0,
            'paper_id' => $apaper->paper_id,
        ]);
        info($req->content);
        $an->content = $req->content;
        $an->save();
        return "OK, saved";
        //
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Annot $annot)
    {
        //
    }
    public function update(Request $req, Annot $annot)
    {
        info($req->all());
        return "UPDATE";
        //
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Annot $annot)
    {
        //
    }
}
