<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnqueteAnswerRequest;
use App\Http\Requests\UpdateEnqueteAnswerRequest;
use App\Models\EnqueteAnswer;
use App\Models\EnqueteItem;
use App\Models\Paper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EnqueteAnswerController extends Controller
{

    /**
     * デモ希望アンケートに、代理回答する
     */
    public function manualset(Request $req)
    {
        if (!auth()->user()->can('role_any', 'pc|demo')) abort(403);
        if ($req->method() === 'POST') {
            info($req->all());
            $pids = $req->input('pids');
            $pidary = explode(",", $pids);
            $pidary = array_map('trim', $pidary);
            $value = $req->input('action');
            $demoei = EnqueteItem::where("name", "demoifaccepted")->first();
            if ($demoei != null) {
                $enq_id = $demoei->enquete_id;
                foreach ($pidary as $pid) {
                    $paper = Paper::find($pid);
                    if ($paper == null) continue;
                    DB::transaction(function () use ($enq_id, $paper, $demoei, $value) {
                        $enq = EnqueteAnswer::firstOrCreate([
                            'enquete_id' => $enq_id,
                            'user_id' => $paper->owner,
                            'paper_id' => $paper->id,
                            'enquete_item_id' => $demoei->id,
                        ]);
                        if (is_numeric($value)) {
                            if ($value <= 2 ** 31 - 1 && $value >= -2 ** 31) $enq->value = $value; // 整数の範囲を越えなければ数値として
                            else $enq->value = null;
                            $enq->valuestr = $value;
                        } else if (is_string($value)) {
                            $enq->value = null;
                            $enq->valuestr = $value;
                        } else if (is_null($value)) {
                            $enq->value = null;
                            $enq->valuestr = null;
                        }
                        $enq->save();
                    });
                }
            }
        }
        return redirect()->route('role.top', ['role' => 'demo']);
    }

    

    

    

    

    

    

    
}
