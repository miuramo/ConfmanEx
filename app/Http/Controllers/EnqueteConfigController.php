<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnqueteConfigRequest;
use App\Http\Requests\UpdateEnqueteConfigRequest;
use App\Models\Enquete;
use App\Models\EnqueteConfig;

class EnqueteConfigController extends Controller
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
    public function store(StoreEnqueteConfigRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(EnqueteConfig $enqueteConfig)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(EnqueteConfig $enqueteConfig)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateEnqueteConfigRequest $request, EnqueteConfig $enqueteConfig)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $enqconfig_id)
    {
        $aEnq = Enquete::accessibleEnquetes(true);
        $enqConfig = EnqueteConfig::find($enqconfig_id);
        $enq_id = $enqConfig->enquete_id;
        if (!isset($aEnq[$enqConfig->enquete_id])) abort(403);
        $enqConfig->delete();
        return redirect()->route('enq.config', ["enq"=>$enq_id])->with('feedback.success', '行を削除しました');
        //
    }
}
