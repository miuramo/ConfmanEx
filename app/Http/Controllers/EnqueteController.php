<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnqueteRequest;
use App\Http\Requests\UpdateEnqueteRequest;
use App\Models\Enquete;
use App\Models\EnqueteAnswer;
use App\Models\Paper;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class EnqueteController extends Controller
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
    public function store(StoreEnqueteRequest $request)
    {
        //
    }

    /**
     * 単一・独立ページでの表示
     */
    public function show(Paper $paper, Enquete $enq)
    {
        if (!Gate::allows('show_paper', $paper)) {
            abort(403, 'forbidden_for_others');
        }
        $enqs = Enquete::needForSubmit($paper);
        $eans = EnqueteAnswer::where('paper_id', $paper->id)->get();
        $enqans = [];
        foreach ($eans as $ea) {
            $enqans[$ea->enquete_id][$ea->enquete_item_id] = $ea;
        }

        return view("enquete.pageview")->with(compact("enq","enqs","enqans","paper"));
        //
    }

    /**
     * 単一・独立ページでの編集
     */
    public function edit(Paper $paper, Enquete $enq)
    {
        if (!Gate::allows('edit_paper', $paper)) {
            abort(403, 'forbidden_for_coauthor_or_others');
        }
        $enqs = Enquete::needForSubmit($paper);
        $eans = EnqueteAnswer::where('paper_id', $paper->id)->get();
        $enqans = [];
        foreach ($eans as $ea) {
            $enqans[$ea->enquete_id][$ea->enquete_item_id] = $ea;
        }

        return view("enquete.pageedit")->with(compact("enq","enqs","enqans","paper"));
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEnqueteRequest $request, Enquete $enquete)
    {
        return $request->shori();
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Enquete $enquete)
    {
        //
    }
}
