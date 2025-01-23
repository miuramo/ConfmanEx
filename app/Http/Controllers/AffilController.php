<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAffilRequest;
use App\Http\Requests\UpdateAffilRequest;
use App\Models\Affil;

class AffilController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        if (!auth()->user()->can('role_any', 'manager|pc|pub')) abort(403);

        // もし、affilsテーブルが空なら、distill()を実行する
        if (Affil::count() == 0) {
            Affil::distill();
        }

        $affils = Affil::orderBy('before')->get();
        return view('affil.index')->with(compact('affils'));
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
    public function store(StoreAffilRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Affil $affil)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Affil $affil)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAffilRequest $request, Affil $affil)
    {
        //
        $affil = Affil::find($request->id);
        $affil->after = $request->after;
        $affil->save();
        return redirect()->route('affil.index')->with('feedback.success', '所属を更新しました');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Affil $affil)
    {
        //
    }
}
