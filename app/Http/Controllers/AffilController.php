<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAffilRequest;
use App\Http\Requests\UpdateAffilRequest;
use App\Models\Affil;
use Illuminate\Http\Request;

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

        $affils = Affil::orderByDesc('pre')->orderBy('skip')->orderByDesc('orderint')->get();
        return view('affil.index')->with(compact('affils'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->can('role_any', 'manager|pc|pub')) abort(403);
        Affil::distill();
        return redirect()->route('affil.index')->with('feedback.success', '所属の修正ルールを再構成しました');
        //
    }
    public function rebuild()
    {
        if (!auth()->user()->can('role_any', 'manager|pc|pub')) abort(403);
        Affil::rebuild();
        return redirect()->route('affil.index')->with('feedback.success', '所属の修正ルールを再構成しました');
    }

    

    

    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $req)
    {
        if (!auth()->user()->can('role_any', 'manager|pc|pub')) abort(403);
        //
        $pres = $req->input('pre');
        if (is_array($pres)) {
            foreach ($pres as $affilid => $pre) {
                $affil = Affil::find($affilid);
                $affil->pre = ($pre == 'on') ? 1 : 0;
                $affil->save();
            }
        }
        $skips = $req->input('skip');
        if (is_array($skips)){
            foreach ($skips as $affilid => $skip) {
                $affil = Affil::find($affilid);
                $affil->skip = ($skip == 'on') ? 1 : 0;
                $affil->save();
            }    
        }
        $afters = $req->input('after');
        if (is_array($afters)){
            foreach ($afters as $affilid => $after) {
                $affil = Affil::find($affilid);
                if ($affil->after != $after){
                    $affil->after = $after;
                    $affil->save();
                }                
            }    
        }
        return redirect()->route('affil.index')->with('feedback.success', '修正ルールを更新しました');
    }

    
}
