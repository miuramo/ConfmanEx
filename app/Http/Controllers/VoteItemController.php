<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storevote_itemsRequest;
use App\Http\Requests\Updatevote_itemsRequest;
use App\Models\vote_items;

class VoteItemController extends Controller
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
    public function store(Storevote_itemsRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(vote_items $vote_items)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(vote_items $vote_items)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Updatevote_itemsRequest $request, vote_items $vote_items)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(vote_items $vote_items)
    {
        //
    }
}
