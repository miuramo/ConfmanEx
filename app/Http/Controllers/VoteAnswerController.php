<?php

namespace App\Http\Controllers;

use App\Http\Requests\Storevote_answersRequest;
use App\Http\Requests\Updatevote_answersRequest;
use App\Models\vote_answers;

class VoteAnswerController extends Controller
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
    public function store(Storevote_answersRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(vote_answers $vote_answers)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(vote_answers $vote_answers)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Updatevote_answersRequest $request, vote_answers $vote_answers)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(vote_answers $vote_answers)
    {
        //
    }
}
