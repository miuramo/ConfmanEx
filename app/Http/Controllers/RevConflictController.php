<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRevConflictRequest;
use App\Http\Requests\UpdateRevConflictRequest;
use App\Models\RevConflict;

class RevConflictController extends Controller
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
    public function store(StoreRevConflictRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(RevConflict $revConflict)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RevConflict $revConflict)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRevConflictRequest $request, RevConflict $revConflict)
    {
        return $request->shori();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RevConflict $revConflict)
    {
        //
    }
}
