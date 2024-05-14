<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBbMesRequest;
use App\Http\Requests\UpdateBbMesRequest;
use App\Mail\BbNotify;
use App\Models\Bb;
use App\Models\BbMes;
use Illuminate\Http\Request;

class BbMesController extends Controller
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
    public function store(Request $req, int $bbid, string $key2)
    {
        // check bb
        $key = $req->input("key");
        $bb = Bb::with("paper")->where('id', $bbid)->where('key', $key)->first();
        if ($bb == null) abort(403, 'bb not found');
        if (strlen($req->input("mes")) < 1){
            return redirect()->route('bb.show', ['bb' => $bbid, 'key' => $key2])->with('feedback.error', "メッセージを入力してください。");
        }
        $bbmes = BbMes::create([
            'bb_id' => $bb->id,
            'user_id' => auth()->id(),
            'subject' => $req->input("sub"),
            'mes' => $req->input("mes"),
        ]);
        //メール通知
        (new BbNotify($bb, $bbmes))->process_send();

        return redirect()->route('bb.show', ['bb' => $bbid, 'key' => $key2])->with('feedback.success', "書き込みました。関係者にメールで通知しました。");
    }

    /**
     * Display the specified resource.
     */
    public function show(BbMes $bbMes)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BbMes $bbMes)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBbMesRequest $request, BbMes $bbMes)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BbMes $bbMes)
    {
        //
    }
}
