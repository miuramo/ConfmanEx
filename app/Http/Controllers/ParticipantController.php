<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreParticipantRequest;
use App\Http\Requests\UpdateParticipantRequest;
use App\Models\Enquete;
use App\Models\EnqueteAnswer;
use App\Models\EnqueteItem;
use App\Models\Participant;
use Illuminate\Http\Request;

class ParticipantController extends Controller
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
        $part = Participant::firstOrCreate(
            [
                'user_id' => auth()->id(),
            ],
            [
                'event_id' => 1,
            ]
        );
        return redirect()->route("part.edit", ["part" => $part]);
        //
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Participant $part)
    {
        // 回答可能(canedit)または参照可能(readonly)
        $enqs = Enquete::needForPart($part);
        $ids = array_keys($enqs['until']);
        // 既存回答
        $eans = EnqueteAnswer::where('paper_id', $part->id)->whereIn('enquete_id', $ids)->get();
        $enqans = [];
        foreach ($eans as $ea) {
            $enqans[$ea->enquete_id][$ea->enquete_item_id] = $ea;
        }
        return view('part.edit', ['part' => $part])->with(compact("part", "enqs","enqans"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreParticipantRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Participant $participant)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $req, Participant $part)
    {
        // 回答可能(canedit)または参照可能(readonly)
        $enqs = Enquete::needForPart($part);
        $ids = array_keys($enqs['until']);
        // 既存回答
        $eans = EnqueteAnswer::where('paper_id', $part->id)->whereIn('enquete_id', $ids)->get();
        $enqans = [];
        foreach ($eans as $ea) {
            $enqans[$ea->enquete_id][$ea->enquete_item_id] = $ea->valuestr;
        }
        // アンケート項目番号=>name
        $einames = EnqueteItem::pluck('name','id')->toArray();
        $einame2id = array_flip($einames);

        // とりあえずNULLでもよいのは、 othergakkai(12) receiptto(16) bikou(17)
        // ただし、othergakkai(12)は gakkai(11)が「その他...」の場合は必要。

        // 学会が非会員なら、参加区分で協賛学会会員はエラー、kaiinid(13)はNULL必須。

        // これらのチェックをパスしたら、$part->valid = true にしてsaveする。また、submitted をセットする。
        return redirect()->route('part.edit', ['part' => $part])->with('feedback.error', '判定コードはまだ実装されていません。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Participant $participant)
    {
        //
    }
}
