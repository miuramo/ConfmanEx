<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAnnotRequest;
use App\Http\Requests\UpdateAnnotRequest;
use App\Models\Annot;
use App\Models\AnnotPaper;
use App\Models\Paper;

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
        //
        return view('annot.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAnnotRequest $request)
    {
        $paper = Paper::find($request->paper_id);
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
        $apaper = AnnotPaper::find($annopaper)->first();
        return view('annot.show')->with(compact('apaper'));
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Annot $annot)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAnnotRequest $request, Annot $annot)
    {
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
